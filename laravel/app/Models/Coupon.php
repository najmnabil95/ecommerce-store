<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_percent',
        'min_order',
        'max_discount',
        'is_active',
        'uses_count',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'min_order' => 'float',
        'max_discount' => 'float',
        'is_active' => 'boolean',
        'uses_count' => 'integer',
    ];
}
