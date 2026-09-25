<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'expired',
                'refunded'
            ])->default('pending')->after('total_price');
        });

        // Migrate existing order statuses
        DB::table('orders')->whereIn('status', ['waiting', 'checking'])->update([
            'payment_status' => 'pending',
            'status' => 'processing',
        ]);
        DB::table('orders')->whereIn('status', ['pending', 'processing', 'shiping', 'completed'])->update([
            'payment_status' => 'paid',
        ]);
        DB::table('orders')->where('status', 'pending')->update([
            'status' => 'processing',
        ]);
        DB::table('orders')->where('status', 'cancelled')->update([
            'payment_status' => 'failed',
        ]);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('processing', 'shiping', 'completed', 'cancelled') NOT NULL DEFAULT 'processing'");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_proof');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('payment_method');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('waiting', 'checking', 'pending', 'processing', 'shiping', 'completed', 'cancelled') NOT NULL DEFAULT 'waiting'");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
