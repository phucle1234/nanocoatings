<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            // post_type: 'banner', 'info', 'slider'
            $table->string('post_type', 20)->default('info')->after('icon')
                ->comment('banner: Banner đầu trang | info: Bài viết giới thiệu | slider: Slider sections');
            
            // section_type: 'design', 'technology', 'experience' (chỉ dùng cho post_type = 'slider')
            $table->string('section_type', 30)->nullable()->after('post_type')
                ->comment('design, technology, experience (chỉ dùng với post_type=slider)');
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['post_type', 'section_type']);
        });
    }
};