<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    // Relasi: Satu Product bisa ada di banyak Transaction (Many-to-Many)
    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'product_transaction')
                    ->withPivot('quantity', 'price_per_item');
    }
}