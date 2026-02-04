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
        Schema::table('massa', function (Blueprint $table) {
            $table->enum('kategori_massa', ['Pengurus', 'Simpatisan'])->default('Simpatisan')->after('nama_lengkap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('massa', function (Blueprint $table) {
            $table->dropColumn('kategori_massa');
        });
    }
};
