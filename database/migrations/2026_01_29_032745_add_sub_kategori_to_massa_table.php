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
            $table->enum('sub_kategori', ['DPD DIY', 'DPC KABUPATEN', 'PAC'])
                ->nullable()
                ->after('kategori_massa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('massa', function (Blueprint $table) {
            $table->dropColumn('sub_kategori');
        });
    }
};
