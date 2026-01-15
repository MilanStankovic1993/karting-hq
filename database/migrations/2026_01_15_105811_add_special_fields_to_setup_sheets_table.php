<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setup_sheets', function (Blueprint $table) {
            // wheels (standard)
            $table->string('wheels')->nullable()->after('front_bar');

            // special (collapsible)
            $table->string('rear_hubs')->nullable()->after('wheels');
            $table->unsignedInteger('front_hubs')->nullable()->after('rear_hubs');
            $table->string('special_axle')->nullable()->after('front_hubs');
            $table->string('bearing_carriers')->nullable()->after('special_axle');
            $table->unsignedInteger('rear_width')->nullable()->after('bearing_carriers');
        });
    }

    public function down(): void
    {
        Schema::table('setup_sheets', function (Blueprint $table) {
            $table->dropColumn([
                'wheels',
                'rear_hubs',
                'front_hubs',
                'special_axle',
                'bearing_carriers',
                'rear_width',
            ]);
        });
    }
};
