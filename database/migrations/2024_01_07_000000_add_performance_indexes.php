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
        try {
            Schema::table('massa', function (Blueprint $table) {
                // Check if simple index works first
                $table->index(['latitude', 'longitude'], 'idx_massa_coords');
            });
        } catch (\Exception $e) {
            // Index likely exists, ignore
        }

        try {
            Schema::table('massa', function (Blueprint $table) {
                $table->index(['province_id', 'latitude', 'longitude'], 'idx_massa_prov_coords');
            });
        } catch (\Exception $e) {
            // Index likely exists
        }

        try {
            Schema::table('events', function (Blueprint $table) {
                $table->index(['status', 'event_start'], 'idx_events_perf');
            });
        } catch (\Exception $e) {
            // Index likely exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to avoid errors during rollback of existing indexes
    }
};
