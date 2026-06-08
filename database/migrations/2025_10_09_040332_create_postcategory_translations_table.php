<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $supportedLanguages = array_keys(config('languages.supported', ['en', 'vi']));
        $defaultLanguage = config('languages.default', 'vi');

        Schema::create('postcategory_translations', function (Blueprint $table) use ($supportedLanguages, $defaultLanguage) {
            $table->id();
            $table->foreignId('postcategory_id')->constrained()->onDelete('cascade');
            $table->enum('language', $supportedLanguages)->default($defaultLanguage);
            // Nội dung hiển thị
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug'); // ✅ Bỏ unique() ở đây
            $table->json('image_urls')->nullable();
            $table->timestamps();
            // 🧠 SEO fields
            $table->string('meta_title')->nullable();                // tiêu đề SEO (<title>)
            $table->string('meta_description', 500)->nullable();     // mô tả SEO
            $table->string('meta_keywords', 500)->nullable();        // từ khóa SEO
            $table->string('canonical_url')->nullable();             // link chuẩn (tránh trùng)
            $table->string('og_title')->nullable();                  // Facebook / Zalo share title
            $table->string('og_description', 500)->nullable();       // Facebook / Zalo description
            $table->string('og_image')->nullable();                  // Ảnh share mạng xã hội

            // Link URL
            $table->string('url')->nullable();

            $table->index('slug');
            $table->index('language');
            $table->index(['postcategory_id', 'language']);
            $table->unique(['postcategory_id', 'language']);
            $table->unique(['slug', 'language'], 'postcategory_translations_slug_language_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postcategory_translations');
    }
};
