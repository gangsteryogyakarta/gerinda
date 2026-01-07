<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Event statistics snapshot for reporting
        Schema::create('event_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->date('stat_date');
            $table->integer('hour')->nullable(); // 0-23 for hourly stats
            $table->integer('registrations_count')->default(0);
            $table->integer('checkins_count')->default(0);
            $table->integer('new_massa_count')->default(0); // New massa acquired
            $table->integer('returning_massa_count')->default(0);
            $table->json('demographic_breakdown')->nullable(); // Age, gender distribution
            $table->json('location_breakdown')->nullable(); // By province/regency
            $table->timestamps();
            
            $table->unique(['event_id', 'stat_date', 'hour']);
            $table->index(['event_id', 'stat_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_statistics');
    }
};
