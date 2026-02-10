<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Data untuk KPI Cards (Sistem Inventaris Pinjaman)
        $totalBarang = Product::count();
        $stokHabis = Product::where('stock', 0)->count();
        $stokTersedia = Product::where('stock', '>', 0)->count();
        $sedangDipinjam = Transaction::where('status', 'borrowed')->count();
        $menungguPersetujuan = Transaction::where('status', 'pending')->count();
        $menungguPengembalian = Transaction::where('status', 'returning')->count();

        // 1. Data untuk grafik pinjaman harian (7 hari terakhir)
        $dailyLabels = [];
        $dailyData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailyLabels[] = $date->format('d M');
            
            $loans = Transaction::whereDate('created_at', $date->format('Y-m-d'))
                ->whereIn('status', ['pending', 'borrowed', 'returned'])
                ->count();
            $dailyData[] = (float) $loans;
        }

        // 2. Data untuk grafik pinjaman bulanan (12 bulan terakhir)
        $monthlyLabels = [];
        $monthlyData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');
            
            $loans = Transaction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereIn('status', ['pending', 'borrowed', 'returned'])
                ->count();
            $monthlyData[] = (float) $loans;
        }

        // 3. Data untuk pie chart status pinjaman
        $statusStats = Transaction::select('status')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('status', ['pending', 'borrowed', 'returning', 'returned', 'cancelled'])
            ->groupBy('status')
            ->get();

        $statusLabels = [];
        $statusData = [];

        // Mapping untuk label yang lebih friendly
        $statusMapping = [
            'pending' => 'Menunggu Persetujuan',
            'borrowed' => 'Sedang Dipinjam',
            'returning' => 'Menunggu Pengembalian',
            'returned' => 'Sudah Dikembalikan',
            'cancelled' => 'Dibatalkan'
        ];

        foreach ($statusStats as $status) {
            $statusLabels[] = $statusMapping[$status->status] ?? ucfirst($status->status);
            $statusData[] = $status->total;
        }

        // Jika tidak ada data status, beri default
        if (empty($statusLabels)) {
            $statusLabels = ['Menunggu Persetujuan', 'Sedang Dipinjam', 'Sudah Dikembalikan'];
            $statusData = [0, 0, 0];
        }

        // 4. Data untuk top products (5 produk paling sering dipinjam)
        $topProductsStats = DB::table('product_transaction')
            ->join('products', 'product_transaction.product_id', '=', 'products.id')
            ->join('transactions', 'product_transaction.transaction_id', '=', 'transactions.id')
            ->select('products.name')
            ->selectRaw('COUNT(DISTINCT transactions.id) as total_loans')
            ->whereIn('transactions.status', ['borrowed', 'returned'])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_loans')
            ->limit(5)
            ->get();

        $topProductLabels = [];
        $topProductData = [];

        foreach ($topProductsStats as $product) {
            // Potong nama produk jika terlalu panjang
            $productName = strlen($product->name) > 20 
                ? substr($product->name, 0, 20) . '...' 
                : $product->name;
            
            $topProductLabels[] = $productName;
            $topProductData[] = $product->total_loans;
        }

        // Jika tidak ada data produk, beri default
        if (empty($topProductLabels)) {
            $topProductLabels = ['Belum ada data', '', '', '', ''];
            $topProductData = [0, 0, 0, 0, 0];
        }

        // Jika user adalah anggota, kembalikan dashboard anggota yang lebih sederhana
        if (auth()->user()->role == 'anggota') {
            $user = auth()->user();

            $activeLoans = Transaction::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->count();

            $pendingLoans = Transaction::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();

            $dueSoonLoans = Transaction::where('user_id', $user->id)
                ->where('status', 'borrowed')
                ->whereBetween('return_date', [Carbon::now()->toDateString(), Carbon::now()->addDays(3)->toDateString()])
                ->count();

            $currentLoans = Transaction::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'borrowed', 'returning', 'returned'])
                ->with('products')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            // Fetch products with filters
            $query = Product::query();

            // Filter by category
            if (request('category')) {
                $query->where('category', request('category'));
            }

            // Filter by box
            if (request('box')) {
                $query->whereHas('boxes', function ($q) {
                    $q->where('box_id', request('box'));
                });
            }

            $products = $query->orderBy('name')->paginate(12);
            $totalProducts = Product::count();

            // Get all categories for filter
            $categories = Product::select('category')
                ->distinct()
                ->orderBy('category')
                ->get()
                ->map(function ($product) {
                    $categoryName = $product->category ?? 'Tanpa Kategori';
                    $count = Product::where('category', $product->category)->count();
                    return [
                        'name' => $categoryName,
                        'slug' => str($categoryName)->slug('-'),
                        'count' => $count
                    ];
                })
                ->sortBy('name')
                ->values()
                ->all();

            // Get all boxes for filter
            $boxes = DB::table('boxes')
                ->leftJoin('box_product', 'boxes.id', '=', 'box_product.box_id')
                ->select('boxes.id', 'boxes.name', 'boxes.barcode')
                ->selectRaw('COUNT(DISTINCT box_product.product_id) as products_count')
                ->groupBy('boxes.id', 'boxes.name', 'boxes.barcode')
                ->orderBy('boxes.name')
                ->get();

            return view('member.home', compact(
                'activeLoans',
                'pendingLoans',
                'dueSoonLoans',
                'currentLoans',
                'topProductLabels',
                'topProductData',
                'products',
                'categories',
                'boxes',
                'totalProducts'
            ));
        }

        // Untuk backward compatibility dengan view lama (jika masih ada)
        $labels = $dailyLabels;
        $data = $dailyData;

        return view('home', compact(
            // KPI Data
            'totalBarang',
            'stokHabis', 
            'stokTersedia',
            'sedangDipinjam',
            'menungguPersetujuan',
            'menungguPengembalian',
            
            // Grafik Harian
            'dailyLabels',
            'dailyData',
            
            // Grafik Bulanan
            'monthlyLabels',
            'monthlyData',
            
            // Pie Chart Status Pinjaman
            'statusLabels',
            'statusData',
            
            // Bar Chart Top Products
            'topProductLabels',
            'topProductData',
            
            // Backward compatibility
            'labels',
            'data'
        ));
    }

    /**
     * Get sales data for specific date range
     * Helper method untuk keperluan lain jika diperlukan
     */
    public function getSalesDataByDateRange($startDate, $endDate)
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get monthly sales summary
     * Helper method untuk report bulanan
     */
    public function getMonthlySalesSummary($year = null)
    {
        $year = $year ?? Carbon::now()->year;
        
        return Transaction::whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('AVG(total_amount) as avg_transaction')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Get product performance data
     * Helper method untuk analisis produk
     */
    public function getProductPerformance()
    {
        return DB::table('product_transaction')
            ->join('products', 'product_transaction.product_id', '=', 'products.id')
            ->join('transactions', 'product_transaction.transaction_id', '=', 'transactions.id')
            ->select(
                'products.id',
                'products.name',
                'products.category',
                DB::raw('SUM(product_transaction.quantity) as total_sold'),
                DB::raw('COALESCE(SUM(product_transaction.quantity * COALESCE(product_transaction.price_per_item, 0)), 0) as total_revenue'),
                DB::raw('COUNT(DISTINCT product_transaction.transaction_id) as transaction_count')
            )
            ->groupBy('products.id', 'products.name', 'products.category')
            ->orderByDesc('total_revenue')
            ->get();
    }
}