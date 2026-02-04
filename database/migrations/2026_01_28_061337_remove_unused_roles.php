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
        $rolesToDelete = ['operator', 'checkin-officer', 'viewer', 'Super Admin'];
        
        foreach ($rolesToDelete as $roleName) {
            \Spatie\Permission\Models\Role::where('name', $roleName)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration as we don't want to restore these legacy roles
    }
};
