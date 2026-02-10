<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Segment;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'stock',
        'reserved_stock',
        'category',
        'image_path',
        'segment_id',
    ];

    protected $appends = ['image_url', 'available_stock'];

    // Relasi: Satu Product bisa ada di banyak Transaction (Many-to-Many)
    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'product_transaction')
            ->withPivot('quantity', 'price_per_item');
    }

    /**
     * Consume (deduct) quantity for an approved borrow.
     * If the product exists in boxes, consume from boxes' pivot quantities
     * (consuming from boxes in order returned by relation). Otherwise consume
     * from `stock`.
     * This method does not touch `reserved_stock` (caller should clear reservation).
     *
     * @param int $qty
     * @throws \Exception when not enough stock available to consume
     */
    public function consumeForApproval(int $qty)
    {
        if ($qty <= 0) return;

        // Refresh to ensure we have latest box relations
        $this->refresh();
        $this->load('boxes');

        $boxQuantity = $this->getTotalQuantityInBoxes();
        if ($boxQuantity > 0) {
            $remaining = $qty;
            // Ensure boxes relation is loaded
            $boxes = $this->boxes;
            foreach ($boxes as $box) {
                $avail = isset($box->pivot->quantity) ? (int) $box->pivot->quantity : 0;
                if ($avail <= 0) continue;
                $take = min($avail, $remaining);
                $newQty = $avail - $take;
                // Update pivot row
                $this->boxes()->updateExistingPivot($box->id, ['quantity' => $newQty]);
                $remaining -= $take;
                if ($remaining <= 0) break;
            }

            if ($remaining > 0) {
                throw new \Exception('Stok di kotak tidak mencukupi untuk mengurangi ' . $qty . ' unit');
            }
        } else {
            // Not in any box, use product.stock
            if ($this->stock < $qty) {
                throw new \Exception('Stok fisik tidak mencukupi untuk mengurangi ' . $qty . ' unit');
            }
            $this->decrement('stock', $qty);
        }
    }

    // Relasi: Product berada di satu Segment
    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    // Relasi: Product dapat berada di beberapa Box
    public function boxes()
    {
        return $this->belongsToMany(\App\Models\Box::class, 'box_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return asset('assets/img/default.png');
    }

    /**
     * Get total quantity of this product across all boxes
     */
    public function getTotalQuantityInBoxes()
    {
        return $this->boxes->sum('pivot.quantity');
    }

    /**
     * Get available stock for borrowing
     * Considers: stock - reserved_stock, and must exist in boxes
     * If product is in boxes, use quantity from box
     */
    public function getAvailableStockForBorrow()
    {
        // If product is assigned to box(es), use quantity from box
        $boxQuantity = $this->getTotalQuantityInBoxes();
        if ($boxQuantity > 0) {
            // If in box, available = box quantity - reserved_stock
            // (reserved_stock should be deducted from box quantity)
            return max(0, $boxQuantity - $this->reserved_stock);
        }

        // If not in any box, use regular stock - reserved_stock
        return max(0, $this->stock - $this->reserved_stock);
    }

    /**
     * Accessor to expose available stock as attribute `available_stock`
     */
    public function getAvailableStockAttribute()
    {
        return $this->getAvailableStockForBorrow();
    }
}