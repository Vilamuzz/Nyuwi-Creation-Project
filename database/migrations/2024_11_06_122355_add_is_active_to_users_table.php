<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToUsersTable extends Migration
{
    /**
     * Menjalankan migrasi untuk menambahkan kolom 'is_active' ke tabel 'users'.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true); // Menambahkan kolom is_active
        });
    }

    /**
     * Membatalkan migrasi dengan menghapus kolom 'is_active' dari tabel 'users'.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
