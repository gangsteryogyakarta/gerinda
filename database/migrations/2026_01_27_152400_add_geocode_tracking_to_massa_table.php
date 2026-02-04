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
            // Track the source of coordinates
            $table->string('geocode_source', 30)->nullable()->after('longitude');
            // Track when geocoding was performed
            $table->timestamp('geocoded_at')->nullable()->after('geocode_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('massa', function (Blueprint $table) {
            $table->dropColumn(['geocode_source', 'geocoded_at']);
        });
    }
};
