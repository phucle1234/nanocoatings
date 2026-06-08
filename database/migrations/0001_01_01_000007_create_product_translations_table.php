<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $supportedLanguages = array_keys(config('languages.supported', ['en', 'vi']));
        $defaultLanguage = config('languages.default', 'vi');

        Schema::create('product_translations', function (Blueprint $table) use ($supportedLanguages, $defaultLanguage) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->enum('language', $supportedLanguages)->default($defaultLanguage);
            $table->string('name');

            $table->text('description')->nullable();

            $table->text('short_description')->nullable();
            $table->text('outstanding_features')->nullable(); //tính năng nổi bật
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('slug'); // ✅ Bỏ unique() ở đây
            $table->text('features')->nullable(); // JSON hoặc text
            $table->text('specifications')->nullable(); // JSON hoặc text

            // Text search field for Meilisearch + MySQL FULLTEXT backup
            $table->text('text_search')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['product_id', 'language']);
            $table->index(['product_id', 'language']);
            $table->index('slug');
            $table->index('language');

            // FULLTEXT index for text_search
            $table->fullText('text_search', 'product_translations_text_search_fulltext');
            
            // ✅ Thêm composite unique constraint: slug + language
            $table->unique(['slug', 'language'], 'product_translations_slug_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_translations');
    }
};
