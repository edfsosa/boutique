<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color',
        'size',
        'sku',
        'price_override',
        'stock',
        'image',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
