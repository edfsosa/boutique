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
        // Crear la tabla de variantes de productos
        // Esta tabla contendrá las variantes de productos, como diferentes colores, tallas, etc.
        // Cada variante estará asociada a un producto maestro en la tabla `products`.
        // Las variantes pueden tener un precio específico, stock y una imagen propia.
        // Las variantes también pueden estar activas o inactivas, lo que permite ocultarlas de la tienda sin eliminarlas.
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('color')->nullable()->comment('Color específico');
            $table->string('size')->nullable()->comment('Talla específica');
            $table->string('sku')->unique()->comment('SKU único por variante');
            $table->integer('price_override')->nullable()->comment('Precio específico para esta variante');
            $table->integer('stock')->default(0)->comment('Stock por variante');
            $table->string('image')->nullable()->comment('Imagen específica');
            $table->boolean('is_active')->default(true)->comment('Indica si la variante está activa y visible en la tienda');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
