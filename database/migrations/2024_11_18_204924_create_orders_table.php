<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("orders", function (Blueprint $table) {
            $table->id();
            $table->string("order_number")->unique();
            $table->foreignId('user_id')->nullable();

            // recipient snapshot — don't rely on users table, name/phone can differ per order
            $table->string("recipient_name");
            $table->string('email', 255)->nullable();
            $table->string("phone");

            // address snapshot
            $table->string("address");
            $table->string("province_id");
            $table->string("province_name");
            $table->string("city_id");
            $table->string("city_name");
            $table->string("district_id");
            $table->string("district_name");
            $table->string("postal_code")->nullable();

            // shipping snapshot
            $table->string("courier"); // e.g. "jne"
            $table->string("shipping_service"); // e.g. "REG"
            $table->unsignedInteger("weight"); // grams, snapshot at order time
            $table->decimal("shipping_cost", 15, 2);
            $table->string("tracking_number")->nullable();

            // money
            $table->decimal("subtotal", 15, 2); // sum of items only
            $table->decimal("total_price", 15, 2); // subtotal + shipping_cost (+tax/discount if added later)

            // payment
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'failed', 'expired', 'refunded'])->default('unpaid');

            $table->enum("status", ["waiting", "checking", "pending", "processing", "shipped", "completed", "cancelled",])->default("waiting");

            $table->timestamps();

            $table->index(["user_id", "status"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("orders");
    }
};
