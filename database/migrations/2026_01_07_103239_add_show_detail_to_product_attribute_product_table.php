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
        Schema::table('product_attribute_product', function (Blueprint $table) {
            $table->string('show_detail', 1)->default('N')->after('attribute_value_id')
                ->comment('Y = Hiển thị trong thông số kỹ thuật, N = Không hiển thị');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attribute_product', function (Blueprint $table) {
            $table->dropColumn('show_detail');
        });
    }
};
