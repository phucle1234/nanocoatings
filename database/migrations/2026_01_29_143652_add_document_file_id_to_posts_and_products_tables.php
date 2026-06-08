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
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('document_file_id')->nullable()->after('meta_keywords')->constrained('uploaded_files')->onDelete('set null');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('document_file_id')->nullable()->after('image_urls')->constrained('uploaded_files')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['document_file_id']);
            $table->dropColumn('document_file_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['document_file_id']);
            $table->dropColumn('document_file_id');
        });
    }
};
