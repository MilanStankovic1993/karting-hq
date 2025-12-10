<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
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

            $table->string('name');
            $table->string('track')->nullable();
            $table->date('date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
