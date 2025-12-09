<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setup_sheets', function (Blueprint $table) {
            // trka / event – za sada tabela races (možeš je posle proširiti)
            $table->foreignId('race_id')
                ->nullable()
                ->constrained('races')
                ->nullOnDelete();

            // vozač – ako budeš imao drivers tabelu
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();

            // korisnik koji je uneo sheet
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('setup_sheets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('race_id');
            $table->dropConstrainedForeignId('driver_id');
            $table->dropConstrainedForeignId('created_by_id');
        });
    }
};
