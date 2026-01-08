<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'total_amount',
        'payment_method',
        'status',
        'borrow_reason',
        'duration',
        'borrow_date',
        'return_date',
        'condition_on_return',
        'return_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relasi: Satu Transaction dimiliki oleh satu User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Satu Transaction memiliki banyak Product (Many-to-Many)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_transaction')
                    ->withPivot('quantity', 'price_per_item');
    }

    // Relasi: Satu Transaction disetujui oleh satu User (Admin)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}