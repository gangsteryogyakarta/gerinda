<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing data that won't fit the new ENUM
        DB::table('massa')->where('sub_kategori', 'DPC KABUPATEN')->update(['sub_kategori' => null]);

        // Using raw SQL is safer for modifying ENUM columns in MySQL to avoid Doctrine issues
        DB::statement("ALTER TABLE massa MODIFY COLUMN sub_kategori ENUM('DPD DIY', 'DPC Sleman', 'DPC Kota Yogyakarta', 'DPC Bantul', 'DPC Kulon Progo', 'DPC Gunungkidul', 'PAC') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous list (approximate based on previous file)
        DB::statement("ALTER TABLE massa MODIFY COLUMN sub_kategori ENUM('DPD DIY', 'DPC KABUPATEN', 'PAC') NULL");
    }
};
