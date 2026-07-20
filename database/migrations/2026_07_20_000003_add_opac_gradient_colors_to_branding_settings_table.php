<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branding_settings', function (Blueprint $table) {
            $table->string('opac_gradient_start_color', 7)->nullable()->after('accent_color');
            $table->string('opac_gradient_end_color', 7)->nullable()->after('opac_gradient_start_color');
        });
    }

    public function down(): void
    {
        Schema::table('branding_settings', function (Blueprint $table) {
            $table->dropColumn([
                'opac_gradient_start_color',
                'opac_gradient_end_color',
            ]);
        });
    }
};
