<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
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
            $table->string('short_name')->nullable(); // npr. MIL

            // "team" ovde je npr. karting tim (ne tenant)
            $table->string('team')->nullable();

            $table->unsignedInteger('kart_number')->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
