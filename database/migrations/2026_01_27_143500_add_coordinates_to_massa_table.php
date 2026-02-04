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
        // Add latitude and longitude to massa table if they don't exist
        if (!Schema::hasColumn('massa', 'latitude')) {
            Schema::table('massa', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable()->after('verified');
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('massa', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
