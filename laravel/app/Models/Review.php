<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'author',
        'rating',
        'comment',
        'photo_url',
        'is_verified_buyer',
        'helpful_count',
        'tags',
        'date',
    ];

    protected $casts = [
        'is_verified_buyer' => 'boolean',
        'helpful_count' => 'integer',
        'tags' => 'array',
        'rating' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
