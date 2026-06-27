<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('product_categories', 'display_scopes')) {
                $table->json('display_scopes')->nullable()->after('sort_order');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'display_scopes')) {
                $table->json('display_scopes')->nullable()->after('sort_order');
            }
        });

        $default = json_encode(['homepage' => true, 'sector_ids' => []]);

        DB::table('product_categories')->whereNull('display_scopes')->update([
            'display_scopes' => $default,
        ]);

        DB::table('products')->whereNull('display_scopes')->update([
            'display_scopes' => $default,
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'display_scopes')) {
                $table->dropColumn('display_scopes');
            }
        });

        Schema::table('product_categories', function (Blueprint $table) {
            if (Schema::hasColumn('product_categories', 'display_scopes')) {
                $table->dropColumn('display_scopes');
            }
        });
    }
};
