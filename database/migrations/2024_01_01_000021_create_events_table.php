<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Auto-generated event code
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('event_category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            
            // Location
            $table->string('venue_name');
            $table->text('venue_address');
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('regency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Schedule
            $table->dateTime('registration_start')->nullable();
            $table->dateTime('registration_end')->nullable();
            $table->dateTime('event_start');
            $table->dateTime('event_end');
            
            // Quota Management
            $table->integer('max_participants')->nullable(); // null = unlimited
            $table->integer('current_participants')->default(0);
            $table->boolean('enable_waitlist')->default(false);
            
            // Features
            $table->boolean('require_ticket')->default(true);
            $table->boolean('enable_checkin')->default(true);
            $table->boolean('enable_lottery')->default(false);
            $table->boolean('send_wa_notification')->default(false);
            
            // Status
            $table->enum('status', ['draft', 'published', 'ongoing', 'completed', 'cancelled'])->default('draft');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'event_start']);
            $table->index('event_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
