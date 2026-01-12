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

            // VEZE
            $table->foreignId('race_id')
                ->nullable()
                ->constrained('races')
                ->nullOnDelete();

            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();

            // HEADER
            $table->date('date')->nullable();
            $table->string('time_label')->nullable();

            // KART SETUP
            $table->string('chassis')->nullable();
            $table->string('carb')->nullable();
            $table->string('engine')->nullable();
            $table->string('sprocket')->nullable();
            $table->string('exhaust')->nullable();
            $table->string('spacer')->nullable();
            $table->string('axle')->nullable();
            $table->string('front_bar')->nullable();

            // ✅ CH POSITION – REALNO (FRONT / REAR)
            $table->string('ch_position_front')->nullable();
            $table->string('ch_position_rear')->nullable();

            // CAMBER / CASTER
            $table->string('camber')->nullable();
            $table->string('caster')->nullable();

            $table->string('tyres_type')->nullable();

            // ✅ TYRE PRESSURES – 2x2 (COLD)
            $table->decimal('pressure_cold_fl', 5, 2)->nullable();
            $table->decimal('pressure_cold_fr', 5, 2)->nullable();
            $table->decimal('pressure_cold_rl', 5, 2)->nullable();
            $table->decimal('pressure_cold_rr', 5, 2)->nullable();

            // ✅ TYRE PRESSURES – 2x2 (HOT)
            $table->decimal('pressure_hot_fl', 5, 2)->nullable();
            $table->decimal('pressure_hot_fr', 5, 2)->nullable();
            $table->decimal('pressure_hot_rl', 5, 2)->nullable();
            $table->decimal('pressure_hot_rr', 5, 2)->nullable();

            // BALANCE / HANDLING (slider -3..3)
            $table->string('front_entry')->nullable();
            $table->string('front_mid')->nullable();
            $table->string('front_exit')->nullable();
            $table->string('rear_entry')->nullable();
            $table->string('rear_mid')->nullable();
            $table->string('rear_exit')->nullable();

            // ENGINE NEEDLES (slider -3..3)
            $table->string('engine_low')->nullable();
            $table->string('engine_mid')->nullable();
            $table->string('engine_top')->nullable();

            // RESULTS
            $table->string('temperature')->nullable();
            $table->string('lap_time')->nullable();
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
