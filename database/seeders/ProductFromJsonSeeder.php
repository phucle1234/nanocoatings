<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductFromJsonSeeder extends Seeder
{
    private $categories = []; // Cache danh mục theo code
    private $categoriesByName = []; // Cache danh mục theo tên (uppercase)
    private $jsonFileName = ''; // Tên file JSON đang xử lý

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ trước khi import (chỉ xóa 1 lần)
        $this->cleanupOldData();

        $jsonFiles = [
            database_path('seeders/data/advenza.json'),
            database_path('seeders/data/xetai_RADIAL.json'),
            database_path('seeders/data/xe_tai_BIAS_nang.json'),
            database_path('seeders/data/xe_tai_BIAS_nhe.json'),
            database_path('seeders/data/xe_tai_NHE_PCR.json'),
            database_path('seeders/data/xe_may_Tubeless.json'),
            database_path('seeders/data/xe-dap-truyenthong.json'),
            database_path('seeders/data/xe-dap-thethao.json'),
            database_path('seeders/data/xe-chuyendung-cong-nghiep.json'),
            database_path('seeders/data/xe-chuyendung-nongnghiep.json'),
            database_path('seeders/data/xe_may_sam_xe_may.json'),
            database_path('seeders/data/xe_tai_sam_xe_tai.json'),
            database_path('seeders/data/xe_tai_yem_bias.json'),
            database_path('seeders/data/xe_tai_xam_bias.json'),
            database_path('seeders/data/xe_tai_yem_radial.json'),
            database_path('seeders/data/xe_tai_xam_radial.json'),
            database_path('seeders/data/xe_dien_xe_dap.json'),
            database_path('seeders/data/xe_dien_xe_may.json'),
            database_path('seeders/data/xe-dap-xam-xe-dap.json'),
        ];

        // Load danh mục có sẵn vào cache (chỉ load 1 lần)
        $this->loadExistingCategories();

        $totalProcessedCount = 0;
        $totalErrorCount = 0;

        // ✅ Duyệt qua từng file JSON
        foreach ($jsonFiles as $jsonFile) {
            if (!file_exists($jsonFile)) {
                $this->command->warn("⚠️  File JSON không tồn tại: {$jsonFile}");
                continue;
            }

            // Lưu tên file để dùng cho logic gán danh mục
            $this->jsonFileName = basename($jsonFile);
            $this->command->info("");
            $this->command->info("═══════════════════════════════════════════════════════");
            $this->command->info("📁 Đang xử lý file: {$this->jsonFileName}");
            $this->command->info("═══════════════════════════════════════════════════════");

            try {
                // Đọc file JSON
                $jsonContent = File::get($jsonFile);

                // ✅ Xử lý file có nhiều phần (array đầu + Sheet1)
                $sheet1Pos = strpos($jsonContent, '],');
                if ($sheet1Pos !== false && strpos($jsonContent, '"Sheet1"') !== false) {
                    $this->command->info("⚠️  Phát hiện file có nhiều phần, chỉ lấy phần đầu tiên...");
                    $firstPart = substr($jsonContent, 0, $sheet1Pos + 1);
                    $jsonContent = $firstPart;
                }

                // Decode JSON
                $data = json_decode($jsonContent, true);

                // Kiểm tra lỗi JSON
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->command->error("File JSON không hợp lệ: " . json_last_error_msg());
                    continue; // Bỏ qua file này, tiếp tục file tiếp theo
                }

                // Xử lý cấu trúc dữ liệu
                $products = [];
                // ✅ Kiểm tra an toàn hơn
                if (!is_array($data)) {
                    $this->command->error("File JSON không phải là array. Data type: " . gettype($data));
                    continue;
                }

                if (isset($data[0]) && is_array($data[0])) {
                    // Trường hợp 1: Array trực tiếp (indexed array)
                    $products = $data;
                } elseif (isset($data['Sheet']) && is_array($data['Sheet'])) {
                    // Trường hợp 2: Object với key 'Sheet'
                    $products = $data['Sheet'];
                } elseif (isset($data['Sheet1']) && is_array($data['Sheet1'])) {
                    // Trường hợp 3: Object với key 'Sheet1'
                    $products = $data['Sheet1'];
                } else {
                    $this->command->error("File JSON không có cấu trúc đúng. Data type: " . gettype($data));
                    if (is_array($data)) {
                        $this->command->error("Available keys: " . implode(', ', array_keys($data)));
                    }
                    continue;
                }
                if (empty($products) || !is_array($products)) {
                    $this->command->error("Không tìm thấy dữ liệu sản phẩm trong file: {$this->jsonFileName}");
                    continue; // Bỏ qua file này
                }

                // ✅ Giới hạn số lượng sản phẩm theo môi trường
                $appUrl = env('APP_URL', null);
                if ($appUrl == 'http://127.0.0.1:8000') {
                    $products = array_slice($products, 0, 2);
                    $this->command->info("🔧 Môi trường LOCAL: Giới hạn 5 sản phẩm");
                } else {
                    $this->command->info("🌐 Môi trường PRODUCTION: Seed tất cả sản phẩm");
                }

                $this->command->info("Tìm thấy " . count($products) . " sản phẩm trong file: {$this->jsonFileName}");

                $processedCount = 0;
                $errorCount = 0;
                $batchSize = 100;
                $validProducts = [];
                foreach ($products as $index => $item) {
                    if (is_array($item) && !empty($item)) {
                        $validProducts[] = $item;
                    }
                }

                if (empty($validProducts)) {
                    $this->command->warn("⚠️  Không có sản phẩm hợp lệ trong file: {$this->jsonFileName}");
                    continue;
                }

                $products = $validProducts;

                foreach (array_chunk($products, $batchSize) as $batch) {
                    foreach ($batch as $rowIndex => $rowData) {
                        try {
                            if (!is_array($rowData)) {
                                continue;
                            }
                            // Bỏ qua dòng trống
                            if (empty($rowData) || !is_array($rowData)) {
                                continue;
                            }

                            // Lấy danh mục phù hợp cho sản phẩm
                            $category = $this->getCategoryForProduct($rowData);

                            // Bỏ qua sản phẩm nếu không có danh mục
                            if ($category === null) {
                                $this->command->warn("  ⚠ Bỏ qua sản phẩm vì không có danh mục phù hợp");
                                continue;
                            }

                            // Tạo sản phẩm
                            $productResult = $this->createProduct($rowData, $category->id, $rowIndex);
                            $productId = $productResult['id'];
                            $isNew = $productResult['is_new'];

                            if ($isNew) {
                                // Chỉ tạo translations khi là sản phẩm mới
                                $this->createTranslations($productId, $rowData);
                            }

                            // Dù mới hay cũ vẫn gán thêm thuộc tính
                            $this->assignAttributes($productId, $rowData);

                            // ✅ Tạo vehicle fitments từ attributes
                            $this->createVehicleFitments($productId, $rowData);

                            $processedCount++;
                            $totalProcessedCount++;

                            if ($processedCount % 50 == 0) {
                                $this->command->info("  Đã xử lý {$processedCount} sản phẩm từ file {$this->jsonFileName}...");
                            }
                        } catch (\Exception $e) {
                            $errorCount++;
                            $totalErrorCount++;
                            $maSP = is_array($rowData) ? ($rowData['MÃ SP'] ?? 'N/A') : 'N/A';
                            Log::error("  Lỗi xử lý sản phẩm dòng " . ($rowIndex + 1) . " trong file {$this->jsonFileName} (MÃ SP: {$maSP}): " . $e->getMessage());
                            $this->command->error("  Lỗi xử lý sản phẩm dòng " . ($rowIndex + 1) . " trong file {$this->jsonFileName} (MÃ SP: {$maSP}): " . $e->getMessage());
                            $this->command->error("  Stack trace: " . $e->getTraceAsString());
                        }
                    }
                }

                $this->command->info("✅ Hoàn thành file {$this->jsonFileName}: {$processedCount} sản phẩm, {$errorCount} lỗi");
            } catch (\Exception $e) {
                $this->command->error("❌ Lỗi đọc file JSON {$this->jsonFileName}: " . $e->getMessage());
                $totalErrorCount++;
            }
        }

        // ✅ Tổng kết sau khi xử lý hết 3 file
        $this->command->info("");
        $this->command->info("═══════════════════════════════════════════════════════");
        $this->command->info("🎉 HOÀN THÀNH TẤT CẢ CÁC FILE!");
        $this->command->info("   📦 Tổng số sản phẩm đã xử lý: {$totalProcessedCount}");
        $this->command->info("   ❌ Tổng số lỗi: {$totalErrorCount}");
        $this->command->info("═══════════════════════════════════════════════════════");

        // Kiểm tra và xóa dữ liệu thừa sau khi import (chỉ 1 lần)
        $this->cleanupAfterImport();

        // ✅ Reindex Meilisearch sau khi seed xong (chỉ 1 lần)
        $this->reindexMeilisearch();
    }

    /**
     * Reindex Meilisearch sau khi seed products
     */
    private function reindexMeilisearch()
    {
        $this->command->info("🔍 Đang reindex Meilisearch...");

        try {
            // Import tất cả products vào Meilisearch
            $this->command->call('scout:import', [
                'model' => 'App\\Models\\Product',
            ]);

            $this->command->info("✅ Reindex Meilisearch thành công!");
        } catch (\Exception $e) {
            $this->command->error("❌ Lỗi reindex Meilisearch: " . $e->getMessage());
            $this->command->warn("Bạn có thể chạy manual: php artisan scout:import \"App\\Models\\Product\"");
        }
    }

    /**
     * Load danh mục có sẵn từ database vào cache
     */
    private function loadExistingCategories()
    {
        $this->command->info("Đang load danh mục có sẵn từ database...");

        // Load tất cả danh mục có sẵn
        $categories = DB::table('product_categories')->get();

        foreach ($categories as $category) {
            // Lấy tên danh mục từ translations (tiếng Việt)
            $translation = DB::table('product_category_translations')
                ->where('category_id', $category->id)
                ->where('language', 'vi')
                ->first();

            $categoryName = $translation ? $translation->name : $category->code;

            // Cache theo code và theo tên (uppercase để so sánh không phân biệt hoa thường)
            $this->categories[$category->code] = $category;
            $this->categoriesByName[strtoupper(trim($categoryName))] = $category;

            $this->command->info("  - Loaded category: {$categoryName} (code: {$category->code})");
        }

        $this->command->info("Đã load " . count($this->categories) . " danh mục có sẵn");
    }


    /**
     * Lấy danh mục phù hợp cho sản phẩm dựa trên Production Type
     * Ví dụ: "ADVENZA VENTURER AV568" → tìm danh mục "VENTURER"
     */
    // Trong method getCategoryForProduct(), thay đổi logic fallback:

    private function getCategoryForProduct($rowData)
    {
        // ✅ Kiểm tra an toàn: $rowData phải là array
        if (!is_array($rowData)) {
            $this->command->warn("  ⚠ rowData không phải là array");
            return null;
        }

        if ($this->jsonFileName === 'xe_may_Tubeless.json') {
            $tireLine = $rowData['DÒNG LỐP'] ?? $rowData['Tire Line'] ?? '';
            $tireLine = (string) $tireLine;

            // Normalize: trim + replace NBSP + collapse spaces
            $normalized = trim(str_replace("\u{00A0}", ' ', $tireLine));
            $normalized = preg_replace('/\s+/', ' ', $normalized);

            $this->command->info("DEBUG DÒNG LỐP: [" . $normalized . "] | FILE: " . $this->jsonFileName);
            if ($normalized !== '') {
                $normalized = ltrim($normalized, "\xEF\xBB\xBF");
                $upper = strtoupper($normalized);

                if ($upper !== '' && $upper[0] === 'E') {
                    // 1) ưu tiên theo code
                    $eSeriesCode = 'sam-lop-xe-may-lop-tubeless-euromina-e-series';
                    if (isset($this->categories[$eSeriesCode])) {
                        return $this->categories[$eSeriesCode];
                    }

                    // 2) fallback theo translation name (vi/en)
                    $eSeriesTrans = DB::table('product_category_translations')
                        ->whereIn('name', ['E Series', 'E-SERIES'])
                        ->first();
                    if ($eSeriesTrans) {
                        $cat = DB::table('product_categories')
                            ->where('id', $eSeriesTrans->category_id)
                            ->first();
                        if ($cat) {
                            return $cat;
                        }
                    }
                }

                // ✅ Rule 2: match theo tên danh mục
                $categoryNameUpper = strtoupper($normalized);
                if (isset($this->categoriesByName[$categoryNameUpper])) {
                    return $this->categoriesByName[$categoryNameUpper];
                }

                // ✅ Rule 3: fallback theo code (slug)
                $code = 'sam-lop-xe-may-lop-tubeless-euromina-' . Str::slug($normalized);
                if (isset($this->categories[$code])) {
                    return $this->categories[$code];
                }
            }

            // ✅ Rule 4: nếu không match -> Lốp đặc trưng Casumina
            $signatureCode = 'sam-lop-xe-may-lop-tubeless-euromina-lop-dac-trung-casumina';
            if (isset($this->categories[$signatureCode])) {
                return $this->categories[$signatureCode];
            }

            // Fallback cuối cùng về parent
            if (isset($this->categories['sam-lop-xe-may'])) {
                return $this->categories['sam-lop-xe-may'];
            }
        } elseif ($this->jsonFileName === 'xe_tai_BIAS_nang.json') {
            $categoryCode = 'sam-lop-xe-tai-lop-tai-nang-bias-nylon';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
        } elseif ($this->jsonFileName === 'xe_tai_BIAS_nhe.json') {
            $categoryCode = 'sam-lop-xe-tai-lop-tai-nhe-bias-nylon';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
        } elseif ($this->jsonFileName === 'xe_tai_NHE_PCR.json') {
            $categoryCode = 'sam-lop-xe-tai-lop-tai-nhe-pcr';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
        } elseif ($this->jsonFileName === 'xetai_RADIAL.json') {
            $productionType = $rowData['Chủng loại lốp'] ?? $rowData['Production Type'] ?? '';
            $productionType = (string) $productionType;

            if (!empty($productionType)) {
                $parts = preg_split('/\s+/', trim($productionType));
                $categoryName = (is_array($parts) && !empty($parts)) ? end($parts) : null;

                if (!empty($categoryName)) {
                    $categoryNameUpper = strtoupper(trim($categoryName));

                    // 1) Tìm theo tên (vi)
                    if (isset($this->categoriesByName[$categoryNameUpper])) {
                        return $this->categoriesByName[$categoryNameUpper];
                    }

                    // 2) Fallback theo code (grandchild)
                    $categoryCode = 'lop-oto-radial-bo-thep-' . Str::slug($categoryName);
                    if (isset($this->categories[$categoryCode])) {
                        return $this->categories[$categoryCode];
                    }
                }
            }

            if (isset($this->categories['sam-lop-xe-tai'])) {
                return $this->categories['sam-lop-xe-tai'];
            }
        } elseif ($this->jsonFileName === 'advenza.json') {
            // File advenza → logic cũ: tìm theo "Chủng loại lốp"
            $productionType = $rowData['Chủng loại lốp'] ?? $rowData['Production Type'] ?? '';

            // ✅ Đảm bảo $productionType là string
            $productionType = (string) $productionType;

            if (!empty($productionType)) {
                // Parse Production Type: "ADVENZA VENTURER AV568" → lấy "VENTURER" (phần thứ 2)
                $parts = explode(' ', trim($productionType));

                // ✅ Kiểm tra an toàn: đảm bảo $parts là array và có phần tử
                if (is_array($parts) && !empty($parts)) {
                    $categoryName = isset($parts[1]) && !empty($parts[1]) ? $parts[1] : $parts[0];
                    $categoryNameUpper = strtoupper(trim($categoryName));

                    // Tìm danh mục theo tên (không phân biệt hoa thường)
                    if (!empty($categoryNameUpper) && isset($this->categoriesByName[$categoryNameUpper])) {
                        $category = $this->categoriesByName[$categoryNameUpper];
                        $this->command->info("  ✓ Tìm thấy danh mục: {$categoryName} cho Chủng loại lốp: {$productionType}");
                        return $category;
                    } else {
                        $this->command->warn("  ⚠ Không tìm thấy danh mục: {$categoryName} cho Chủng loại lốp: {$productionType}");
                    }
                }
            }

            // Fallback: Tìm danh mục "Lốp advenza PCR" (parent của Venturer, Coverer...)
            if (isset($this->categories['lop-advenza-pcr'])) {
                $this->command->info("  ℹ Sử dụng danh mục fallback: Lốp advenza PCR");
                return $this->categories['lop-advenza-pcr'];
            }
        } elseif ($this->jsonFileName === 'xe-dap-truyenthong.json') {
            // Xe đạp truyền thống
            $categoryCode = 'sam-lop-xe-dap-lop-truyen-thong';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }

            // Fallback về parent
            if (isset($this->categories['sam-lop-xe-dap'])) {
                return $this->categories['sam-lop-xe-dap'];
            }
        } elseif ($this->jsonFileName === 'xe-dap-thethao.json') {
            // Xe đạp thể thao
            $categoryCode = 'sam-lop-xe-dap-lop-the-thao';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }

            // Fallback về parent
            if (isset($this->categories['sam-lop-xe-dap'])) {
                return $this->categories['sam-lop-xe-dap'];
            }
        } elseif ($this->jsonFileName === 'xe-dap-xam-xe-dap.json') {
            $categoryCode = 'sam-lop-xe-dap-sam-xe-dap';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
            if (isset($this->categories['sam-lop-xe-dap'])) {
                return $this->categories['sam-lop-xe-dap'];
            }
        } elseif ($this->jsonFileName === 'xe-chuyendung-cong-nghiep.json') {
            $categoryCode = 'sam-lop-chuyen-dung-lop-cong-nghiep';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
            // Fallback về parent
            if (isset($this->categories['sam-lop-chuyen-dung'])) {
                return $this->categories['sam-lop-chuyen-dung'];
            }
        } elseif ($this->jsonFileName === 'xe-chuyendung-nongnghiep.json') {
            // Xe chuyển dụng nông nghiệp
            $categoryCode = 'sam-lop-chuyen-dung-lop-nong-nghiep';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
            if (isset($this->categories['sam-lop-chuyen-dung'])) {
                return $this->categories['sam-lop-chuyen-dung'];
            }
        } elseif ($this->jsonFileName === 'xe_tai_yem_bias.json' || $this->jsonFileName === 'xe_tai_xam_bias.json' || $this->jsonFileName === 'xe_tai_yem_radial.json' || $this->jsonFileName === 'xe_tai_xam_radial.json') {
            $categoryCode = 'sam-lop-xe-tai-sam-yem-oto';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
        } elseif ($this->jsonFileName === 'xe_dien_xe_dap.json') {
            $categoryCode = 'sam-lop-xe-dien-lop-xe-dap-dien';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
        } elseif ($this->jsonFileName === 'xe_dien_xe_may.json') {
            $categoryCode = 'sam-lop-xe-dien-lop-xe-may-dien';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
        } elseif ($this->jsonFileName === 'xe_may_sam_xe_may.json') {
            $categoryCode = 'sam-lop-xe-may-sam-xe-may';
            if (isset($this->categories[$categoryCode])) {
                return $this->categories[$categoryCode];
            }
        }
        // ✅ Nếu không có danh mục nào, trả về null
        $this->command->warn("  ⚠ Không tìm thấy danh mục phù hợp cho sản phẩm này");
        return null;
    }

    /**
     * Tạo sản phẩm từ dữ liệu JSON
     */
    private function createProduct($rowData, $categoryId, $rowIndex)
    {
        if (!is_array($rowData)) {
            throw new \Exception("rowData không phải là array trong createProduct");
        }

        $code = $rowData['MÃ SP'] ?? $rowData['No'] ?? null;
        if ($code !== null && is_numeric($code)) {
            $code = (string)$code;
        }

        // ✅ Nếu MÃ SP đã tồn tại thì không tạo mới, chỉ trả về ID
        if (!empty($code)) {
            $existing = DB::table('products')->where('code', $code)->first();
            if ($existing) {
                $this->command->info("  ℹ️ MÃ SP {$code} đã tồn tại → chỉ gán thêm thuộc tính");
                return [
                    'id' => $existing->id,
                    'is_new' => false,
                ];
            }
        }


        $is_bestseller = false;

        $price = $this->parsePrice($rowData['Unit Price'] ?? $rowData['Giá'] ?? 0);
        $salePrice = null;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'code' => $code,
            'sku' => $code,
            'price' => $price,
            'sale_price' => $salePrice,
            'stock_quantity' => rand(10, 100),
            'min_stock_quantity' => 5,
            'is_active' => true,
            'is_featured' => true,
            'is_new' => true,
            'is_bestseller' => $is_bestseller,
            'view_count' => rand(0, 100),
            'sort_order' => rand(1, 1000),
            'image_urls' => $this->generateImages($rowData),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uniqueSku = $code;
        DB::table('products')->where('id', $productId)->update(['sku' => $uniqueSku]);

        $this->attachProductToCategories($productId, $categoryId);

        return [
            'id' => $productId,
            'is_new' => true,
        ];
    }
    private function attachCategoryChain(array &$categoriesToAttach, $childCategoryId, bool $primaryOnChild = true): bool
    {
        if (empty($childCategoryId)) {
            return false;
        }

        // Child
        $categoriesToAttach[] = [
            'id' => $childCategoryId,
            'is_primary' => $primaryOnChild,
        ];

        // Parents (parent, grandparent, ...)
        $currentId = $childCategoryId;
        while ($currentId) {
            $current = DB::table('product_categories')->where('id', $currentId)->first();
            if (!$current || empty($current->parent_id)) {
                break;
            }

            $parentId = $current->parent_id;
            $categoriesToAttach[] = [
                'id' => $parentId,
                'is_primary' => false,
            ];
            $currentId = $parentId;
        }

        return true;
    }
    private function attachProductToCategories($productId, $childCategoryId)
    {
        $categoriesToAttach = [];
        $isPrimarySet = false;

        if ($this->jsonFileName === 'xe_may_Tubeless.json') {
            // Parent (primary)
            if (isset($this->categories['sam-lop-xe-may'])) {
                $parentId = $this->categories['sam-lop-xe-may']->id;
                $categoriesToAttach[] = [
                    'id' => $parentId,
                    'is_primary' => true,
                ];
                $isPrimarySet = true;
            }

            // Child: Tubeless Euromina
            if (isset($this->categories['sam-lop-xe-may-lop-tubeless-euromina'])) {
                $childId = $this->categories['sam-lop-xe-may-lop-tubeless-euromina']->id;
                $categoriesToAttach[] = [
                    'id' => $childId,
                    'is_primary' => false,
                ];
            }

            // ✅ THÊM: danh mục cháu theo DÒNG LỐP (E‑SERIES, Advance, ...)
            if (!empty($childCategoryId)) {
                $categoriesToAttach[] = [
                    'id' => $childCategoryId,
                    'is_primary' => false,
                ];
            }
        } elseif ($this->jsonFileName === 'xetai_RADIAL.json') {
            if ($this->attachCategoryChain($categoriesToAttach, $childCategoryId, true)) {
                $isPrimarySet = true;
            }
        } else {
            if ($this->attachCategoryChain($categoriesToAttach, $childCategoryId, true)) {
                $isPrimarySet = true;
            }
        }

        // Nếu không có category nào được gán, dùng childCategoryId mặc định
        if (empty($categoriesToAttach)) {
            $categoriesToAttach[] = [
                'id' => $childCategoryId,
                'is_primary' => true,
            ];
        }

        // Insert vào bảng pivot
        $categoryNames = [];
        foreach ($categoriesToAttach as $categoryData) {
            $categoryId = $categoryData['id'];
            $isPrimary = $categoryData['is_primary'] ?? false;

            // Kiểm tra xem đã tồn tại chưa để tránh duplicate
            $exists = DB::table('product_product_category')
                ->where('product_id', $productId)
                ->where('product_category_id', $categoryId)
                ->exists();

            if (!$exists) {
                DB::table('product_product_category')->insert([
                    'product_id' => $productId,
                    'product_category_id' => $categoryId,
                    'is_primary' => $isPrimary,
                    'sort_order' => $isPrimary ? 0 : 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Lấy tên category để log
                $category = DB::table('product_categories')->where('id', $categoryId)->first();
                $categoryCode = $category ? $category->code : "ID:{$categoryId}";
                $categoryNames[] = $categoryCode . ($isPrimary ? ' (primary)' : '');
            }
        }

        if (!empty($categoryNames)) {
            $this->command->info("  → Gán vào " . count($categoryNames) . " danh mục: " . implode(', ', $categoryNames));
        }
    }
    /**
     * Tạo translations cho sản phẩm
     */
    private function createTranslations($productId, $rowData)
    {
        // ✅ Kiểm tra an toàn: $rowData phải là array
        if (!is_array($rowData)) {
            throw new \Exception("rowData không phải là array trong createTranslations");
        }

        $manufacturer = $rowData['Hãng xe'] ?? $rowData['Manufacturer'] ?? 'Unknown';
        $model = $rowData['Model'] ?? 'Unknown';
        $size = $rowData['Size'] ?? '';
        $productionType = $rowData['Chủng loại lốp'] ?? $rowData['Production Type'] ?? '';
        $characteristics = $rowData['Đặc điểm'] ?? '';
        $weight = $rowData['Tải trọng'] ?? $rowData['Weight'] ?? '';
        $speed = $rowData['Tốc độ'] ?? $rowData['Speed'] ?? '';
        $finger = (string)($rowData['Mã gai'] ?? $rowData['Finger'] ?? '');

        // Tạo tên sản phẩm theo format: lốp 265/65 R17 Coverer AC586 TL 112S H/T RBD (Advenza)
        if ($this->jsonFileName === 'advenza.json') {
            $name = $this->generateProductName($rowData);
        } else {
            $name = $rowData['Tên sản phẩm'] ?? $rowData['Product Name'] ?? '';
        }

        $sku = $rowData['MÃ SP'] ?? $rowData['No'] ?? $productId;
        if (is_numeric($sku)) {
            $sku = (string)$sku;
        }
        $baseSlug = \Illuminate\Support\Str::slug(trim($name) !== '' ? $name : 'san-pham') . '-' . strtolower($sku);
        $slugVi = $this->generateUniqueSlug($baseSlug . '-vi', 'product_translations');
        $slugEn = $this->generateUniqueSlug($baseSlug . '-en', 'product_translations');

        // Tạo mô tả
        $description = '';
        $shortDescription = $productionType . ($characteristics ? ' - ' . $characteristics : '');

        $features = '';
        $shortDescription = $productionType . ($characteristics ? ' - ' . $characteristics : '');


        // ✅ Tạo text_search cho Meilisearch (tổng hợp tất cả text searchable)
        $textSearchVi = $this->generateTextSearch($rowData, 'vi', $name, $description);
        $textSearchEn = $this->generateTextSearch($rowData, 'en', $name, $description);

        $descFeat = $this->buildDescriptionFeaturesByCategory($productId, $name);
        $description = $descFeat['description'];
        $features = $descFeat['features'];
        DB::table('product_translations')->insert([
            [
                'product_id' => $productId,
                'language' => 'vi',
                'name' => $name,
                'description' => $description,
                'features' => $features,
                'short_description' => $shortDescription,
                'meta_title' => $name . ' - Lốp xe ' . $manufacturer . ' chính hãng',
                'meta_description' => 'Lốp xe ' . $name . ' chất lượng cao với hiệu suất tuyệt vời',
                'slug' => $slugVi,
                'text_search' => $textSearchVi, // ✅ Text search cho VI
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => $productId,
                'language' => 'en',
                'name' => $name,
                'description' => $description,
                'features' => $features,
                'short_description' => $shortDescription,
                'meta_title' => $name . ' - ' . $manufacturer . ' Original Tire',
                'meta_description' => 'High quality ' . $name . ' tire with excellent performance',
                'slug' => $slugEn,


                'text_search' => $textSearchEn, // ✅ Text search cho EN
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Gán attributes cho sản phẩm
     */
    private function assignAttributes($productId, $rowData)
    {
        // ✅ Kiểm tra an toàn: $rowData phải là array
        if (!is_array($rowData)) {
            return; // Bỏ qua nếu không phải array
        }

        // Mapping các trường từ JSON mới sang attribute codes
        $attributeMapping = [
            'Mã gai' => 'finger',
            'Finger' => 'finger',
            'Hãng xe' => 'manufacturer',
            'Manufacturer' => 'manufacturer',
            'Model' => 'model',
            'Size' => 'size',
            'Chiều rộng' => 'wide',
            'Wide' => 'wide',
            'Tỉ lệ' => 'rate',
            'Rate' => 'rate',
            'Đường kính mâm' => 'diameter',
            'Diameter' => 'diameter',
            'Chủng loại lốp' => 'production_type',
            'Production Type' => 'production_type',
            'Tải trọng' => 'weight',
            'Weight' => 'weight',
            'Tốc độ' => 'speed_rating',
            'Speed' => 'speed_rating',
            'Bám đường' => 'road_grip',
            'Chịu nhiệt' => 'heat_resistance',
            'Heat' => 'heat_resistance',
            'Bảo hành' => 'warranty',
            'Warranty' => 'warranty',
            // Thêm các mapping còn thiếu cho file euromina và casumina
            'Số lớp bố' => 'ply_rating',
            'Loại lốp' => 'tire_type',
            'Chỉ số tải' => 'load_index_number',
            'Chỉ số tốc độ' => 'speed_index',
            'DÒNG LỐP' => 'tire_line',
            'Chiều sâu gai (mm)' => 'tread_depth',
            // ✅ Mapping mới cho xe đạp
            'Kiểu hoa' => 'tire_pattern',
            'ETRTO' => 'etrto_code',
            'Inches' => 'size_inches',
            'Đường kính ngoài' => 'outer_diameter',
            'Chiều rộng hông lốp' => 'sidewall_width',
            'Đường kính vành' => 'rim_diameter',
            'Chiều rộng vành' => 'rim_width',
            'Nội áp tiêu chuẩn (kPa)' => 'standard_pressure',
            'Nhóm Lốp Màu' => 'tire_color_group',
            'Chiều dài gập đôi (mm)' => 'folded_length',
            'Độ dày thân (mm)' => 'body_thickness',
            'Van' => 'valve',
            "Etrto" => 'etrto',


        ];

        foreach ($attributeMapping as $jsonField => $attributeCode) {
            // ✅ Kiểm tra key có tồn tại trong JSON không
            if (!isset($rowData[$jsonField])) {
                continue; // Bỏ qua nếu key không tồn tại
            }

            $value = $rowData[$jsonField];

            // ✅ Kiểm tra value có hợp lệ không
            if ($this->isValidAttributeValue($value)) {
                $this->assignAttributeValue($productId, $attributeCode, $value);
            }
        }

        // ✅ Xử lý đặc biệt cho "Năm sản xuất" - chỉ khi key tồn tại
        $productionYear = null;
        if (isset($rowData['Năm sản xuất'])) {
            $productionYear = $rowData['Năm sản xuất'];
        } elseif (isset($rowData['Production Year'])) {
            $productionYear = $rowData['Production Year'];
        }

        if ($productionYear !== null && $productionYear !== '') {
            $years = $this->parseProductionYear($productionYear);
            if (!empty($years)) {
                foreach ($years as $year) {
                    $this->assignAttributeValue($productId, 'production_year', $year);
                }
            }
        }

        // ✅ Gán cứng "Đặc điểm" theo danh mục
        $this->assignFixedProductFeaturesByCategory($productId);
    }
    /**
     * Kiểm tra giá trị attribute có hợp lệ không
     * Chỉ gán nếu value không null, không empty string, và không phải empty array
     * 
     * @param mixed $value
     * @return bool
     */
    private function isValidAttributeValue($value)
    {
        // Null → không hợp lệ
        if ($value === null) {
            return false;
        }

        // Empty string → không hợp lệ
        if ($value === '') {
            return false;
        }

        // Empty array → không hợp lệ
        if (is_array($value) && empty($value)) {
            return false;
        }

        // Số 0 là hợp lệ (có thể là giá trị thực)
        // Boolean false là hợp lệ (có thể là giá trị thực)

        return true;
    }
    /**
     * Gán giá trị attribute cho sản phẩm
     */
    /**
     * Gán giá trị attribute cho sản phẩm
     */
    private function assignAttributeValue($productId, $attributeCode, $value)
    {
        // ✅ Normalize value: convert về string và trim
        $normalizedValue = trim((string) $value);

        // Bỏ qua nếu value rỗng sau khi normalize
        if ($normalizedValue === '') {
            return;
        }

        // Lấy hoặc tạo attribute
        $attribute = DB::table('product_attributes')->where('code', $attributeCode)->first();

        if (!$attribute) {
            // Tạo attribute mới nếu chưa có
            $attributeId = DB::table('product_attributes')->insertGetId([
                'code' => $attributeCode,
                'type' => $this->getAttributeType($attributeCode),
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 1,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tạo translations cho attribute
            DB::table('product_attribute_translations')->insert([
                [
                    'attribute_id' => $attributeId,
                    'language' => 'vi',
                    'name' => $this->getAttributeName($attributeCode, 'vi'),
                    'description' => 'Thuộc tính ' . $attributeCode,
                ],
                [
                    'attribute_id' => $attributeId,
                    'language' => 'en',
                    'name' => $this->getAttributeName($attributeCode, 'en'),
                    'description' => $attributeCode . ' attribute',
                ],
            ]);

            $attribute = (object) ['id' => $attributeId];
        }

        // ✅ Xác định vehicle_type dựa vào jsonFileName VÀ attributeCode
        $vehicleType = $this->getVehicleTypeFromFileName($attributeCode);

        // ✅ Tìm hoặc tạo attribute value (dùng normalized value)
        $attributeValue = DB::table('product_attribute_values')
            ->where('attribute_id', $attribute->id)
            ->where('value', $normalizedValue)
            ->first();

        if (!$attributeValue) {
            $attributeValueId = DB::table('product_attribute_values')->insertGetId([
                'attribute_id' => $attribute->id,
                'value' => $normalizedValue,
                'vehicle_type' => $vehicleType, // ✅ Thêm vehicle_type
                'color_code' => null,
                'image_url' => null,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tạo translations cho attribute value
            DB::table('product_attribute_value_translations')->insert([
                [
                    'attribute_value_id' => $attributeValueId,
                    'language' => 'vi',
                    'value' => $normalizedValue,
                ],
                [
                    'attribute_value_id' => $attributeValueId,
                    'language' => 'en',
                    'value' => $normalizedValue,
                ],
            ]);
        } else {
            $attributeValueId = $attributeValue->id;

            // ✅ Cập nhật vehicle_type nếu chưa có hoặc = NULL
            if (empty($attributeValue->vehicle_type)) {
                DB::table('product_attribute_values')
                    ->where('id', $attributeValueId)
                    ->update(['vehicle_type' => $vehicleType]);
            }
        }

        // Gán attribute value cho sản phẩm (kiểm tra trùng lặp)
        $existing = DB::table('product_attribute_product')
            ->where('product_id', $productId)
            ->where('attribute_value_id', $attributeValueId)
            ->first();

        if (!$existing) {
            $showDetail = 'N'; // Mặc định
            if ($this->jsonFileName === 'xe_may_Tubeless.json') {
                if ($attributeCode === 'wide' || $attributeCode === 'rate' || $attributeCode === 'diameter' || $attributeCode === 'ply_rating' || $attributeCode === 'tire_type' || $attributeCode === 'load_index_number' || $attributeCode === 'speed_index') {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'advenza.json') {
                if ($attributeCode === 'wide' || $attributeCode === 'rate' || $attributeCode === 'diameter' || $attributeCode === 'speed_rating' || $attributeCode === 'weight' || $attributeCode === 'road_grip' || $attributeCode === 'heat_resistance' || $attributeCode === 'warranty') {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xetai_RADIAL.json' || $this->jsonFileName === 'xe_tai_BIAS_nang.json' || $this->jsonFileName === 'xe_tai_BIAS_nhe.json' || $this->jsonFileName === 'xe_tai_NHE_PCR.json') {
                if ($attributeCode === 'wide' || $attributeCode === 'rate' || $attributeCode === 'diameter' || $attributeCode === 'ply_rating' || $attributeCode === 'tire_type' || $attributeCode === 'weight' || $attributeCode === 'speed_rating' ||  $attributeCode === 'tread_depth') {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xe-dap-truyenthong.json' || $this->jsonFileName === 'xe-dap-thethao.json') {
                // ✅ Xe đạp show các thông số kỹ thuật
                if (in_array($attributeCode, [
                    'tire_pattern',
                    'size_inches',
                    'outer_diameter',
                    'sidewall_width',
                    'rim_diameter',
                    'rim_width',
                    'standard_pressure'
                ])) {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xe-chuyendung-cong-nghiep.json' || $this->jsonFileName === 'xe-chuyendung-nongnghiep.json') {
                // ✅ Xe đạp show các thông số kỹ thuật
                if (in_array($attributeCode, [
                    'wide',
                    'rate',
                    'diameter',
                    'ply_rating',
                    'tire_type',
                    'warranty'
                ])) {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xe_may_sam_xe_may.json') {
                if (
                    $attributeCode === 'wide'  || $attributeCode === 'folded_length' || $attributeCode === 'body_thickness' || $attributeCode === 'valve'
                ) {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xe_tai_xam_bias.json' || $this->jsonFileName === 'xe_tai_xam_radial.json') {
                if (
                    $attributeCode === 'wide' || $attributeCode === 'folded_length' ||  $attributeCode === 'body_thickness' || $attributeCode === 'valve'
                ) {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xe_tai_yem_bias.json' || $this->jsonFileName === 'xe_tai_yem_radial.json') {
                if (
                    $attributeCode === 'body_thickness'
                ) {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xe_dien_xe_dap.json') {
                if (
                    $attributeCode === 'tire_type'  || $attributeCode === 'warranty'
                ) {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xe_dien_xe_may.json') {
                if (
                    $attributeCode === 'wide' || $attributeCode === 'rate' || $attributeCode === 'diameter' || $attributeCode === 'ply_rating' || $attributeCode === 'tire_type' || $attributeCode === 'load_index_number' || $attributeCode === 'speed_index' || $attributeCode === 'warranty'
                ) {
                    $showDetail = 'Y';
                }
            } elseif ($this->jsonFileName === 'xe-dap-xam-xe-dap.json') {
                if (
                    $attributeCode === 'etrto' || $attributeCode === 'wide' || $attributeCode === 'folded_length' || $attributeCode === 'body_thickness' || $attributeCode === 'valve'
                ) {
                    $showDetail = 'Y';
                }
            }

            DB::table('product_attribute_product')->insert([
                'product_id' => $productId,
                'attribute_value_id' => $attributeValueId,
                'show_detail' => $showDetail,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Lấy vehicle_type dựa vào tên file JSON và loại thuộc tính
     * CHỈ phân biệt cho model của xe máy và ô tô
     * 
     * @param string $attributeCode
     * @return string|null
     */
    private function getVehicleTypeFromFileName($attributeCode)
    {
        // ✅ CHỈ phân biệt vehicle_type cho thuộc tính 'model'
        if ($attributeCode === 'model') {
            if ($this->jsonFileName === 'xe_may_Tubeless.json') {
                return 'xe-may';
            } elseif ($this->jsonFileName === 'advenza.json') {
                return 'oto';
            }
        }

        // ✅ Tất cả các trường hợp khác (xe tải, manufacturer, year, v.v.) → 'all'
        return 'all';
    }
    /**
     * Parse năm sản xuất từ string
     * Hỗ trợ các format:
     * - "2004-2017" → [2004, 2005, ..., 2017]
     * - "2008" → [2008]
     * - 2018 → [2018]
     * 
     * @param string|int $yearValue
     * @return array
     */
    private function parseProductionYear($yearValue)
    {
        // Nếu là số, trả về array với 1 phần tử
        if (is_numeric($yearValue)) {
            return [(string)$yearValue];
        }

        $yearValue = trim((string)$yearValue);

        // Nếu rỗng, trả về array rỗng
        if (empty($yearValue)) {
            return [];
        }

        // Kiểm tra format range: "2004-2017"
        if (preg_match('/^(\d{4})\s*-\s*(\d{4})$/', $yearValue, $matches)) {
            $startYear = (int)$matches[1];
            $endYear = (int)$matches[2];

            // Đảm bảo startYear <= endYear
            if ($startYear > $endYear) {
                [$startYear, $endYear] = [$endYear, $startYear];
            }

            // Tạo array các năm từ startYear đến endYear
            $years = [];
            for ($year = $startYear; $year <= $endYear; $year++) {
                $years[] = (string)$year;
            }

            return $years;
        }

        // Nếu là năm đơn lẻ, trả về array với 1 phần tử
        if (preg_match('/^\d{4}$/', $yearValue)) {
            return [$yearValue];
        }

        // Nếu không parse được, trả về array rỗng
        return [];
    }

    /**
     * Gán các đặc điểm sản phẩm (multiselect)
     * Xử lý trường "Đặc điểm" với format "01,02,03,04"
     */
    private function
    assignProductFeatures($productId, $featuresString)
    {
        // Định nghĩa mapping các mã đặc điểm
        $featureMapping = $this->listFeatureMapping();

        // Lấy hoặc tạo attribute "product_features"
        $attributeCode = 'product_features';
        $attribute = DB::table('product_attributes')->where('code', $attributeCode)->first();

        if (!$attribute) {
            // Tạo attribute mới với type multiselect
            $attributeId = DB::table('product_attributes')->insertGetId([
                'code' => $attributeCode,
                'type' => 'multiselect',
                'is_required' => false,
                'is_filterable' => true,
                'is_comparable' => true,
                'is_active' => true,
                'sort_order' => 100,
                'options' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tạo translations cho attribute
            DB::table('product_attribute_translations')->insert([
                [
                    'attribute_id' => $attributeId,
                    'language' => 'vi',
                    'name' => 'Đặc điểm sản phẩm',
                    'description' => 'Các đặc điểm nổi bật của sản phẩm',
                ],
                [
                    'attribute_id' => $attributeId,
                    'language' => 'en',
                    'name' => 'Product Features',
                    'description' => 'Outstanding features of the product',
                ],
            ]);

            $attribute = (object) ['id' => $attributeId];
        }

        // ✅ Đảm bảo $featuresString là string
        $featuresString = (string) $featuresString;

        // ✅ Kiểm tra rỗng
        if (empty(trim($featuresString))) {
            return;
        }

        // Parse chuỗi đặc điểm (VD: "01,02,03,04")
        $featureCodes = array_map('trim', explode(',', $featuresString));
        $featureCodes = array_filter($featureCodes); // Loại bỏ giá trị rỗng

        // ✅ Kiểm tra sau khi filter
        if (empty($featureCodes)) {
            return;
        }

        // Tạo hoặc lấy attribute values cho từng đặc điểm
        foreach ($featureCodes as $featureCode) {
            if (!isset($featureMapping[$featureCode])) {
                continue; // Bỏ qua mã không hợp lệ
            }

            $featureInfo = $featureMapping[$featureCode];
            $value = $featureCode; // Lưu mã làm value

            // Tìm hoặc tạo attribute value
            $attributeValue = DB::table('product_attribute_values')
                ->where('attribute_id', $attribute->id)
                ->where('value', $value)
                ->first();

            if (!$attributeValue) {
                $attributeValueId = DB::table('product_attribute_values')->insertGetId([
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                    'color_code' => null,
                    'image_url' => null,
                    'is_active' => true,
                    'sort_order' => (int) $featureCode, // Sắp xếp theo mã
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Tạo translations cho attribute value
                DB::table('product_attribute_value_translations')->insert([
                    [
                        'attribute_value_id' => $attributeValueId,
                        'language' => 'vi',
                        'value' => $featureInfo['vi'],
                    ],
                    [
                        'attribute_value_id' => $attributeValueId,
                        'language' => 'en',
                        'value' => $featureInfo['en'],
                    ],
                ]);
            } else {
                $attributeValueId = $attributeValue->id;
            }

            // Gán attribute value cho sản phẩm (kiểm tra trùng lặp)
            $existing = DB::table('product_attribute_product')
                ->where('product_id', $productId)
                ->where('attribute_value_id', $attributeValueId)
                ->first();

            if (!$existing) {
                DB::table('product_attribute_product')->insert([
                    'product_id' => $productId,
                    'attribute_value_id' => $attributeValueId,
                ]);
            }
        }
    }

    /**
     * Parse giá từ string
     */
    private function parsePrice($price)
    {
        // ✅ Kiểm tra null hoặc empty
        if ($price === null || $price === '') {
            return 0.0;
        }

        // ✅ Nếu là số, trả về trực tiếp
        if (is_numeric($price)) {
            return (float) $price;
        }

        // ✅ Đảm bảo là string trước khi xử lý
        $price = (string) $price;

        // Loại bỏ ký tự không phải số
        $price = preg_replace('/[^0-9.,]/', '', $price);

        // ✅ Kiểm tra sau khi loại bỏ ký tự
        if (empty($price)) {
            return 0.0;
        }

        $price = str_replace(',', '.', $price);

        $result = (float) $price;

        // ✅ Trả về 0 nếu kết quả không hợp lệ
        return $result > 0 ? $result : 0.0;
    }



    /**
     * Tạo hình ảnh cho sản phẩm
     */
    private function generateImages($rowData)
    {
        if (!is_array($rowData)) {
            return; // Bỏ qua nếu không phải array
        }
        $imageUrls = [];
        $nameCategoryImage = $rowData['Chủng loại lốp'] ?? $rowData['Images'] ?? [];
        if (!empty($nameCategoryImage) and $nameCategoryImage === 'ADVENZA VENTURER AV568') {
            $imageUrls[] = '/storage/images/Property 1=AV568 right.png';
            $imageUrls[] = '/storage/images/Property 1=AV568 front.png';

            $imageUrls[] = '/storage/images/Property 1=AV568 side.png';
            $imageUrls[] = '/storage/images/Property 1=AV568.png';
        } elseif (!empty($nameCategoryImage) and $nameCategoryImage === 'ADVENZA VENTURER AV579') {
            $imageUrls[] = '/storage/images/Property 1=AV579 right.png';
            $imageUrls[] = '/storage/images/Property 1=AV579 front.png';

            $imageUrls[] = '/storage/images/Property 1=AV579 side.png';
            $imageUrls[] = '/storage/images/Property 1=AV579.png';
        } elseif (!empty($nameCategoryImage) and $nameCategoryImage === 'ADVENZA VENTURER AV789') {
            $imageUrls[] = '/storage/images/Property 1=AV789 right.png';
            $imageUrls[] = '/storage/images/Property 1=AV789 front.png';

            $imageUrls[] = '/storage/images/Property 1=AV789 side.png';
            $imageUrls[] = '/storage/images/Property 1=AV789.png';
        } elseif (!empty($nameCategoryImage) and $nameCategoryImage === 'ADVENZA VENTURER AV568') {
            $imageUrls[] = '/storage/images/Property 1=AV568 right.png';
            $imageUrls[] = '/storage/images/Property 1=AV568 front.png';

            $imageUrls[] = '/storage/images/Property 1=AV568 side.png';
            $imageUrls[] = '/storage/images/Property 1=AV568.png';
        } elseif (!empty($nameCategoryImage) and $nameCategoryImage === 'ADVENZA TRAVELLER AT666') {
            $imageUrls[] = '/storage/images/Property 1=AT666 front.png';
            $imageUrls[] = '/storage/images/Property 1=AT666 right.png';
            $imageUrls[] = '/storage/images/Property 1=AT666 side.png';
            $imageUrls[] = '/storage/images/Property 1=AT666.png';
        } elseif (!empty($nameCategoryImage) and $nameCategoryImage === 'ADVENZA DISCOVERER AT352') {
            $imageUrls[] = '/storage/images/Property 1=AT352 right.png';
            $imageUrls[] = '/storage/images/Property 1=AT352 front.png';

            $imageUrls[] = '/storage/images/Property 1=AT352 side.png';
            $imageUrls[] = '/storage/images/Property 1=AT352.png';
        } elseif (!empty($nameCategoryImage) and $nameCategoryImage === 'ADVENZA COVERER HT AC586') {
            $imageUrls[] = '/storage/images/Property 1=AC586 right.png';
            $imageUrls[] = '/storage/images/Property 1=AC586 front.png';
            $imageUrls[] = '/storage/images/Property 1=AC586 side.png';
            $imageUrls[] = '/storage/images/Property 1=AC586.png';
        } elseif (!empty($nameCategoryImage) and $nameCategoryImage === 'ADVENZA COVERER HL AC686') {
            $imageUrls[] = '/storage/images/Property 1=AC686 right.png';
            $imageUrls[] = '/storage/images/Property 1=AC686 front.png';

            $imageUrls[] = '/storage/images/Property 1=AC686 side.png';
            $imageUrls[] = '/storage/images/Property 1=AC686.png';
        } elseif ($this->jsonFileName === 'xe-dap-truyenthong.json' || $this->jsonFileName === 'xe-dap-thethao.json') {

            $magGai = $rowData['Kiểu hoa'] ?? [];
            if (!empty($magGai)) {
                $imageUrls[] = '/storage/images/' . $magGai . '.png';
            }
        } elseif ($this->jsonFileName === 'xe-chuyendung-cong-nghiep.json' || $this->jsonFileName === 'xe-chuyendung-nongnghiep.json' || $this->jsonFileName === 'xe_dien_xe_dap.json' || $this->jsonFileName === 'xe_dien_xe_may.json') {
            $magGai = $rowData['Mã gai'] ?? [];
            if (!empty($magGai)) {
                $imageUrls[] = '/storage/images/' . $magGai . '.png';
            }
        } elseif ($this->jsonFileName === 'xe_tai_yem_bias.json') {
            $imageUrls[] = '/storage/images/xe_tai_yem_bias.png';
        } elseif ($this->jsonFileName === 'xe_tai_xam_bias.json') {
            $imageUrls[] = '/storage/images/xe_tai_xam_bias.png';
        } elseif ($this->jsonFileName === 'xe_tai_yem_radial.json') {
            $imageUrls[] = '/storage/images/xe_tai_yem_radial.png';
        } elseif ($this->jsonFileName === 'xe_tai_xam_radial.json') {
            $imageUrls[] = '/storage/images/xe_tai_xam_radial.png';
        } elseif ($this->jsonFileName === 'xe_may_sam_xe_may.json') {
            $imageUrls[] = '/storage/images/xe_may_sam_xe_may_Butyl.png';
        } else {
            $magGai = $rowData['Mã gai'] ?? [];
            if (!empty($magGai)) {
                $imageUrls[] = '/storage/images/' . $magGai . '.png.png';
            }
        }


        //AV789 bestseller =/storage/images/1767578884_0_Tyg6CLiWqD.png
        // xe may CA134P /storage/images/1767577950_0_FkE0mF5rmm.png'
        //discover AT352 https://casumina.org/storage/images/1767578536_0_smlvlsmvUd.png

        return json_encode($imageUrls);
    }


    /**
     * Lấy loại attribute
     */
    private function getAttributeType($attributeCode)
    {
        $types = [
            'finger' => 'text',
            'manufacturer' => 'select',
            'model' => 'text',
            'production_year' => 'select',
            'size' => 'text',
            'wide' => 'number',
            'rate' => 'number',
            'diameter' => 'number',
            'production_type' => 'select',
            'weight' => 'number',
            'speed_rating' => 'select',
            'path_type' => 'select',
            'heat_resistance' => 'select',
            'warranty' => 'text',
            'characteristic' => 'textarea',
            'ply_rating' => 'number',
            'tire_type' => 'select',
            'load_index_number' => 'number',
            'speed_index' => 'text',
            'tire_line' => 'text',
            // ✅ Types mới cho xe đạp
            'tire_pattern' => 'text',
            'etrto_code' => 'text',
            'size_inches' => 'text',
            'outer_diameter' => 'text',
            'sidewall_width' => 'text',
            'rim_diameter' => 'text',
            'rim_width' => 'text',
            'standard_pressure' => 'text',
            'tire_color_group' => 'text',
            'folded_length' => 'number',
            'body_thickness' => 'number',
            'valve' => 'text',
            'etrto' => 'text',
        ];

        return $types[$attributeCode] ?? 'text';
    }

    /**
     * Lấy tên attribute theo ngôn ngữ
     */
    private function getAttributeName($attributeCode, $language)
    {
        $names = [
            'finger' => ['vi' => 'Mã Gai', 'en' => 'Finger'],
            'manufacturer' => ['vi' => 'Nhà sản xuất', 'en' => 'Manufacturer'],
            'model' => ['vi' => 'Mẫu', 'en' => 'Model'],
            'production_year' => ['vi' => 'Năm sản xuất', 'en' => 'Production Year'],

            'wide' => ['vi' => 'Chiều rộng', 'en' => 'Width'],
            'rate' => ['vi' => 'Tỷ lệ', 'en' => 'Rate'],
            'diameter' => ['vi' => 'Đường kính', 'en' => 'Diameter'],
            'size' => ['vi' => 'Kích thước', 'en' => 'Size'],

            'production_type' => ['vi' => 'Loại lốp', 'en' => 'Production Type'],
            'weight' => ['vi' => 'Tải trọng', 'en' => 'Weight'],
            'speed_rating' => ['vi' => 'Tốc độ', 'en' => 'Speed Rating'],
            'path_type' => ['vi' => 'kết cấu của lốp', 'en' => 'Path Type'],
            'heat_resistance' => ['vi' => 'Chịu nhiệt', 'en' => 'Heat Resistance'],
            'road_grip' => ['vi' => 'Bám đường', 'en' => 'Road Grip'],
            'warranty' => ['vi' => 'Bảo hành', 'en' => 'Warranty'],
            'characteristic' => ['vi' => 'Đặc điểm', 'en' => 'Characteristic'],
            'ply_rating' => ['vi' => 'Số lớp bố', 'en' => 'Ply Rating'],
            'tire_type' => ['vi' => 'Loại lốp', 'en' => 'Tire Type'],
            'load_index_number' => ['vi' => 'Chỉ số tải', 'en' => 'Load Index Number'],
            'speed_index' => ['vi' => 'Chỉ số tốc độ', 'en' => 'Speed Index'],
            'tire_line' => ['vi' => 'Dòng lốp', 'en' => 'Tire Line'],
            // ✅ Names mới cho xe đạp
            'tire_pattern' => ['vi' => 'Kiểu hoa', 'en' => 'Tire Pattern'],
            'etrto_code' => ['vi' => 'Mã ETRTO', 'en' => 'ETRTO Code'],
            'size_inches' => ['vi' => 'Kích thước (Inches)', 'en' => 'Size (Inches)'],
            'outer_diameter' => ['vi' => 'Đường kính ngoài', 'en' => 'Outer Diameter'],
            'sidewall_width' => ['vi' => 'Chiều rộng hông lốp', 'en' => 'Sidewall Width'],
            'rim_diameter' => ['vi' => 'Đường kính vành', 'en' => 'Rim Diameter'],
            'rim_width' => ['vi' => 'Chiều rộng vành', 'en' => 'Rim Width'],
            'standard_pressure' => ['vi' => 'Nội áp tiêu chuẩn (kPa)', 'en' => 'Standard Pressure (kPa)'],
            'tire_color_group' => ['vi' => 'Nhóm lốp màu', 'en' => 'Tire Color Group'],
            // ✅ Names mới sam yem xe may
            'folded_length' => ['vi' => 'Chiều dài gập đôi (mm)', 'en' => 'Folded Length (mm)'],
            'body_thickness' => ['vi' => 'Độ dày thân (mm)', 'en' => 'Body Thickness (mm)'],
            'valve' => ['vi' => 'Van', 'en' => 'Valve'],
            'etrto' => ['vi' => 'Mã ETRTO', 'en' => 'ETRTO Code'],
        ];

        return $names[$attributeCode][$language] ?? $attributeCode;
    }

    /**
     * Tạo tên sản phẩm theo format: lốp 265/65 R17 Coverer AC586 TL 112S H/T RBD (Advenza)
     */
    private function generateProductName($rowData)
    {
        $size = $rowData['Size'] ?? '';
        $productionType = $rowData['Chủng loại lốp'] ?? $rowData['Production Type'] ?? '';
        $finger = (string)($rowData['Mã gai'] ?? $rowData['Finger'] ?? '');
        $manufacturer = $rowData['Hãng xe'] ?? $rowData['Manufacturer'] ?? '';
        $model = $rowData['Model'] ?? '';
        $weight = $rowData['Tải trọng'] ?? $rowData['Weight'] ?? '';
        $speed = $rowData['Tốc độ'] ?? $rowData['Speed'] ?? '';

        // ✅ Đảm bảo $productionType là string
        $productionType = (string) $productionType;

        // Tách thông tin từ Chủng loại lốp (VD: "ADVENZA VENTURER AV568")
        $productionParts = explode(' ', trim($productionType));
        if (!is_array($productionParts) || empty($productionParts)) {
            $productionParts = [];
        }

        $weightParts = explode(' ', trim($weight));
        if (!is_array($weightParts) || empty($weightParts)) {
            $weightParts = [];
        }

        $speedParts = explode(' ', trim($speed));
        if (!is_array($speedParts) || empty($speedParts)) {
            $speedParts = [];
        }
        $weightBrand = $weightParts[0] ?? '';
        $speedBrand = $speedParts[0] ?? '';

        $brand = $productionParts[0] ?? '';
        $tireModel = $productionParts[1] ?? '';
        $code = $productionParts[2] ?? '';
        if (isset($productionParts[3]) && !empty($productionParts[3])) {
            $code2 = $productionParts[3];
        } else {
            $code2 = '';
        }
        $parts = [];

        if (!empty($size)) {
            $parts[] = "Lốp {$size}";
        } else {
            $parts[] = 'Lốp xe';
        }

        if (!empty($tireModel)) {
            $parts[] = $tireModel;
        } elseif (!empty($model)) {
            $parts[] = $model;
        }

        if (!empty($code)) {
            $parts[] = $code;
        } elseif (!empty($finger)) {
            $parts[] = '';
        }
        if (!empty($code2)) {
            $parts[] = $code2;
        } elseif (!empty($finger)) {
            $parts[] = '';
        }

        if (!empty($weightBrand)) {
            $parts[] = " TL {$weightBrand}";
        }
        if (!empty($speedBrand)) {
            $parts[] = " {$speedBrand} ";
        }

        if (!empty($brand)) {
            $parts[] = "({$brand})";
        } elseif (!empty($manufacturer)) {
            $parts[] = "(Error manufacturer)";
        }

        $name = trim(implode(' ', array_filter($parts)));

        $sku = $rowData['MÃ SP'] ?? $rowData['No'] ?? '';
        return $name !== '' ? $name : ($sku ? 'Sản phẩm ' . $sku : 'Sản phẩm không tên');
    }

    /**
     * Tạo slug unique
     */
    private function generateUniqueSlug($slug, $table)
    {
        $originalSlug = $slug;
        $counter = 1;

        while (DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Xóa dữ liệu sản phẩm cũ trước khi import
     */
    private function cleanupOldData()
    {
        $this->command->info('🧹 Đang xóa dữ liệu sản phẩm cũ...');

        // Tắt foreign key checks để tránh lỗi constraint
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            // Pivot tables first
            'product_product_category',
            'product_attribute_product',
            'product_vehicle_fitments', // ✅ Vehicle fitments

            // Translation tables (không xóa product_category_translations vì danh mục đã có sẵn)
            'product_translations',
            'product_attribute_translations',
            'product_attribute_value_translations',

            // Attribute tables
            'product_attribute_values',
            'product_attributes',

            // Main tables (không xóa product_categories vì danh mục đã có sẵn)
            'products',
            // ❌ REMOVED: 'product_images' - Table không tồn tại
        ];

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($tables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $count = DB::table($table)->count();
                    DB::table($table)->truncate();
                    $this->command->info("✅ Đã xóa {$table} ({$count} records)");
                    $deletedCount++;
                } else {
                    $this->command->warn("⚠️  Bảng {$table} không tồn tại, bỏ qua...");
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $this->command->error("❌ Lỗi xóa {$table}: " . $e->getMessage());
            }
        }

        // Bật lại foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Reset auto increment
        $this->command->info('🔄 Đang reset auto increment...');
        $autoIncrementTables = [
            'products',
            'product_categories',
            'product_attributes',
            'product_attribute_values',
        ];

        foreach ($autoIncrementTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                try {
                    DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                    $this->command->info("✅ Reset auto increment cho {$table}");
                } catch (\Exception $e) {
                    $this->command->warn("⚠️  Không thể reset auto increment cho {$table}: " . $e->getMessage());
                }
            }
        }

        $this->command->info("🎉 Hoàn thành cleanup!");
        $this->command->info("   - Đã xóa: {$deletedCount} bảng");
        $this->command->info("   - Bỏ qua: {$skippedCount} bảng");
        $this->command->info("");
    }

    /**
     * Xóa dữ liệu thừa sau khi import
     */
    private function cleanupAfterImport()
    {
        $this->command->info('🧹 Kiểm tra và xóa dữ liệu thừa sau import...');

        // Tắt foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Chỉ xóa các attribute values và translations không được sử dụng
        $cleanedCount = 0;

        // Xóa attribute values không được sử dụng
        $unusedAttributeValues = DB::table('product_attribute_values')
            ->leftJoin('product_attribute_product', 'product_attribute_values.id', '=', 'product_attribute_product.attribute_value_id')
            ->whereNull('product_attribute_product.attribute_value_id')
            ->pluck('product_attribute_values.id');

        if ($unusedAttributeValues->count() > 0) {
            DB::table('product_attribute_value_translations')
                ->whereIn('attribute_value_id', $unusedAttributeValues)
                ->delete();

            DB::table('product_attribute_values')
                ->whereIn('id', $unusedAttributeValues)
                ->delete();

            $this->command->info("✅ Đã xóa {$unusedAttributeValues->count()} attribute values không sử dụng");
            $cleanedCount++;
        }

        // Xóa attributes không có values
        $unusedAttributes = DB::table('product_attributes')
            ->leftJoin('product_attribute_values', 'product_attributes.id', '=', 'product_attribute_values.attribute_id')
            ->whereNull('product_attribute_values.attribute_id')
            ->pluck('product_attributes.id');

        if ($unusedAttributes->count() > 0) {
            DB::table('product_attribute_translations')
                ->whereIn('attribute_id', $unusedAttributes)
                ->delete();

            DB::table('product_attributes')
                ->whereIn('id', $unusedAttributes)
                ->delete();

            $this->command->info("✅ Đã xóa {$unusedAttributes->count()} attributes không có values");
            $cleanedCount++;
        }

        // Bật lại foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        if ($cleanedCount > 0) {
            $this->command->info("🎉 Đã xóa {$cleanedCount} loại dữ liệu thừa!");
        } else {
            $this->command->info("✅ Không có dữ liệu thừa cần xóa.");
        }
        $this->command->info("");
    }

    /**
     * Generate text_search column cho Meilisearch
     * Tổng hợp tất cả text có thể search được
     * 
     * @param array $rowData
     * @param string $language
     * @param string $name
     * @param string $description
     * @return string
     */
    private function    generateTextSearch($rowData, $language, $name, $description)
    {
        $searchTerms = [];

        // 1. Tên sản phẩm
        $searchTerms[] = $name;

        // 2. SKU
        $sku = $rowData['MÃ SP'] ?? $rowData['No'] ?? '';
        if (!empty($sku)) {
            $searchTerms[] = (string)$sku;
        }

        // 3. Manufacturer (Hãng xe)
        $manufacturer = $rowData['Hãng xe'] ?? $rowData['Manufacturer'] ?? '';
        if (!empty($manufacturer)) {
            $searchTerms[] = $manufacturer;
        }

        // 4. Model
        if (!empty($rowData['Model'])) {
            $searchTerms[] = $rowData['Model'];
        }

        // 5. Size
        if (!empty($rowData['Size'])) {
            $searchTerms[] = $rowData['Size'];
        }

        // 6. Finger (Mã gai)
        $finger = (string)($rowData['Mã gai'] ?? $rowData['Finger'] ?? '');
        if (!empty($finger)) {
            $searchTerms[] = $finger;
        }

        // 7. Production Type (Chủng loại lốp)
        $productionType = $rowData['Chủng loại lốp'] ?? $rowData['Production Type'] ?? '';
        if (!empty($productionType)) {
            $searchTerms[] = $productionType;
        }

        // 8. Production Year - parse và thêm tất cả các năm
        $productionYear = $rowData['Năm sản xuất'] ?? $rowData['Production Year'] ?? '';
        if (!empty($productionYear)) {
            $years = $this->parseProductionYear($productionYear);
            foreach ($years as $year) {
                $searchTerms[] = $year;
            }
            // Thêm cả chuỗi gốc
            $searchTerms[] = $productionYear;
        }

        // 9. Đặc điểm - thêm cả mã và tên đặc điểm
        if (!empty($rowData['Đặc điểm'])) {
            $searchTerms[] = $rowData['Đặc điểm'];

            // Mapping các mã đặc điểm sang tên
            $featureMapping = $this->listFeatureMapping();;


            // Parse và thêm tên đặc điểm
            $featureCodes = array_map('trim', explode(',', $rowData['Đặc điểm']));
            foreach ($featureCodes as $code) {
                if (isset($featureMapping[$code])) {
                    $searchTerms[] = $featureMapping[$code][$language] ?? $featureMapping[$code]['vi'];
                }
            }
        }

        // 10. Description (strip HTML tags)
        if (!empty($description)) {
            $searchTerms[] = strip_tags($description);
        }

        // 11. Weight (Tải trọng)
        $weight = $rowData['Tải trọng'] ?? $rowData['Weight'] ?? '';
        if (!empty($weight)) {
            $searchTerms[] = 'tải trọng ' . $weight;
        }

        // 12. Speed (Tốc độ)
        $speed = $rowData['Tốc độ'] ?? $rowData['Speed'] ?? '';
        if (!empty($speed)) {
            $searchTerms[] = $speed;
        }

        // 13. Wide (Chiều rộng), Rate (Tỉ lệ), Diameter (Đường kính mâm)
        $wide = $rowData['Chiều rộng'] ?? $rowData['Wide'] ?? '';
        if (!empty($wide)) {
            $searchTerms[] = is_numeric($wide) ? (string)$wide : $wide;
        }

        $rate = $rowData['Tỉ lệ'] ?? $rowData['Rate'] ?? '';
        if (!empty($rate)) {
            $searchTerms[] = is_numeric($rate) ? (string)$rate : $rate;
        }

        $diameter = $rowData['Đường kính mâm'] ?? $rowData['Diameter'] ?? '';
        if (!empty($diameter)) {
            $searchTerms[] = is_numeric($diameter) ? (string)$diameter : $diameter;
        }

        // 14. Bám đường, Chịu nhiệt
        $roadGrip = $rowData['Bám đường'] ?? '';
        if (!empty($roadGrip)) {
            $searchTerms[] = $roadGrip;
        }

        $heatResistance = $rowData['Chịu nhiệt'] ?? '';
        if (!empty($heatResistance)) {
            $searchTerms[] = $heatResistance;
        }
        $treadDepth = $rowData['Chiều sâu gai (mm)'] ?? $rowData['Tread Depth'] ?? '';
        if (!empty($treadDepth)) {
            $searchTerms[] = 'chiều sâu gai ' . $treadDepth . ' mm';
        }


        $combinedTerms = [];

        // Kết hợp: "kia morning"
        if (!empty($manufacturer) && !empty($rowData['Model'])) {
            $combinedTerms[] = $manufacturer . ' ' . $rowData['Model'];
        }

        // Kết hợp: "advenza venturer" (từ Chủng loại lốp)
        if (!empty($productionType)) {
            $productionParts = explode(' ', $productionType);
            if (count($productionParts) >= 2) {
                // Lấy 2 từ đầu: "ADVENZA VENTURER"
                $combinedTerms[] = $productionParts[0] . ' ' . $productionParts[1];
            }
        }

        // Kết hợp đầy đủ: "kia morning advenza venturer"
        if (
            !empty($manufacturer) &&
            !empty($rowData['Model']) &&
            !empty($productionType)
        ) {
            $productionParts = explode(' ', $productionType);
            if (count($productionParts) >= 2) {
                $combinedTerms[] = $manufacturer . ' ' .
                    $rowData['Model'] . ' ' .
                    $productionParts[0] . ' ' .
                    $productionParts[1];
            }
        }

        // Kết hợp với size: "kia morning 165/65r13"
        if (
            !empty($manufacturer) &&
            !empty($rowData['Model']) &&
            !empty($rowData['Size'])
        ) {
            $combinedTerms[] = $manufacturer . ' ' .
                $rowData['Model'] . ' ' .
                $rowData['Size'];
        }

        // Thêm tất cả chuỗi kết hợp vào searchTerms
        $searchTerms = array_merge($searchTerms, $combinedTerms);


        // Ghép tất cả các terms lại, loại bỏ duplicate, lowercase
        $textSearch = implode(' ', $searchTerms);
        $textSearch = mb_strtolower($textSearch, 'UTF-8');

        // Remove extra whitespace
        $textSearch = preg_replace('/\s+/', ' ', $textSearch);
        $textSearch = trim($textSearch);

        return $textSearch;
    }
    public function listFeatureMapping()
    {
        $featureMapping = [
            '01' => ['vi' => 'Độ mòn thấp', 'en' => 'Low Wear'],
            '02' => ['vi' => 'Vận hành êm ái', 'en' => 'Smooth Operation'],
            '03' => ['vi' => 'Vận hành yên tĩnh', 'en' => 'Quiet Operation'],
            '04' => ['vi' => 'Tiết kiệm nhiên liệu', 'en' => 'Fuel Efficient'],
            '05' => ['vi' => 'Đi đường đất đá', 'en' => 'Rocky / Off-road Capability'],
            '06' => ['vi' => 'Tải trọng lớn', 'en' => 'Heavy Load Capacity'],
            '07' => ['vi' => 'Dành cho xe 16 chỗ vận chuyển hành khách', 'en' => 'For 16-seat Passenger Vehicles'],
            '08' => ['vi' => 'Hiệu suất đường ướt', 'en' => 'Wet Road Performance'],
            '09' => ['vi' => 'Hiệu suất đường khô', 'en' => 'Dry Road Performance'],
            '10' => ['vi' => 'Vận hành ổn định, an toàn', 'en' => 'Stable and Safe Operation'],
            '11' => ['vi' => 'Tản nhiệt nhanh', 'en' => 'Fast Heat Dissipation'],
            '12' => ['vi' => 'Chịu tải tốt', 'en' => 'Good Load Capacity'],
            '13' => ['vi' => 'Bám đường, chống trơn trượt', 'en' => 'Good Grip and Anti-slip'],
            '14' => ['vi' => 'Đường quanh co, đèo dốc', 'en' => 'Winding and Slope Roads'],
            '15' => ['vi' => 'Đường công trình, hầm mỏ, đất đá', 'en' => 'Construction, Mining and Rocky Roads'],
            '16' => ['vi' => 'Chống cắt chém tốt, độ bền cao', 'en' => 'Cut Resistant and High Durability'],
            '17' => ['vi' => 'Phù hợp chạy tốc độ thấp', 'en' => 'Suitable for Low Speed'],
        ];
        return $featureMapping;
    }
    private function assignFixedProductFeaturesByCategory($productId)
    {
        // Map theo code danh mục
        $featureByCategoryCode = [
            // Venturer -> 01,02,03,04
            'lop-advenza-pcr-venturer' => ['01', '02', '03', '04'],
            'lop-advenza-pcr-coverer' => ['01', '02', '03', '04', '05', '06'],
            'lop-advenza-pcr-discoverer' => ['01', '02', '03', '04', '05', '06'],
            'lop-advenza-pcr-traveller' => ['01', '02', '03', '04', '07'],

            'lop-oto-radial-bo-thep-greenstone' => ['10',  '11', '04', '13'],
            'lop-oto-radial-bo-thep-bluestone' => ['10',  '11', '12', '13', '14'],
            'lop-oto-radial-bo-thep-redstone' => ['10',  '12', '15', '16', '17'],
            'lop-oto-radial-bo-thep-silverstone' => ['10',  '12', '15', '16', '17'],

            //xe may
            'sam-lop-xe-may-lop-tubeless-euromina-e-series' => ['01', '02',  '04', '05'],

        ];

        $categoryCodes = DB::table('product_product_category')
            ->join('product_categories', 'product_product_category.product_category_id', '=', 'product_categories.id')
            ->where('product_product_category.product_id', $productId)
            ->pluck('product_categories.code')
            ->toArray();

        if (empty($categoryCodes)) {
            return;
        }

        $featureCodes = [];
        foreach ($categoryCodes as $code) {
            if (isset($featureByCategoryCode[$code])) {
                $featureCodes = array_merge($featureCodes, $featureByCategoryCode[$code]);
            }
        }

        $featureCodes = array_values(array_unique($featureCodes));
        if (empty($featureCodes)) {
            return;
        }

        $this->assignProductFeatures($productId, implode(',', $featureCodes));
    }
    private function buildDescriptionFeaturesByCategory($productId, $name): array
    {
        // ✅ ƯU TIÊN: Map theo file JSON trước (để tùy chỉnh theo từng file)
        $mapByFile = [
            'xe_tai_xam_bias.json' => [
                'description' => '<div>Sản phẩm dùng cho lốp ô tô bias bố nylon.</div>',

                'features' => '<ul>'
                    . '<li>Được sản xuất bằng dây chuyền, công nghệ hiện đại.</li>'
                    . '<li>Sản xuất bằng cao su thiên nhiên, dẻo dai, chịu nhiệt cao.</li>'
                    . '<li>Sử dụng cao su Butyl dẻo dai, giữ hơi lâu, vá ép dễ.</li>'
                    . '</ul>',
            ],
            'xe_tai_xam_radial.json' => [
                'description' => '<div>Sử dụng cho lốp toàn thép RADIAL.</div>',

                'features' => '<ul>'
                    . '<li>Được sản xuất bằng cao su Butyl dẻo dai, giúp giữ hơi lâu, chịu nhiệt cao và đặc biệt dễ dàng cho việc vá ép.</li>'
                    . '<li>Casumina đầu tư dây chuyền, công nghệ hiện đại, đáp ứng được nhu cầu thị trường ngày càng cao.</li>'
                    . '</ul>',
            ],
            'xe_tai_yem_bias.json' => [
                'description' => '<div>Sản phẩm dùng cho lốp ô tô bias bố nylon.</div>',

                'features' => '<ul>'
                    . '<li>Được sản xuất bằng dây chuyền, công nghệ hiện đại.</li>'
                    . '<li>Sản xuất bằng cao su thiên nhiên, dẻo dai, chịu nhiệt cao.</li>'
                    . '</ul>',
            ],
            'xe_tai_yem_radial.json' => [
                'description' => '<div>Sử dụng cho lốp toàn thép RADIAL.</div>',

                'features' => '<ul>'
                    . '<li>Yếm sử dụng thêm miếng kim loại lót tại ổ van, giúp tăng độ bền và bảo vệ van tối ưu.</li>'
                    . '<li>Được sản xuất bằng dây chuyền, công nghệ hiện đại.</li>'
                    . '<li>Sản xuất bằng cao su thiên nhiên, dẻo dai, chịu nhiệt cao.</li>'
                    . '</ul>',
            ],
            'xe_dien_xe_dap.json' => [
                'description' => '<div>Sản phẩm dùng cho xe đạp điện chạy đường trường, đường đô thị.</div>',

                'features' => '<ul>'
                    . '<li>Cao su mặt lốp được thiết kế chống trơn trượt, mang lại khả năng kháng mòn tốt.</li>'
                    . '<li>Kiểu gai mới hiện đại, giúp xe vận hành êm ái trên nhiều địa hình và có độ bền cao.</li>'
                    . '</ul>',
            ],
            'xe_dien_xe_may.json' => [
                'description' => '<div>Sản phẩm dùng cho xe máy điện chạy đường trường, đường đô thị.</div>',

                'features' => '<ul>'
                    . '<li>Lốp Tubeless (không săm) hiện đại, an toàn và giảm thiểu rủi ro khi cán phải vật nhọn.</li>'
                    . '<li>Cao su mặt lốp được thiết kế chống trơn trượt, mang lại khả năng kháng mòn tốt.</li>'
                    . '<li>Vận hành êm ái, độ bền cao, thiết kế đẹp và thân thiện với môi trường.</li>'
                    . '</ul>',
            ],
            'xe-dap-xam-xe-dap.json' => [
                'description' => '<div>Sản phẩm được sản xuất bằng cao su thiên nhiên chất lượng cao.</div>',

                'features' => '<ul>'
                    . '<li>Công nghệ săm đúc hiện đại với van liền, đảm bảo độ kín khít và an toàn tuyệt đối.</li>'
                    . '<li>Chất liệu dẻo dai, khả năng giữ hơi lâu và chịu nhiệt tốt trong điều kiện vận hành khắc nghiệt.</li>'
                    . '</ul>',
            ],
        ];

        // ✅ Check theo file JSON trước (ưu tiên cao nhất)
        if (isset($mapByFile[$this->jsonFileName])) {
            $result = $mapByFile[$this->jsonFileName];

            return $result;
        }

        // Map theo code danh mục
        $map = [
            'lop-advenza-pcr-venturer' => [
                'description' => '<div style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);"><div style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);"><div style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);"><span style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0); font-weight: 600;">VENTURER</span>&nbsp;- Sản phẩm dùng cho các dòng xe Sedan , Hatchback, Crossover “<span style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0); font-weight: 600;">VẬN HÀNH ÊM ÁI&nbsp;</span></div><div style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);"><span style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0); font-weight: 600;">THOẢI MÁI LƯỚT NHANH</span>” chạy an toàn , êm nhẹ với một cám giác lái cực tốt từ</div><div style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);">&nbsp;sự linh hoạt trên các tuyến đường đô thị đến tốc độ cao trên các tuyến đường cao tốc.</div></div></div> ' . $name . ' với đặc tính Độ mòn thấp ,Vận hành êm ái, Vận hành yên tĩnh ,Tiết kiệm nhiên liệu',

                'features' => '<ul style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);"><li style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);">Lốp không săm tubeless (TL)</li><li style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);">Kết cấu thép đặc biệt, chịu tải cao, chống va đập tốt, vận hành ổn định trên nhiều điều kiện mặt đường.</li><li style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);">Hông lốp mềm dẻo, khung sườn cứng vững, tạo cảm giác lái êm ái, thoải mái mà vẫn đảm bảo độ an toàn cao.</li><li style="text-align: justify; scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);">Hợp chất cao su tiên tiến, chống cắt xé tốt, chịu được dải nhiệt độ rộng, thích hợp với thời tiết và địa hình tại Việt Nam.</li><li style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);">Rãnh gai thiết kế đan xen linh hoạt, giúp giảm ồn, thoát nước tốt và làm mát hiệu quả.</li><li style="scrollbar-color: rgba(36, 31, 45, 0.16) rgba(0, 0, 0, 0);">Hoa lốp dạng hướng dọc cùng 3 rãnh chính cân đối, hỗ trợ điều khiển ổn định, phanh chính xác, tiết kiệm nhiên liệu và thân thiện môi trường.</li></ul>',
            ],
            'lop-advenza-pcr-coverer' => [
                'description' => '<div><b>COVERER</b> - Sản phẩm dùng cho các dòng xe đa dụng cỡ lớn, thể thao đa dụng (SUV,CUV, MPV), “ĐÁNH THỨC BẢN LĨNH BÊN TRONG”, lốp chạy được đa dạng địa hình, dành cho những tay lái thích khám phá, cá tính và năng động. Phù hợp cho các loại xe: Ford Everest, Ranger WT. M</div><div><br></div>' . $name . ' với đặc tính Độ mòn thấp ,Vận hành êm ái, Vận hành yên tĩnh ,Tiết kiệm nhiên liệu,Đi đường đất đá,Tải trọng lớn',

                'features' => '<ul>
                <li>Lốp không săm tubeless (TL)</li>
                <li>Kết cấu thép chịu tải cao, chống va đập tốt, đảm bảo vận hành ổn định trên nhiều loại địa hình, thích hợp cho các dòng SUV, CUV, MPV cỡ lớn</li>
                <li>Hông lốp linh hoạt, khung sườn cứng vững, tạo cảm giác lái thoải mái, êm ái, đồng thời nâng cao độ an toàn và độ bền kết cấu</li>
                <li>Hợp chất cao su được nghiên cứu chuyên biệt, có khả năng kháng cắt xé tốt, chịu được biên độ nhiệt lớn, phù hợp với khí hậu và địa hình Việt Nam</li>
                <li>Rãnh gai đan xen linh hoạt, giúp giảm tiếng ồn, thoát nhiệt tốt, tăng độ bám và kiểm soát lái</li>
                <li>Bốn rãnh chính chạy dọc giữa mặt lốp, hỗ trợ giữ cân bằng khi lái xe địa hình, phanh tốt, chống trượt hiệu quả, tiết kiệm nhiên liệu và thân thiện với môi trường"</li></ul>',
            ],
            'lop-advenza-pcr-discoverer' => [
                'description' => '<div><b>DISCOVERER</b> - Sản phẩm chuyên cho các dòng xe bán tải, thể thao đa dụng, lốp chạy được đa dạng địa hình, mạnh mẽ, an toàn, chinh phục mọi hành trình.</div><div><br></div> ' . $name . ' với đặc tính Độ mòn thấp ,Vận hành êm ái, Vận hành yên tĩnh ,Tiết kiệm nhiên liệu,Đi đường đất đá,Tải trọng lớn',
                'features' => '<div><br></div><ul><li>Lốp không săm tubeless (TL)</li><li>Kết cấu thép chịu tải cao, chống va đập tốt, đảm bảo vận hành ổn định trên nhiều loại địa hình, thích hợp cho các dòng xe bán tải, thể thao đa dụng.</li><li>Hông lốp linh hoạt, khung sườn cứng vững, tạo cảm giác lái thoải mái, êm ái, đồng thời nâng cao độ an toàn và độ bền kết cấu.</li><li>Thiết kế khối gai lệch kết hợp ngạnh bên, tối ưu lực kéo, bám đường vượt trội.</li><li>Thành lốp tản nhiệt hiệu quả, tăng chịu tải, kéo dài tuổi thọ.</li><li>Rãnh gai so le, tăng độ vững, ổn định, hạn chế giữ đá.</li><li>Hợp chất cao su cao cấp, bền bỉ, ổn định trên cả mặt đường khô và trơn trượt.</li><li>Hoa lốp thiết kế cá tính và mạnh mẽ, hỗ trợ điều khiển ổn định, phanh chính xác trên nhiều loại địa hình, tiết kiệm nhiên liệu và thân thiện môi trường.</li></ul>',
            ],
            'lop-advenza-pcr-traveller' => [
                'description' => '<div><b>TRAVELER</b> - Sản phẩm dùng cho dòng xe thương mại, vận chuyển hành khách, mini bus, các loại xe tải nhẹ. “Rút ngắn hành trình an toàn về bến”, lốp dành cho những nhà xe thông thái giúp tiết kiệm chi phí..</div><div><br></div> ' . $name . ' với đặc tính Độ mòn thấp ,Vận hành êm ái, Vận hành yên tĩnh ,Tiết kiệm nhiên liệu,Dành cho xe 16 chỗ vận chuyển hành khách',
                'features' => '<div><br></div><ul><li>Lốp không săm tubeless (TL)</li><li>Kết cấu thép đặc biệt chịu tải cao, chống va đập tốt, vận hành ổn định trên nhiều loại đường, phù hợp cho xe thương mại và vận chuyển hành khách.</li><li>Hông lốp chịu tải và cường lực cao, mang lại cảm giác lái êm ái, thoải mái, đảm bảo độ cứng vững và an toàn cao khi di chuyển liên tục trên cao tốc và đường trường.</li><li>Hợp chất cao su được nghiên cứu đặc biệt, giúp kháng cắt xé tốt, chịu nhiệt cao, phù hợp với xe tải trọng lớn hoạt động ở cường độ cao.</li><li>Hoa lốp thiết kế hướng dọc kết hợp các rãnh chính sâu và rộng, hỗ trợ giữ cân bằng tốt, phanh hiệu quả, tiết kiệm nhiên liệu và thân thiện với môi trường."</li></ul>',
            ],

            'lop-oto-radial-bo-thep-greenstone' => [
                'description' => '<div><b>GREENSTONE</b> - Lốp TBR phù hợp các phương tiện vận chuyển chạy đường dài, tốc độ cao. CASUMINA RADIAL GREENSTONE được tạo nên từ cấu trúc, thành phần cao su và thiết kế kiểu gai giúp lốp tản nhiệt nhanh, định hướng và chịu tải tốt; nhờ đó xe vận hành ổn định, an toàn và tiết kiệm nhiên liệu.</div><div><br></div> '
                    . $name
                    . ' với đặc tính: Vận hành ổn định, an toàn; Tản nhiệt nhanh; Tiết kiệm nhiên liệu; Bám đường, chống trơn trượt ',
                'features' => '<div><br></div><ul>'
                    . '<li>Kiểu gai định hướng dọc, ba hàng gai giữa liên tục: dẫn hướng tốt, ít hao nhiên liệu, chạy nhẹ và êm xe.</li>'
                    . '<li>Vai và mặt lốp có nhiều rãnh nhỏ, thoát nước tốt: bám đường tốt trong nhiều điều kiện đường.</li>'
                    . '<li>Mặt chạy rộng, kiểu gai liên khối: hạn chế mòn không đều, thoát nhiệt tốt, độ bền cao.</li>'
                    . '<li>Kiểu gai dọc với ba hàng gai thẳng đều ở giữa đỉnh lốp: dẫn hướng tốt, ít hao nhiên liệu.</li>'
                    . '<li>Rãnh gai đa dạng, “răng cua” lệch nhau với nhiều khối gai nhỏ hỗ trợ: thoát nhiệt tốt, chống đinh, hạn chế nhét đá.</li>'
                    . '</ul>',
            ],
            'lop-oto-radial-bo-thep-bluestone' => [
                'description' => '<div><b>BLUESTONE</b> - Lốp TBR phù hợp các phương tiện vận chuyển chạy ở khu vực địa hình quanh co, đèo dốc, xe phải hoạt động với cường độ cao. CASUMINA RADIAL BLUESTONE được tăng cường về cấu trúc và thành phần cao su, đặc biệt thiết kế kiểu gai giúp gia tăng khả năng chịu tải, bám đường, chống trơn trượt; khả năng phanh thắng và sinh nhiệt thấp giúp xe vận hành an toàn, ổn định khi tải nặng.</div><div><br></div> '
                    . $name
                    . ' với đặc tính: Vận hành ổn định, an toàn; Tản nhiệt nhanh; Chịu tải tốt; Bám đường, chống trơn trượt; Đường quanh co, đèo dốc ',
                'features' => '<div><br></div><ul>'
                    . '<li>Kiểu gai dọc với ba hàng gai đều ở giữa đỉnh lốp: dẫn hướng tốt, ít tiêu hao nhiên liệu.</li>'
                    . '<li>Rãnh gai dạng “răng cua” lệch nhau với nhiều khối gai nhỏ hỗ trợ: thoát nhiệt tốt, chống đinh, hạn chế nhét đá.</li>'
                    . '<li>Công thức cao su pha chế chuyên biệt: tăng độ bền và ổn định khi vận hành cường độ cao.</li>'
                    . '<li>Vai lốp có gờ liên kết các múi gai: cải thiện khả năng chống cắt chém, vỡ gai khi quẹo cua, độ bền cao.</li>'
                    . '</ul>',
            ],
            'lop-oto-radial-bo-thep-redstone' => [
                'description' => '<div><b>REDSTONE</b> - Lốp TBR phù hợp các phương tiện chuyên chạy đường đất đá. Nhờ cấu trúc tăng cường trên toàn bộ khung lốp, thành phần cao su nghiên cứu riêng biệt và kiểu gai thích hợp, CASUMINA RADIAL REDSTONE có khả năng chịu tải trọng cao, va đập tốt, chống cắt chém tốt và độ bền cao. Xe vận hành yên tâm trong điều kiện địa hình xấu như đường công trình, hầm mỏ, đất đá vì lốp bám đường tốt, chống trơn trượt; REDSTONE phù hợp khi chạy ở tốc độ thấp.</div><div><br></div> '
                    . $name
                    . ' với đặc tính: Vận hành ổn định, an toàn; Chịu tải tốt; Đường công trình, hầm mỏ, đất đá; Chống cắt chém tốt, độ bền cao; Phù hợp chạy tốc độ thấp ',
                'features' => '<div><br></div><ul>'
                    . '<li>Khối gai lớn, múi to: chịu tải và va đập tốt, chống trượt.</li>'
                    . '<li>Rãnh gai rộng, thưa: bám đường tốt, đặc biệt trên đường đất đá/công trình; tản nhiệt tốt.</li>'
                    . '<li>Công thức cao su pha chế chuyên biệt: cải thiện khả năng chống cắt chém.</li>'
                    . '</ul>',
            ],
            'lop-oto-radial-bo-thep-silverstone' => [
                'description' => '<div><b>SILVERSTONE</b> - Lốp TBR phù hợp các phương tiện chuyên dùng cho khu vực hầm mỏ, thiết kế đặc biệt cho các dòng xe ben khai thác quặng/khoáng sản với cường độ hoạt động lớn, thường quá tải và tốc độ giới hạn cực thấp. Đây là dòng sản phẩm mới, nổi bật với thiết kế đột phá giúp tăng khả năng chịu quá tải.</div>'
                    . '<div><br></div>'
                    . '<div>Lốp có bề rộng mặt chạy lớn giúp phân bổ tải trọng đều hơn. Các đường gân nối giữa các khối gai ở vai kết hợp với hợp chất cao su đặc biệt giúp tăng độ cứng cho các múi gai và cải thiện khả năng chống chém, cắt cho lốp.</div><div><br></div> '
                    . $name
                    . ' với đặc tính: Vận hành ổn định, an toàn; Chịu tải tốt; Đường công trình, hầm mỏ, đất đá; Chống cắt chém tốt, độ bền cao; Phù hợp chạy tốc độ thấp ',
                'features' => '<div><br></div><ul>'
                    . '<li>Khối gai lớn, múi to: chịu tải tốt, chống kẹp đá, chống tua trượt.</li>'
                    . '<li>Rãnh gai rộng, thưa: bám đường tốt, đặc biệt trên đường đất đá/công trình; tản nhiệt tốt.</li>'
                    . '<li>Công thức cao su pha chế chuyên biệt: cải thiện khả năng chống chém cắt.</li>'
                    . '</ul>',
            ],
            'sam-lop-xe-tai-lop-tai-nhe-bias-nylon' => [
                'description' => '<div><b>Lốp ôtô Bias tải nhẹ tube type</b> (sử dụng săm yếm). Dành cho các dòng xe tải vành nhỏ hoặc bằng 16 inch, tải trọng trung bình thấp, vận chuyển cự ly vừa/nội đô. Lốp vận hành bền bỉ, dùng được nhiều loại đường sá; mặt lốp mềm dẻo, bám đường tốt.</div><div><br></div> ',
                'features' => '<div><br></div><ul>'
                    . '<li>Gai lốp thiết kế linh hoạt, theo kiểu dáng hoa gai xuôi và gai ngang.</li>'
                    . '<li>Kết cấu vải mành chéo nhiều lớp: tăng khả năng chịu tải.</li>'
                    . '<li>Gót tanh kết cấu 1 hoặc 2 vòng tanh: cứng, vững khi sử dụng.</li>'
                    . '<li>Phù hợp cung đường ngắn và môi trường đô thị: linh hoạt, tăng khả năng chống cắt chém.</li>'
                    . '<li>Độ bền cao, hiệu quả kinh tế tốt, tiết kiệm chi phí.</li>'
                    . '</ul>',
            ],
            'sam-lop-xe-tai-lop-tai-nang-bias-nylon' => [
                'description' => '<div><b>Lốp ôtô Bias tải nặng tube type</b> (sử dụng săm yếm). Dành cho các dòng xe tải trọng lớn, vành từ 20 inch trở lên, di chuyển cự ly xa với độ bền bỉ vượt trội, chịu va đập và mài mòn tốt.</div><div><br></div> ',
                'features' => '<div><br></div><ul>'
                    . '<li>Gai lốp được nghiên cứu và thiết kế linh hoạt, hoa gai xuôi và gai ngang.</li>'
                    . '<li>Kết cấu vải mành chéo nhiều lớp: tăng khả năng chịu tải.</li>'
                    . '<li>Gót tanh kết cấu 2 vòng tanh: cứng, vững khi sử dụng.</li>'
                    . '<li>Hiệu quả trong môi trường công trường, cầu cảng; tăng khả năng chống cắt chém khi chạy đường xấu.</li>'
                    . '<li>Độ bền cao, tăng thời gian sử dụng, tiết kiệm chi phí, hiệu quả kinh tế cao.</li>'
                    . '<li>Phát nhiệt thấp khi chạy với tốc độ trung bình - cao.</li>'
                    . '</ul>',
            ],
            'sam-lop-xe-tai-lop-tai-nhe-pcr' => [
                'description' => '<div><b>Lốp không săm tubeless</b> - Sản phẩm dùng cho xe tải nhẹ/thương mại, phù hợp chạy đường đô thị.</div><div><br></div> ',
                'features' => '<div><br></div><ul>'
                    . '<li>Kết cấu thép đặc biệt chịu tải cao, chịu va đập trên nhiều loại địa hình khác nhau.</li>'
                    . '<li>Rãnh gai được thiết kế theo dạng hướng dọc: dẫn đường tốt.</li>'
                    . '<li>Chạy tốt trên nhiều loại đường phức tạp.</li>'
                    . '<li>Rãnh gai có nhiều rãnh nhỏ chéo xen kẽ: dễ điều khiển, giúp tản nhiệt tốt.</li>'
                    . '<li>Ba rãnh chính đảm bảo thoát nước tốt, tiết kiệm nhiên liệu và thân thiện với môi trường.</li>'
                    . '</ul>',
            ],
            ///Xe may
            'sam-lop-xe-may-lop-tubeless-euromina-e-series' => [
                'description' => '<div><b>E-SERIES</b> - Dòng E-Series với các kiểu gai nguyên bản theo từng dòng xe, phù hợp cho hầu hết các loại xe tay ga và xe số trong nước như: Honda (SH, Air Blade, Lead...), Yamaha (Nouvo, Exciter...), Piaggio (LX), Suzuki (Hayate, Raider...). E-Series bao gồm: E-City, E-Cross, E-Classic, E-Force, E-Highspeed, E-Highway, E-Racing.</div><div><br></div> '
                    . $name
                    . ' với đặc tính: Vận hành ổn định, an toàn; Tản nhiệt nhanh; Tiết kiệm nhiên liệu; Bám đường, chống trơn trượt ',
                'features' => '<div><br></div><ul>'
                    . '<li>Công nghệ không săm: hạn chế tối đa rủi ro xẹp lốp trên đường, giúp an toàn và yên tâm khi di chuyển.</li>'
                    . '<li>Hiệu suất sử dụng cao: công thức cao su cải tiến, tăng khả năng chống mài mòn, kéo dài tuổi thọ lốp và ổn định ở tốc độ cao.</li>'
                    . '<li>Tăng cường chịu tải: hông lốp được gia cố cứng vững, tăng khả năng chịu tải, phù hợp nhu cầu chở nặng.</li>'
                    . '<li>Thoát nước tốt: rãnh gai thiết kế định hướng giúp thoát nước tốt, tăng bám đường khi chạy trên đường ướt.</li>'
                    . '<li>Thiết kế thể thao: kiểu gai mạnh mẽ, thể thao, phù hợp phong cách hiện đại.</li>'
                    . '</ul>',
            ],
            'sam-lop-xe-may-lop-tubeless-euromina-advance' => [
                'description' => '<div><b>EUROMINA ADVANCE</b> - Dòng Advance được kế thừa từ những ưu điểm xuất sắc của dòng lốp E-Series. Lốp EUROMINA ADVANCE được phát triển với những kiểu gai đột phá, giúp tối ưu vận hành và mang lại trải nghiệm mới, phù hợp với nhu cầu thực tế khi chạy xe trên đường.</div><div><br></div> '
                    . $name,
                'features' => '<div><br></div><ul>'
                    . '<li>Công nghệ không săm: hạn chế tối đa rủi ro xẹp lốp trên đường, giúp an toàn và yên tâm khi di chuyển.</li>'
                    . '<li>Hiệu suất sử dụng cao: công thức cao su cải tiến, tăng khả năng chống mài mòn, kéo dài tuổi thọ lốp và ổn định ở tốc độ cao.</li>'
                    . '<li>Tăng cường chịu tải: hông lốp được gia cố cứng vững, tăng khả năng chịu tải, phù hợp nhu cầu chở nặng.</li>'
                    . '<li>Thoát nước tốt: rãnh gai thiết kế định hướng giúp thoát nước tốt, tăng bám đường khi chạy trên đường ướt.</li>'
                    . '<li>Thiết kế thể thao: kiểu gai mạnh mẽ, thể thao, phù hợp phong cách hiện đại.</li>'
                    . '</ul>',
            ],
            'sam-lop-xe-may-lop-tubeless-euromina-m75' => [
                'description' => '<div><b>M75</b> - Dòng lốp xe máy không săm được thiết kế dành cho những ai yêu sự bền bỉ và an toàn trên mọi cung đường. Với cấu trúc gai lốp thể thao xen kẽ các rãnh thoát nước thông minh, M75 mang lại độ bám vượt trội cả khi trời mưa lẫn nắng. Chất liệu cao su cao cấp giúp hạn chế mài mòn, tăng tuổi thọ lốp, đồng thời giảm tối đa nguy cơ thủng/xẹp đột ngột nhờ công nghệ không săm tiên tiến. M75 là lựa chọn đáng tin cậy để bạn tự tin lăn bánh mỗi ngày. Thích hợp cho các dòng xe tay ga phổ biến như Janus (Yamaha), NVX (Yamaha)...</div><div><br></div> '
                    . $name,
                'features' => '<div><br></div><ul>'
                    . '<li>Công nghệ không săm: hạn chế tối đa rủi ro xẹp lốp trên đường, giúp an toàn và yên tâm khi di chuyển.</li>'
                    . '<li>Hiệu suất sử dụng cao: công thức cao su cải tiến, tăng khả năng chống mài mòn, giúp kéo dài tuổi thọ lốp và ổn định ở tốc độ cao.</li>'
                    . '<li>Tăng cường chịu tải: hông lốp được gia cố cứng vững, tăng khả năng chịu tải, phù hợp nhu cầu chở nặng.</li>'
                    . '<li>Thoát nước tốt: rãnh gai thiết kế định hướng giúp thoát nước tốt, tăng khả năng bám khi xe chạy trên đường ướt.</li>'
                    . '<li>Thiết kế thể thao: kiểu gai mạnh mẽ, thể thao, phù hợp phong cách hiện đại.</li>'
                    . '</ul>',
            ],
            'sam-lop-xe-may-lop-tubeless-euromina-lop-dac-trung-casumina' => [
                'description' => '<div><b>Dòng sản phẩm tâm huyết của Casumina</b> - Hoa gai đẹp, thời trang và riêng biệt với những đặc tính tối ưu, vượt trội phù hợp với từng loại địa hình, mang một phong cách rất Casumina. Bao gồm các mẫu: Dragon, Fireking, Swordman, Phoenix, Lightning.</div><div><br></div> '
                    . $name,
                'features' => '<div><br></div><ul>'
                    . '<li>Công nghệ không săm: hạn chế tối đa rủi ro xẹp lốp trên đường, giúp an toàn và yên tâm khi di chuyển.</li>'
                    . '<li>Hiệu suất sử dụng cao: công thức cao su cải tiến, tăng khả năng chống mài mòn, giúp kéo dài tuổi thọ lốp và ổn định ở tốc độ cao.</li>'
                    . '<li>Tăng cường chịu tải: hông lốp được gia cố cứng vững, tăng khả năng chịu tải, phù hợp nhu cầu chở nặng.</li>'
                    . '<li>Thoát nước tốt: rãnh gai thiết kế định hướng giúp thoát nước tốt, tăng khả năng bám khi xe chạy trên đường ướt.</li>'
                    . '<li>Thiết kế thể thao: kiểu gai mạnh mẽ, thể thao, phù hợp phong cách hiện đại.</li>'
                    . '</ul>',
            ],
            'sam-lop-chuyen-dung-lop-nong-nghiep' => [
                'description' => '<div><b>Lốp sử dụng cho xe Máy Kéo (Máy Cày)</b> phục vụ cho Nông nghiệp.</div>',
                'features' => '<ul>'
                    . '<li>Lốp sử dụng săm (tube type).</li>'
                    . '<li>Thiết kế cứng cáp, phù hợp cho đường rừng; tăng độ bám và khả năng kháng đâm thủng.</li>'
                    . '<li>Hoa lốp lớn, bản rộng giúp tăng sức kéo, xe di chuyển nhẹ nhàng và giảm tiêu hao nhiên liệu.</li>'
                    . '<li>Phù hợp cho xe vận hành trên đường sình lầy, đường đất.</li>'
                    . '<li>Giá thành cạnh tranh, giúp người nông dân tiết kiệm chi phí.</li>'
                    . '</ul>',
            ],
            'sam-lop-chuyen-dung-lop-cong-nghiep' => [
                'description' => '<div>Lốp sử dụng cho xe công trình dân dụng, công nghiệp và công nghiệp phụ trợ...vv.</div>',

                'features' => '<ul>'
                    . '<li>Các dòng lốp công nghiệp được nghiên cứu, pha chế cao su đạt chất lượng tối ưu, đáp ứng nhu cầu thị trường.</li>'
                    . '<li>Độ bền cao, tiết kiệm chi phí, mang lại hiệu quả tốt nhất khi sử dụng.</li>'
                    . '</ul>',

            ],
            'sam-lop-xe-may-sam-xe-may' => [
                'description' => '<div>"- Được sản xuất bằng cao su Butyl, Cao su tổng hợp, cao su thiên nhiên...</div>',

                'features' => '<ul>'
                    . '<li>- Giữ hơi lâu, chịu nhiệt tốt vá ép dễ.</li>'
                    . '</ul>',

            ],
            'sam-yem-oto' => [
                'description' => '<div><b>Lốp ôtô Bias tải nặng tube type</b> (sử dụng Săm yếm).</div>'
                    . '<div>Dành cho các dòng xe tải trọng lớn, vành từ 20 inch trở lên, di chuyển cự ly xa với độ bền bỉ vượt trội, chịu va đập và mài mòn tốt.</div>',

                'features' => '<ul>'
                    . '<li>Gai lốp được nghiên cứu và thiết kế linh hoạt, hoa gai xuôi và gai ngang.</li>'
                    . '<li>Kết cấu vải mành chéo nhiều lớp giúp tăng khả năng chịu tải.</li>'
                    . '<li>Gót tanh kết cấu 2 vòng tanh, cứng và vững khi sử dụng.</li>'
                    . '<li>Hiệu quả trong môi trường công trường, cầu cảng; tăng khả năng chống cắt chém khi chạy đường xấu.</li>'
                    . '<li>Độ bền cao, tăng thời gian sử dụng, tiết kiệm chi phí, hiệu quả kinh tế cao.</li>'
                    . '<li>Phát nhiệt thấp khi chạy với tốc độ trung bình – cao.</li>'
                    . '</ul>',
            ],

        ];

        $categoryCodes = DB::table('product_product_category')
            ->join('product_categories', 'product_product_category.product_category_id', '=', 'product_categories.id')
            ->where('product_product_category.product_id', $productId)
            ->pluck('product_categories.code')
            ->toArray();

        foreach ($categoryCodes as $code) {
            if (isset($map[$code])) {
                return $map[$code];
            }
        }

        return [
            'description' => '',
            'features' => '',
        ];
    }

    /**
     * Tạo vehicle fitments từ row data
     * 
     * @param int $productId
     * @param array $rowData
     * @return void
     */
    private function createVehicleFitments($productId, $rowData)
    {
        // Lấy thông tin manufacturer, model, year từ row data
        $manufacturer = null;
        $model = null;
        $year = null;

        // Extract manufacturer
        if (isset($rowData['Hãng xe'])) {
            $manufacturer = trim($rowData['Hãng xe']);
        } elseif (isset($rowData['Manufacturer'])) {
            $manufacturer = trim($rowData['Manufacturer']);
        }

        // Extract model
        if (isset($rowData['Model'])) {
            $model = trim($rowData['Model']);
        }

        // Extract year
        if (isset($rowData['Năm sản xuất'])) {
            $yearRaw = $rowData['Năm sản xuất'];
        } elseif (isset($rowData['Production Year'])) {
            $yearRaw = $rowData['Production Year'];
        } else {
            $yearRaw = null;
        }

        // Kiểm tra có ít nhất manufacturer hoặc model
        if (empty($manufacturer) && empty($model)) {
            return; // Không có thông tin vehicle nào
        }

        // Parse years (có thể là range hoặc multiple years)
        $years = [];
        if ($yearRaw !== null && $yearRaw !== '') {
            $years = $this->parseProductionYear($yearRaw);
        }

        // Nếu không có year, tạo 1 fitment với year = null
        if (empty($years)) {
            $years = [null];
        }

        // Tạo fitments cho mỗi year
        foreach ($years as $year) {
            try {
                // Check xem fitment đã tồn tại chưa
                $exists = DB::table('product_vehicle_fitments')
                    ->where('product_id', $productId)
                    ->where(function ($query) use ($manufacturer) {
                        if ($manufacturer) {
                            $query->where('manufacturer', $manufacturer);
                        } else {
                            $query->whereNull('manufacturer');
                        }
                    })
                    ->where(function ($query) use ($model) {
                        if ($model) {
                            $query->where('model', $model);
                        } else {
                            $query->whereNull('model');
                        }
                    })
                    ->where(function ($query) use ($year) {
                        if ($year) {
                            $query->where('year', $year);
                        } else {
                            $query->whereNull('year');
                        }
                    })
                    ->exists();

                if (!$exists) {
                    DB::table('product_vehicle_fitments')->insert([
                        'product_id' => $productId,
                        'manufacturer' => $manufacturer,
                        'model' => $model,
                        'year' => $year,
                        'is_verified' => true, // Auto-verified khi seed
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Log error nhưng không break seeding process
                Log::error("Error creating vehicle fitment for product {$productId}: " . $e->getMessage());
            }
        }
    }
}
