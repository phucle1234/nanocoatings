<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CopyEnglishTranslationsSeeder extends Seeder
{
    private string $sourceLanguage = 'en';

    /** @var array<string, string> table => foreign key column */
    private array $translationTables = [
        'product_category_translations' => 'category_id',
        'product_translations' => 'product_id',
        'product_attribute_translations' => 'attribute_id',
        'product_attribute_value_translations' => 'attribute_value_id',
        'postcategory_translations' => 'postcategory_id',
        'post_translations' => 'post_id',
    ];

    public function run(): void
    {
        $targetLanguages = $this->resolveTargetLanguages();

        if (empty($targetLanguages)) {
            $this->command?->warn('Không có ngôn ngữ đích nào để copy từ tiếng Anh.');
            return;
        }

        $this->command?->info('Copy bản dịch EN → ' . implode(', ', $targetLanguages));

        foreach ($this->translationTables as $table => $foreignKey) {
            if (!Schema::hasTable($table)) {
                $this->command?->warn("Bỏ qua bảng không tồn tại: {$table}");
                continue;
            }

            $copied = $this->copyTable($table, $foreignKey, $targetLanguages);
            $this->command?->info("  {$table}: +{$copied} bản ghi");
        }

        $this->command?->info('Hoàn tất copy bản dịch tiếng Anh.');
    }

    /**
     * Copy EN sang các ngôn ngữ mới (fi, ge, po, sw...), không đè bản vi có sẵn.
     */
    private function resolveTargetLanguages(): array
    {
        $supported = array_keys(config('languages.supported', []));

        return array_values(array_filter($supported, function (string $code) {
            return !in_array($code, [$this->sourceLanguage, 'vi'], true);
        }));
    }

    private function copyTable(string $table, string $foreignKey, array $targetLanguages): int
    {
        $sourceRows = DB::table($table)
            ->where('language', $this->sourceLanguage)
            ->get();

        $copied = 0;

        foreach ($sourceRows as $row) {
            $rowData = (array) $row;

            foreach ($targetLanguages as $targetLanguage) {
                $exists = DB::table($table)
                    ->where($foreignKey, $rowData[$foreignKey])
                    ->where('language', $targetLanguage)
                    ->exists();

                if ($exists) {
                    continue;
                }

                unset($rowData['id']);
                $rowData['language'] = $targetLanguage;
                $rowData['created_at'] = now();
                $rowData['updated_at'] = now();

                DB::table($table)->insert($rowData);
                $copied++;
            }
        }

        return $copied;
    }
}
