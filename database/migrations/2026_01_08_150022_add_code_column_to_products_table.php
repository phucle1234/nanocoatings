<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Thêm cột code để lưu SKU hiện tại (có thể trùng nhau)
            $table->string('code')->nullable()->after('category_id');
            $table->index('code');
        });

        // Bỏ unique constraint trên sku
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
        });

        // Cập nhật sku hiện tại thành code và tạo sku mới từ ID
        DB::statement("UPDATE products SET code = sku, sku = CONCAT('PROD-', id)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Khôi phục unique constraint trên sku
            $table->unique('sku');
            
            // Xóa cột code
            $table->dropColumn('code');
        });
    }
};
