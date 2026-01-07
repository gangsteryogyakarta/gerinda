<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Loyalty tracking - aggregated stats per massa
        Schema::create('massa_loyalty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('massa_id')->constrained('massa')->cascadeOnDelete();
            $table->integer('total_events_registered')->default(0);
            $table->integer('total_events_attended')->default(0);
            $table->integer('total_lotteries_won')->default(0);
            $table->date('first_event_date')->nullable();
            $table->date('last_event_date')->nullable();
            $table->decimal('attendance_rate', 5, 2)->default(0); // Percentage
            $table->enum('loyalty_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->integer('points')->default(0);
            $table->timestamps();
            
            $table->unique('massa_id');
            $table->index('loyalty_tier');
            $table->index('attendance_rate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('massa_loyalty');
    }
};
