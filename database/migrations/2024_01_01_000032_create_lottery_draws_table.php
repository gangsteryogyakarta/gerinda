<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lottery_prize_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('massa_id')->constrained('massa')->cascadeOnDelete();
            $table->foreignId('drawn_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('drawn_at');
            $table->boolean('is_claimed')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['lottery_prize_id', 'event_registration_id']); // One prize per registration
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_draws');
    }
};
