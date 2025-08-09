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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('address');
            $table->string('village');
            $table->string('district');
            $table->string('city');
            $table->string('province');
            $table->string('phone');
            $table->decimal('total_price', 15, 2);
            $table->enum('payment_method', ['digital_wallet', 'qris'])->default('qris');
            $table->string('payment_proof')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', [
                'waiting',
                'checking',
                'pending',
                'processing',
                'shiping',
                'completed',
                'cancelled'
            ])->default('waiting');
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
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
