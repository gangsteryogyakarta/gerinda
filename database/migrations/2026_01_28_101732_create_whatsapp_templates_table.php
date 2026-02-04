<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category'); // promosi, event, birthday, survey, transaksi
            $table->text('content'); // Template dengan placeholder
            $table->json('variables')->nullable(); // List variabel yang digunakan
            $table->json('conditions')->nullable(); // Kondisi if-else
            $table->string('image_url')->nullable();
            $table->boolean('is_system')->default(false); // Built-in templates
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
