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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Relasi ke tabel orders
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Relasi ke tabel products
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Harga satuan saat checkout
            $table->decimal('total_price', 15, 2); // Total harga item (quantity * price)
            $table->string('size')->nullable(); // Add size column
            $table->string('color')->nullable(); // Add color column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
