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
        Schema::create('product_attribute_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_value_id');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('attribute_value_id')->references('id')->on('product_attribute_values')->onDelete('cascade');
            $table->unique(['product_id', 'attribute_value_id']);
            $table->index(['product_id', 'attribute_value_id']);
            $table->index(['attribute_value_id', 'product_id']); // <----- Thêm chiều ngược lại để tối ưu filter nhiều thuộc tính cùng lúc
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_product');
    }
};
