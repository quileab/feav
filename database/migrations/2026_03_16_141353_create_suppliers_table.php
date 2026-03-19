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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id_type_id')->constrained();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->foreignId('province_id_type_id')->constrained();
            $table->string('phone', 40)->nullable();
            $table->string('email', 127)->unique();
            $table->string('CUIT', 11)->nullable();
            $table->foreignId('tax_condition_type_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
