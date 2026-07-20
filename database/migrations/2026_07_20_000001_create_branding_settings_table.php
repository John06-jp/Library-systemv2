<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branding_settings', function (Blueprint $table) {
            $table->id();
            $table->string('banner_path')->nullable();
            $table->string('opac_banner_path')->nullable();
            $table->string('opac_logo_path')->nullable();
            $table->string('opac_default_book_cover_path')->nullable();
            $table->string('sidebar_logo_path')->nullable();
            $table->string('sidebar_brand_name', 60)->nullable();
            $table->string('sidebar_brand_subtitle', 100)->nullable();
            $table->string('primary_color', 7)->nullable();
            $table->string('secondary_color', 7)->nullable();
            $table->string('accent_color', 7)->nullable();
            $table->string('sidebar_background_color', 7)->nullable();
            $table->string('sidebar_text_color', 7)->nullable();
            $table->string('sidebar_brand_text_color', 7)->nullable();
            $table->string('sidebar_active_color', 7)->nullable();
            $table->string('sidebar_hover_background_color', 7)->nullable();
            $table->string('sidebar_hover_text_color', 7)->nullable();
            $table->string('button_color', 7)->nullable();
            $table->string('sidebar_footer_background_color', 7)->nullable();
            $table->string('table_header_color', 7)->nullable();
            $table->string('table_header_text_color', 7)->nullable();
            $table->string('table_border_color', 7)->nullable();
            $table->string('table_hover_color', 7)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branding_settings');
    }
};
