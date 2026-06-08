<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_postcategory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('postcategory_id')->constrained('postcategories')->onDelete('cascade');
            $table->boolean('is_primary')->default(false); // Đánh dấu danh mục chính
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['post_id', 'postcategory_id']);
            $table->index('is_primary');
            $table->index('sort_order');

            // Unique constraint để tránh trùng lặp
            $table->unique(['post_id', 'postcategory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_postcategory');
    }
};
