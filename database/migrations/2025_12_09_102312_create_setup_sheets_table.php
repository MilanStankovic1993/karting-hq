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

            // KO JE KREIRAO / IZMENIO
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // TENANT TEAM
            $table->foreignId('team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            // VEZE (direktno ovde, kad radimo fresh bazu)
            $table->foreignId('race_id')
                ->nullable()
                ->constrained('races')
                ->nullOnDelete();

            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();

            // Header
            $table->date('date')->nullable();
            $table->string('time_label')->nullable();

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

            // ✅ po zahtevu: camber pa caster (UI rešava redosled, ali kolone su tu)
            $table->string('camber')->nullable();
            $table->string('caster')->nullable();

            $table->string('tyres_type')->nullable();

            // ✅ Tyre pressures (cold 2x2 + hot 2x2)
            $table->decimal('pressure_cold_fl', 5, 2)->nullable();
            $table->decimal('pressure_cold_fr', 5, 2)->nullable();
            $table->decimal('pressure_cold_rl', 5, 2)->nullable();
            $table->decimal('pressure_cold_rr', 5, 2)->nullable();

            $table->decimal('pressure_hot_fl', 5, 2)->nullable();
            $table->decimal('pressure_hot_fr', 5, 2)->nullable();
            $table->decimal('pressure_hot_rl', 5, 2)->nullable();
            $table->decimal('pressure_hot_rr', 5, 2)->nullable();

            // Balance / Handling (slider -3..3) - ostaje string radi kompatibilnosti
            $table->string('front_entry')->nullable();
            $table->string('front_mid')->nullable();
            $table->string('front_exit')->nullable();
            $table->string('rear_entry')->nullable();
            $table->string('rear_mid')->nullable();
            $table->string('rear_exit')->nullable();

            // Engine needles (slider -3..3) - ostaje string radi kompatibilnosti
            $table->string('engine_low')->nullable();
            $table->string('engine_mid')->nullable();
            $table->string('engine_top')->nullable();

            // Results
            $table->string('temperature')->nullable();
            $table->string('lap_time')->nullable();     // ✅ između temp i fastest lap
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
