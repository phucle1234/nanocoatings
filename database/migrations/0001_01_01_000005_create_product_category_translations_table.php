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

        Schema::create('product_category_translations', function (Blueprint $table) use ($supportedLanguages, $defaultLanguage) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->enum('language', $supportedLanguages)->default($defaultLanguage);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('slug'); // ✅ Bỏ unique() ở đây
            $table->json('image_urls')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->unique(['category_id', 'language']);
            $table->index(['category_id', 'language']);
            $table->index('slug');
            $table->index('language');
            // ✅ Thêm composite unique constraint: slug + language
            $table->unique(['slug', 'language'], 'product_category_translations_slug_language_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_category_translations');
    }
};
