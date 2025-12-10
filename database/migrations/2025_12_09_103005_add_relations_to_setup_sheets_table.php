<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setup_sheets', function (Blueprint $table) {
            // trka / event – veza ka races
            $table->foreignId('race_id')
                ->nullable()
                ->constrained('races')
                ->nullOnDelete();

            // vozač – veza ka drivers
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('setup_sheets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('race_id');
            $table->dropConstrainedForeignId('driver_id');
        });
    }
};
