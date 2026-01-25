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
        Schema::dropIfExists('lottery_draws');
        Schema::dropIfExists('lottery_prizes');
        
        if (Schema::hasColumn('events', 'enable_lottery')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('enable_lottery');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse, this is a destructive removal
    }
};
