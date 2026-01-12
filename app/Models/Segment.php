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

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}