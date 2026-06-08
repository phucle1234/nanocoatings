<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_category_translations', function (Blueprint $table) {
            $table->string('link_type')->default('detail')->after('image_urls');
            $table->string('youtube_url')->nullable()->after('link_type');
        });
    }

    public function down(): void
    {
        Schema::table('product_category_translations', function (Blueprint $table) {
            $table->dropColumn(['link_type', 'youtube_url']);
        });
    }
};
