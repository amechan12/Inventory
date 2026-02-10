<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'image_path',
    ];

    protected $appends = ['image_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return asset('assets/img/defaultsegmen.png');
    }

    /**
     * Get total count of products in this segment
     * Includes both direct products and products in boxes of this segment
     */
    public function getTotalProductCount()
    {
        // Count direct products (via segment_id)
        $directCount = $this->products()->count();

        // Count products in boxes of this segment
        $boxProducts = Box::where('segment_id', $this->id)
            ->with('products')
            ->get()
            ->reduce(function ($carry, $box) {
                return $carry + $box->products->count();
            }, 0);

        return $directCount + $boxProducts;
    }
}