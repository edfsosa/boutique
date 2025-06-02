<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'brand',
        'model',
        'material',
        'description',
        'price',
        'on_sale_price',
        'stock',
        'image',
        'thumbnail',
        'gallery_images',
        'video_url',
        'is_active',
        'is_featured',
        'is_new',
        'is_on_sale',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
