<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postcategory_id')->nullable()->constrained('postcategories')->onDelete('set null');
            $table->string('icon')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('published_at');
            $table->index('sort_order');
            $table->index('view_count');
            $table->index('postcategory_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
