<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class GenerateProductTextSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:generate-text-search 
                            {--chunk=50 : Number of products per chunk}
                            {--force : Force regenerate all products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate text_search field for all product translations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chunk = (int) $this->option('chunk');
        $force = $this->option('force');

        $query = Product::query();

        if (!$force) {
            // Chỉ generate cho products chưa có text_search
            $query->whereHas('translations', function ($q) {
                $q->whereNull('text_search');
            });
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('✅ All products already have text_search generated!');
            return 0;
        }

        $this->info("🚀 Generating text_search for {$total} products...");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        $query->with([
            'translations',
            'categories.translations',
            'attributeValues.attribute.translations',
            'attributeValues.translations'
        ])->chunk($chunk, function ($products) use ($bar, &$success, &$failed) {
            foreach ($products as $product) {
                try {
                    $product->generateAndSaveTextSearch();
                    $success++;
                } catch (\Exception $e) {
                    $this->error("\nError for product {$product->id}: " . $e->getMessage());
                    $failed++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Successfully generated: {$success}");
        if ($failed > 0) {
            $this->error("❌ Failed: {$failed}");
        }

        return 0;
    }
}
