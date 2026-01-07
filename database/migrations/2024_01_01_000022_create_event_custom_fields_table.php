<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Custom form fields untuk masing-masing event
        Schema::create('event_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('field_name');
            $table->string('field_label');
            $table->enum('field_type', ['text', 'textarea', 'number', 'select', 'checkbox', 'radio', 'date', 'file']);
            $table->json('field_options')->nullable(); // For select, checkbox, radio
            $table->text('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('event_id');
            $table->unique(['event_id', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_custom_fields');
    }
};
