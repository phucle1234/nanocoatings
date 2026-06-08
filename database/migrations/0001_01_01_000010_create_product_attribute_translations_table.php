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

        Schema::create('product_attribute_translations', function (Blueprint $table) use ($supportedLanguages, $defaultLanguage) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->enum('language', $supportedLanguages)->default($defaultLanguage);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('attribute_id')->references('id')->on('product_attributes')->onDelete('cascade');
            $table->unique(['attribute_id', 'language']);
            $table->index(['attribute_id', 'language']);
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_translations');
    }
};
