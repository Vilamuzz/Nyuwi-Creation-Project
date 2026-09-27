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
        Schema::table('profile_stores', function (Blueprint $table) {
            $table->string('shipping_origin_district_id')->nullable()->after('shipping_origin_city_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_stores', function (Blueprint $table) {
            $table->dropColumn('shipping_origin_district_id');
        });
    }
};
