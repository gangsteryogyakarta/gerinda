<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('massa_id')->constrained('massa')->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['check_in', 'check_out', 'manual_override']);
            $table->string('method')->nullable(); // qr_scan, manual_input, nfc
            $table->string('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['event_id', 'created_at']);
            $table->index('event_registration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_logs');
    }
};
