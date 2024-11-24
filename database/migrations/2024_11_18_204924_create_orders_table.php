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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relasi ke tabel users
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('province');
            $table->string('phone');
            $table->decimal('total_price', 15, 2);
            $table->enum('payment_method', ['E-Wallet', 'cash_on_delivery'])->default('cash_on_delivery');
            $table->text('note')->nullable();
            $table->enum('status', ['awaiting', 'pending', 'processing', 'completed', 'cancelled'])->default('awaiting');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
