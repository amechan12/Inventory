<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Box;
use App\Models\Product;
use App\Models\Segment;
use Illuminate\Support\Facades\DB;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class BoxController extends Controller
{
    public function index()
    {
        $boxes = Box::with('products', 'segment')->orderBy('name')->get();
        $segments = Segment::orderBy('name')->get();
        $totalBoxes = $boxes->count();
        $totalProductsInBoxes = $boxes->reduce(function ($carry, $b) { return $carry + $b->products->count(); }, 0);

        return view('boxes.index', compact('boxes', 'segments', 'totalBoxes', 'totalProductsInBoxes'));
    }

    public function create()
    {
        $segments = Segment::orderBy('name')->get();
        return view('boxes.create', compact('segments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'segment_id' => 'required|exists:segments,id',
        ]);

        // Generate unique barcode for box
        do {
            $barcode = 'BOX-' . strtoupper(Str::random(8));
        } while (Box::where('barcode', $barcode)->exists());

        $data['barcode'] = $barcode;

        $box = Box::create($data);

        // Location field removed from UI - keep DB column but don't auto-fill it here

        return redirect()->route('boxes.index')->with('success', 'Box created');
    }

    public function show(Box $box)
    {
        $box->load('products', 'segment');
        $allProducts = Product::orderBy('name')->get();
        return view('boxes.show', compact('box', 'allProducts'));
    }

    // Update products assigned to a box (sync pivot quantities)
    public function updateProducts(Request $request, Box $box)
    {
        $data = $request->validate([
            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Get current products in this box BEFORE update
            $currentProducts = $box->products()->pluck('product_id')->toArray();
            
            // Build new sync array with new quantities
            $sync = [];
            if (!empty($data['products'])) {
                foreach ($data['products'] as $p) {
                    $qty = isset($p['quantity']) && $p['quantity'] > 0 ? (int) $p['quantity'] : 1;
                    $sync[$p['id']] = ['quantity' => $qty];
                }
            }

            // Find products that are being removed (not in new sync)
            $productsToRemove = array_diff($currentProducts, array_keys($sync));
            
            // Find products that are being added or updated
            $newOrUpdatedProducts = array_keys($sync);

            // Update box-product relationships
            $box->products()->sync($sync);

            DB::commit();

            return redirect()->route('boxes.show', $box)->with('success', 'Isi kotak diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui isi kotak: ' . $e->getMessage());
        }
    }

    public function edit(Box $box)
    {
        $segments = Segment::orderBy('name')->get();
        return view('boxes.edit', compact('box', 'segments'));
    }

    public function update(Request $request, Box $box)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'segment_id' => 'required|exists:segments,id',
        ]);

        // Keep barcode unchanged (auto-generated). Update segment/name only.
        $box->update([ 'name' => $data['name'], 'segment_id' => $data['segment_id'] ]);

        return redirect()->route('boxes.index')->with('success', 'Box updated');
    }

    // Scan box barcode and show items inside (used by scanner flow)
    public function scan(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'required|string',
        ]);

        $box = Box::where('barcode', $data['barcode'])->with('products', 'segment')->first();

        if (! $box) {
            return redirect()->back()->with('error', 'Box not found');
        }

        return view('boxes.show', compact('box'));
    }

    // API: return box and products by barcode (JSON)
    public function apiGetByBarcode($barcode)
    {
        $box = Box::where('barcode', $barcode)->with('products')->first();
        if (! $box) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        // Normalize product fields to ensure frontend gets numeric quantities and stock
        $box->products->transform(function ($p) {
            $p->pivot_quantity = isset($p->pivot->quantity) ? (int) $p->pivot->quantity : 0;
            $p->stock = isset($p->stock) ? (int) $p->stock : 0;
            return $p;
        });

        return response()->json(['success' => true, 'box' => $box]);
    }

    // API: return box and products by numeric id
    public function apiGetById($id)
    {
        $box = Box::with('products')->find($id);
        if (! $box) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        // Normalize product fields to ensure frontend gets numeric quantities and stock
        $box->products->transform(function ($p) {
            $p->pivot_quantity = isset($p->pivot->quantity) ? (int) $p->pivot->quantity : 0;
            $p->stock = isset($p->stock) ? (int) $p->stock : 0;
            return $p;
        });

        return response()->json(['success' => true, 'box' => $box]);
    }

    // Show QR page for a box (printable)
    public function showQR($id)
    {
        try {
            $box = Box::findOrFail($id);

            // Build URL to box show page
            $url = route('boxes.show', $box->id);

            $qrcode = new QRCode();
            $qrImage = $qrcode->render($url);

            if (strpos($qrImage, 'data:image') === 0) {
                $qrCode = '<img src="' . $qrImage . '" alt="QR Code" class="mx-auto border-4 border-indigo-500 rounded-lg shadow-lg" style="width: 300px; height: 300px;" />';
            } else {
                $qrCode = '<img src="data:image/png;base64,' . base64_encode($qrImage) . '" alt="QR Code" class="mx-auto border-4 border-indigo-500 rounded-lg shadow-lg" style="width: 300px; height: 300px;" />';
            }

            return view('boxes.qr', compact('box', 'qrCode'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate QR Code: ' . $e->getMessage());
        }
    }

    // Delete a box
    public function destroy(Box $box)
    {
        // This will cascade delete pivot entries because migration uses cascadeOnDelete
        $box->delete();
        return redirect()->route('boxes.index')->with('success', 'Kotak berhasil dihapus.');
    }

    // AJAX: add or update a product in a box
    public function addProduct(Request $request, Box $box)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $productId = $data['product_id'];
        $qty = isset($data['quantity']) ? (int) $data['quantity'] : 1;

        // If already exists, update pivot; otherwise attach
        if ($box->products()->where('product_id', $productId)->exists()) {
            $box->products()->updateExistingPivot($productId, ['quantity' => $qty]);
        } else {
            $box->products()->attach($productId, ['quantity' => $qty]);
        }

        $product = Product::find($productId);

        return response()->json(['success' => true, 'product' => $product, 'quantity' => $qty]);
    }

    // AJAX: update a product quantity in a box
    public function updateProduct(Request $request, Box $box, $productId)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $qty = (int) $data['quantity'];

        if (! $box->products()->where('product_id', $productId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan di kotak'], 404);
        }

        $box->products()->updateExistingPivot($productId, ['quantity' => $qty]);

        return response()->json(['success' => true, 'product_id' => $productId, 'quantity' => $qty]);
    }

    // AJAX: remove a product from a box
    public function removeProduct(Box $box, $productId)
    {
        if (! $box->products()->where('product_id', $productId)->exists()) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan di kotak'], 404);
        }

        $box->products()->detach($productId);

        return response()->json(['success' => true, 'product_id' => $productId]);
    }
}
