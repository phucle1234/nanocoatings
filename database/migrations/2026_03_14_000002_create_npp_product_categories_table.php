<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('npp_product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');        // id của NPP trong bảng users
            $table->unsignedBigInteger('category_id');    // id của danh mục trong bảng product_categories
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('category_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('cascade');

            // Mỗi NPP chỉ được gán 1 lần với mỗi danh mục
            $table->unique(['user_id', 'category_id']);

            $table->index('user_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npp_product_categories');
    }
};
