<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'address',
        'city',
        'payment_method',
        'notes',
        'coupon_code',
        'subtotal',
        'discount',
        'shipping',
        'total',
        'status',
        'items_json',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'discount' => 'float',
        'shipping' => 'float',
        'total' => 'float',
        'items_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
