<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Notification logs for WA Gateway
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('massa_id')->nullable()->constrained('massa')->nullOnDelete();
            $table->foreignId('event_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('channel', ['whatsapp', 'sms', 'email']);
            $table->string('recipient'); // phone/email
            $table->enum('type', ['ticket', 'reminder', 'lottery_win', 'announcement', 'custom']);
            $table->text('message');
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
