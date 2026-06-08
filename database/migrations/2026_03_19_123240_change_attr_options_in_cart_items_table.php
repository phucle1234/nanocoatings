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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('unique_cart_product');
            $table->string('options')->nullable()->change();
            $table->unique(['cart_id', 'product_id', 'options'], 'unique_cart_product_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('unique_cart_product_options');
            $table->json('options')->nullable()->change();
            $table->unique(['cart_id', 'product_id'], 'unique_cart_product');
        });
    }
};
