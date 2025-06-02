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

    public function getPriceAttribute()
    {
        // Si hay un precio específico para la variante, lo usamos; de lo contrario, usamos el precio del producto
        return $this->price_override ?? $this->product->price;
    }

    public function getStockAttribute()
    {
        // Si hay stock específico para la variante, lo usamos; de lo contrario, usamos el stock del producto
        return $this->stock ?? $this->product->stock;
    }
}
