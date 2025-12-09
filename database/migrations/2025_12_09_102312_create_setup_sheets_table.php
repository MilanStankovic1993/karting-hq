<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setup_sheets', function (Blueprint $table) {
            $table->id();

            // Header
            $table->date('date')->nullable();
            $table->string('time_label')->nullable(); // TIME / TEST name

            // Kart setup fields
            $table->string('chassis')->nullable();
            $table->string('carb')->nullable();
            $table->string('engine')->nullable();
            $table->string('sprocket')->nullable();
            $table->string('exhaust')->nullable();
            $table->string('spacer')->nullable();
            $table->string('axle')->nullable();
            $table->string('front_bar')->nullable();
            $table->string('ch_positions')->nullable();
            $table->string('caster')->nullable();
            $table->string('camber')->nullable();
            $table->string('tyres_type')->nullable();

            // Pressures
            $table->string('front_entry')->nullable();
            $table->string('front_mid')->nullable();
            $table->string('front_exit')->nullable();
            $table->string('rear_entry')->nullable();
            $table->string('rear_mid')->nullable();
            $table->string('rear_exit')->nullable();

            // Engine needles
            $table->string('engine_low')->nullable();
            $table->string('engine_mid')->nullable();
            $table->string('engine_top')->nullable();

            // Results
            $table->string('temperature')->nullable();
            $table->string('fastest_lap')->nullable();
            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_sheets');
    }
};
