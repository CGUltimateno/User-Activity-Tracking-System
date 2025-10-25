<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->text('description')->nullable();
        });

        // seed default settings
        DB::table('settings')->insert([
            ['key' => 'idle_timeout_seconds', 'value' => '5', 'description' => 'Idle timeout in seconds'],
            ['key' => 'monitoring_enabled', 'value' => '1', 'description' => 'Enable inactivity monitoring'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
