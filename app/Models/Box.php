<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Box extends Model
{
    protected $fillable = [
        'name',
        'barcode',
        'location',
        'segment_id',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'box_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    /**
     * Get total quantity of all products in this box
     */
    public function getTotalQuantity()
    {
        return $this->products()->sum('box_product.quantity') ?? 0;
    }}