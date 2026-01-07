<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('massa_id')->constrained('massa')->cascadeOnDelete();
            
            // Ticket Info
            $table->string('ticket_number', 50)->unique();
            $table->string('qr_code_path')->nullable();
            
            // Registration Status
            $table->enum('registration_status', ['pending', 'confirmed', 'cancelled', 'waitlist'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            
            // Check-in Info
            $table->enum('attendance_status', ['not_arrived', 'checked_in', 'no_show'])->default('not_arrived');
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checkin_method')->nullable(); // qr_scan, manual
            
            // Lottery
            $table->boolean('eligible_for_lottery')->default(true);
            $table->boolean('won_lottery')->default(false);
            $table->string('lottery_prize')->nullable();
            $table->timestamp('lottery_won_at')->nullable();
            
            // Custom field values stored as JSON
            $table->json('custom_field_values')->nullable();
            
            $table->text('notes')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['event_id', 'massa_id']); // One registration per massa per event
            $table->index(['event_id', 'registration_status']);
            $table->index(['event_id', 'attendance_status']);
            $table->index('ticket_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
