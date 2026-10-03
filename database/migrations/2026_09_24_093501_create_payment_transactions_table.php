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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->string('gateway')->default('midtrans');
            $table->string('gateway_order_id')->nullable()->index();
            $table->string('gateway_transaction_id')->nullable()->unique(); // Midtrans's transaction_id
            $table->string('snap_token')->nullable();
            $table->string('snap_redirect_url')->nullable();
            $table->string('payment_channel')->nullable(); // 'gopay', 'bank_transfer', 'qris', 'credit_card'

            $table->decimal('amount', 15, 2);        // what the customer is charged
            $table->decimal('gateway_fee', 15, 2)->default(0);
            $table->enum('fee_borne_by', ['customer', 'merchant'])->default('merchant');
            $table->decimal('net_amount', 15, 2);    // amount received after fee (relevant when merchant absorbs it)

            $table->enum('status', ['pending', 'settlement', 'capture', 'deny', 'cancel', 'expire', 'refund'])
                  ->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->json('raw_response')->nullable(); // full webhook payload, for audit/debugging
            $table->boolean('signature_verified')->default(false);

            $table->timestamps();
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
