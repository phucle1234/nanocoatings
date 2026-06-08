<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// (Đây là nơi chứa tiêu đề, nội dung và metadata cho từng ngôn ngữ)
return new class extends Migration {
    public function up(): void
    {
        $supportedLanguages = array_keys(config('languages.supported', ['en', 'vi']));
        $defaultLanguage = config('languages.default', 'vi');

        Schema::create('post_translations', function (Blueprint $table) use ($supportedLanguages, $defaultLanguage) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->enum('language', $supportedLanguages)->default($defaultLanguage);

            // Nội dung chính
            $table->string('title');
            $table->string('slug')->nullable();
            $table->longText('content')->nullable();
            $table->text('excerpt')->nullable();

            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();

            // Open Graph Fields
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();

            // Multiple images support
            $table->json('image_urls')->nullable();

            // Link URL
            $table->string('url')->nullable();

            $table->timestamps();

            $table->unique(['post_id', 'language']);
            $table->index('language');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_translations');
    }
};
