<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla de productos maestro
        // Esta tabla contendrá tanto los productos simples como los productos con variantes.
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique()->comment('Slug único para SEO');
            $table->string('sku')->unique()->comment('SKU único del producto');
            $table->string('brand')->nullable()->comment('Marca del producto, si aplica');
            $table->string('model')->nullable()->comment('Modelo del producto, si aplica');
            $table->string('material')->nullable()->comment('Material del producto, ej. algodón, poliéster');
            $table->text('description')->nullable()->comment('Descripción del producto');
            $table->integer('price')->default(0)->comment('Precio base del producto');
            $table->integer('on_sale_price')->nullable()->comment('Precio en oferta, si aplica');
            $table->integer('stock')->default(0)->comment('Stock total del producto');
            $table->string('image')->nullable()->comment('Imagen principal del producto');
            $table->string('thumbnail')->nullable()->comment('Miniatura del producto');
            $table->json('gallery_images')->nullable()->comment('Galería de imágenes del producto');
            $table->string('video_url')->nullable()->comment('URL del video del producto, si aplica');
            $table->boolean('is_active')->default(true)->comment('Indica si el producto está activo y visible en la tienda');
            $table->boolean('is_featured')->default(false)->comment('Indica si el producto es destacado');
            $table->boolean('is_new')->default(false)->comment('Indica si el producto es nuevo');
            $table->boolean('is_on_sale')->default(false)->comment('Indica si el producto está en oferta');
            $table->boolean('has_variants')->default(false)->comment('Indica si el producto tiene variantes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
