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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // APP ROLE – SUPER_ADMIN | TECHNICIAN | WORKER
            $table->string('role')->default('WORKER');

            // LOGIN PREKO USERNAME-A
            $table->string('username')->unique();

            // Email ostavljamo zbog Filament-a, notifikacija, reset lozinke itd.
            $table->string('email')->unique()->nullable();

            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            // ✅ User manual active flag (Team deactivation can flip this)
            $table->boolean('is_active')
                ->default(true)
                ->comment('Manual user disable (e.g. when team is deactivated)');

            // TENANT TEAM – svi sem super admina će imati team_id
            $table->foreignId('team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
