<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $translationTables = [
        'product_category_translations',
        'product_translations',
        'product_attribute_translations',
        'product_attribute_value_translations',
        'postcategory_translations',
        'post_translations',
    ];

    public function up(): void
    {
        $supportedLanguages = array_keys(config('languages.supported', ['en', 'vi']));
        $defaultLanguage = config('languages.default', 'en');
        $enumList = "'" . implode("','", $supportedLanguages) . "'";

        foreach ($this->translationTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'language')) {
                continue;
            }

            DB::statement(
                "ALTER TABLE `{$table}` MODIFY `language` ENUM({$enumList}) NOT NULL DEFAULT '{$defaultLanguage}'"
            );
        }
    }

    public function down(): void
    {
        $enumList = "'en','vi'";

        foreach ($this->translationTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'language')) {
                continue;
            }

            DB::statement(
                "ALTER TABLE `{$table}` MODIFY `language` ENUM({$enumList}) NOT NULL DEFAULT 'en'"
            );
        }
    }
};
