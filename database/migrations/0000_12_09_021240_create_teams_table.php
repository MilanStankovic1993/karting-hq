<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();

            // 🧾 Osnovno
            $table->string('name');
            $table->text('notes')->nullable();

            // 👤 Audit (za sada BEZ foreign key constraints – samo ID-jevi)
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->unsignedBigInteger('updated_by_id')->nullable();

            // 💳 Pretplata
            $table->boolean('is_active')
                ->default(true)
                ->comment('Super admin manual override ON/OFF');

            $table->timestamp('subscription_started_at')
                ->nullable()
                ->comment('When the subscription was activated');

            $table->timestamp('subscription_expires_at')
                ->nullable()
                ->comment('When the subscription will expire');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
