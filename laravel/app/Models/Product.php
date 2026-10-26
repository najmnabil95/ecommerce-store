<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'description_ar',
        'price',
        'original_price',
        'stock',
        'sizes',
        'colors',
        'material',
        'care_instructions',
        'fit_type',
        'gender',
        'rating',
        'reviews_count',
        'badge',
        'image_url',
    ];

    protected $casts = [
        'price' => 'float',
        'original_price' => 'float',
        'rating' => 'float',
        'stock' => 'integer',
        'reviews_count' => 'integer',
        'sizes' => 'array',
        'colors' => 'array',
    ];

    public function getImageAttribute()
    {
        return $this->image_url;
    }

    public function getOldPriceAttribute()
    {
        return $this->original_price;
    }

    public function getFabricAttribute()
    {
        return $this->material;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->orderBy('id', 'desc');
    }
}
