<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'brand',
        'model',
        'sku',
        'material',
        'dimensions',
        'weight',
        'warranty',
        'origin',
        'price',
        'cost_price',
        'stock',
        'image',
        'rating',
        'status',
        'views_count',
        'cart_additions',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'rating' => 'decimal:1',
            'status' => 'string',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')
            ->orderBy('is_main', 'desc')
            ->orderBy('id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id')->where('status', 'approved');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'product_id');
    }
}