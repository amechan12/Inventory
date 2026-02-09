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

    protected $appends = ['image_url'];

    // Relasi: Satu Product bisa ada di banyak Transaction (Many-to-Many)
    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'product_transaction')
            ->withPivot('quantity', 'price_per_item');
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
}