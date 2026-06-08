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

        Schema::create('product_attribute_value_translations', function (Blueprint $table) use ($supportedLanguages, $defaultLanguage) {
            $table->id();
            $table->unsignedBigInteger('attribute_value_id');
            $table->enum('language', $supportedLanguages)->default($defaultLanguage);
            $table->string('value');
            $table->timestamps();

            $table->foreign('attribute_value_id')->references('id')->on('product_attribute_values')->onDelete('cascade');
            $table->unique(['attribute_value_id', 'language'], 'pavt_attr_val_id_lang_unique');
            $table->index(['attribute_value_id', 'language'], 'pavt_attr_val_lang_index');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value_translations');
    }
};
