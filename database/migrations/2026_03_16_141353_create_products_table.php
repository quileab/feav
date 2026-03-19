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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 50)->unique()->nullable();
            $table->string('origin_code', 50)->nullable();
            $table->foreignId('category_id')->constrained();
            $table->string('brand', 30)->nullable();
            $table->string('model', 30)->nullable();
            $table->string('description', 50);
            $table->decimal('quantity_min', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->foreignId('tax_condition_type_id')->constrained();
            $table->foreignId('unit_type_id')->constrained();
            $table->decimal('sale_price1', 12, 2)->default(0);
            $table->decimal('profit_percentage1', 12, 2)->default(0);
            $table->decimal('sale_price2', 12, 2)->default(0);
            $table->decimal('profit_percentage2', 12, 2)->default(0);
            $table->decimal('discount_max', 5, 2)->default(0);
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
