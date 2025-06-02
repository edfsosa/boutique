<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Actualiza el stock del producto al crear una variante.
     */
    public function created(ProductVariant $variant)
    {
        $this->updateProductStock($variant->product_id);
        // Sincronizar price_override si está vacío
        if (is_null($variant->price_override)) {
            $variant->price_override = $variant->product->price;
            $variant->saveQuietly();
        }
    }

    /**
     * Actualiza el stock del producto al actualizar una variante.
     */
    public function updated(ProductVariant $variant)
    {
        // Verifica si el stock fue modificado
        if ($variant->isDirty('stock')) {
            $this->updateProductStock($variant->product_id);
        }
        // Sincronizar price_override si está vacío
        if (is_null($variant->price_override)) {
            $variant->price_override = $variant->product->price;
            $variant->saveQuietly();
        }
    }

    /**
     * Actualiza el stock del producto al eliminar una variante.
     */
    public function deleted(ProductVariant $variant)
    {
        $this->updateProductStock($variant->product_id);
    }

    /**
     * Handle the ProductVariant "restored" event.
     */
    public function restored(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Handle the ProductVariant "force deleted" event.
     */
    public function forceDeleted(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Actualiza el stock del producto (método centralizado).
     */
    protected function updateProductStock($productId)
    {
        // Suma el stock de todas las variantes no eliminadas
        $totalStock = ProductVariant::where('product_id', $productId)
            ->sum('stock');

        // Actualiza el producto principal
        Product::where('id', $productId)
            ->update(['stock' => $totalStock]);
    }
}
