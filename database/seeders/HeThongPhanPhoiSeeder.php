<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

// class HeThongPhanPhoiSeeder extends Seeder
// {
//     public function run(): void
//     {
//         $dataDir = database_path('seeders/data/hethongphanphoi');

//         if (!File::isDirectory($dataDir)) {
//             $this->command->error("❌ Không tồn tại thư mục: {$dataDir}");
//             return;
//         }

//         // ✅ Tự động lấy tất cả file JSON đúng pattern
//         $jsonFiles = collect(File::files($dataDir))
//             ->filter(fn($f) => Str::lower($f->getExtension()) === 'json')
//             ->map(fn($f) => $f->getPathname())
//             ->values()
//             ->all();


//         if (empty($jsonFiles)) {
//             $this->command->warn("⚠️  Không tìm thấy file JSON nào trong: {$dataDir}");
//             return;
//         }

//         $totalProcessedCount = 0;
//         $totalErrorCount = 0;

//         foreach ($jsonFiles as $jsonFile) {

//             $jsonFileName = pathinfo($jsonFile, PATHINFO_FILENAME); // vd: hethongphanphoiAdvenza

//             // ✅ Resolve category theo slug dựa vào tên file (thử nhiều biến thể)
//             $category = $this->resolveCategoryByFileBase($jsonFileName);
//             if (!$category) {
//                 $this->command->error("❌ Không tìm thấy danh mục theo tên file: {$jsonFileName} (slug translations)");
//                 continue;
//             }

//             // ✅ XÓA HẾT POST THUỘC DANH MỤC TRƯỚC KHI SEED
//             $deleted = $this->deletePostsByCategory($category->id);
//             $this->command->info("🗑️  Đã xoá {$deleted} bài viết thuộc danh mục: {$jsonFileName}");

//             // ✅ Đọc + làm sạch JSON nếu có nhiều phần
//             $jsonContent = File::get($jsonFile);

//             $sheet1Pos = strpos($jsonContent, '],');
//             if ($sheet1Pos !== false && strpos($jsonContent, '"Sheet1"') !== false) {
//                 $this->command->info("⚠️  Phát hiện file có nhiều phần, chỉ lấy phần đầu tiên...");
//                 $jsonContent = substr($jsonContent, 0, $sheet1Pos + 1);
//             }

//             $data = json_decode($jsonContent, true);

//             if (!$data || !is_array($data)) {
//                 $this->command->error("❌ Không thể decode JSON từ file: {$jsonFile}");
//                 continue;
//             }

//             foreach ($data as $postData) {
//                 try {
//                     $post = Post::create([
//                         'status' => 'published',
//                         'is_active' => true,
//                         'is_featured' => false,
//                         'sort_order' => 0,
//                         'published_at' => now(),
//                     ]);

//                     $post->postcategories()->attach($category->id, [
//                         'is_primary' => true,
//                         'sort_order' => 1
//                     ]);

//                     $address = $postData['Địa chỉ mới'] ?? '';
//                     $address = $postData['Địa chỉ mới'] ?? '';

//                     // ✅ Cắt từ bất kỳ "http" trở đi (mọi URL)
//                     $address = preg_replace('/\s*https?:\/\/\S+.*$/u', '', $address);
//                     $address = trim($address);

//                     $phone = $postData['Số Điện Thoại'] ?? '';
//                     $email = $postData['E-Mail'] ?? '';
//                     $latitude = $postData['Latitude'] ?? '';
//                     $longitude = $postData['Longitude'] ?? '';

//                     $mapLink = '';
//                     if (!empty($latitude) && !empty($longitude)) {
//                         $mapLink = "{$latitude},{$longitude}";
//                     }

//                     $contentHtml = '<ul class="list-unstyled mb-0">';

//                     if (!empty($address)) {
//                         $contentHtml .= '<li class="d-flex align-items-center gap-2">';
//                         $contentHtml .= '<img src="https://casumina.org/langding/imgs/icon-location.svg" alt="Icon" width="13">';
//                         $contentHtml .= '<a href="#" class="text-muted fs-16 opacity-75">' . e($address) . '</a>';
//                         $contentHtml .= '</li>';
//                     }

//                     if (!empty($phone)) {
//                         $contentHtml .= '<li class="d-flex align-items-center gap-2 mt-2">';
//                         $contentHtml .= '<img src="https://casumina.org/langding/imgs/telephone-call.svg" alt="Icon" width="16">';
//                         $contentHtml .= '<a href="#" class="text-muted fs-16 opacity-75">' . e($phone) . '</a>';
//                         $contentHtml .= '</li>';
//                     }

//                     if (!empty($email)) {
//                         $contentHtml .= '<li class="d-flex align-items-center gap-2 mt-2">';
//                         $contentHtml .= '<img src="https://casumina.org/langding/imgs/icon-mail.svg" alt="Icon" width="16">';
//                         $contentHtml .= '<a href="#" class="text-muted fs-16 opacity-75">' . e($email) . '</a>';
//                         $contentHtml .= '</li>';
//                     }

//                     $contentHtml .= '</ul>';

//                     $title = $postData['Tên'] ?? '';

//                     $post->handleTranslations([
//                         'title_vi' => $title,
//                         'title_en' => $title,
//                         'excerpt_vi' => $mapLink,
//                         'excerpt_en' => $mapLink,
//                         'content_vi' => $contentHtml,
//                         'content_en' => $contentHtml,
//                         'meta_title_vi' => $title,
//                         'meta_title_en' => $title,
//                         'meta_description_vi' => $title,
//                         'meta_description_en' => $title,
//                     ]);

//                     $totalProcessedCount++;
//                 } catch (\Exception $e) {
//                     $totalErrorCount++;
//                     $this->command->error("❌ Lỗi khi tạo bài viết: " . $e->getMessage());
//                     if (isset($postData['Tên'])) {
//                         $this->command->error("   Tên: " . $postData['Tên']);
//                     }
//                 }
//             }

//             $this->command->info('✅ Đã tạo ' . count($data) . ' bài viết từ file: ' . basename($jsonFile));
//             $this->command->info("───────────────────────────────────────────────────────");
//         }

//         $this->command->info("");
//         $this->command->info("═══════════════════════════════════════════════════════");
//         $this->command->info("🎉 HOÀN THÀNH TẤT CẢ CÁC FILE!");
//         $this->command->info("   📦 Tổng số bài viết đã xử lý: {$totalProcessedCount}");
//         $this->command->info("   ❌ Tổng số lỗi: {$totalErrorCount}");
//         $this->command->info("═══════════════════════════════════════════════════════");
//     }

//     private function resolveCategoryByFileBase(string $fileBaseName): ?PostCategory
//     {
//         // vd: hethongphanphoiAdvenza
//         $candidates = array_values(array_unique([
//             $fileBaseName,
//             Str::lower($fileBaseName),
//             Str::kebab($fileBaseName),         // hethongphanphoi-advenza
//         ]));

//         return PostCategory::whereHas('translations', function ($q) use ($candidates) {
//             $q->where('language', 'vi')
//                 ->whereIn('slug', $candidates);
//         })->first();
//     }

//     private function deletePostsByCategory(int $categoryId): int
//     {

//         $query = Post::whereHas('postcategories', function ($q) use ($categoryId) {
//             $q->where('postcategories.id', $categoryId);
//         });

//         $count = (clone $query)->count();

//         $query->chunkById(200, function ($posts) {
//             foreach ($posts as $post) {
//                 $post->delete();
//             }
//         });

//         return $count;
//     }
// }

class HeThongPhanPhoiSeeder extends Seeder
{
    /**
     * XÓA TẤT CẢ DATA TỪ HeThongPhanPhoiSeeder
     */
    public function run(): void
    {
        $this->command->info('🗑️  BẮT ĐẦU XÓA DATA TỪ HETHONGPHANPHOI SEEDER...');
        $this->command->info('═══════════════════════════════════════════════════════');

        // ✅ Danh sách các slug categories từ file JSON
        $categorySlugs = [
            'he-thong-phan-phoi-lop-xe-tai',
            'he-thong-phan-phoi-lop-pcr-advenza',
            // Thêm các slug khác nếu có
        ];

        $totalDeleted = 0;

        foreach ($categorySlugs as $slug) {
            $category = PostCategory::whereHas('translations', function ($q) use ($slug) {
                $q->where('language', 'vi')
                    ->where('slug', $slug);
            })->first();

            if (!$category) {
                $this->command->warn("⚠️  Không tìm thấy danh mục: {$slug}");
                continue;
            }

            // Đếm posts
            $postCount = Post::whereHas('postcategories', function ($q) use ($category) {
                $q->where('postcategories.id', $category->id);
            })->count();

            if ($postCount > 0) {
                $this->command->info("🗑️  Đang xóa {$postCount} bài viết từ: {$slug}");

                // Xóa posts
                $deleted = $this->deletePostsByCategory($category->id);
                $totalDeleted += $deleted;

                $this->command->info("   ✅ Đã xóa {$deleted} bài viết");
            } else {
                $this->command->info("   ℹ️  Không có bài viết nào trong: {$slug}");
            }
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info("🎉 HOÀN THÀNH!");
        $this->command->info("   🗑️  Tổng số bài viết đã xóa: {$totalDeleted}");
        $this->command->info('═══════════════════════════════════════════════════════');
    }

    /**
     * Xóa posts theo category ID
     */
    private function deletePostsByCategory(int $categoryId): int
    {
        return DB::transaction(function () use ($categoryId) {

            // Lấy tất cả post IDs thuộc category này
            $postIds = DB::table('post_postcategory')
                ->where('postcategory_id', $categoryId)
                ->pluck('post_id')
                ->unique()
                ->toArray();

            if (empty($postIds)) return 0;

            // ✅ Tách 2 nhóm: post thuộc nhiều category vs chỉ category này
            $postIdsInOtherCategories = DB::table('post_postcategory')
                ->whereIn('post_id', $postIds)
                ->where('postcategory_id', '!=', $categoryId)
                ->pluck('post_id')
                ->unique()
                ->toArray();

            $safeToDelete  = array_diff($postIds, $postIdsInOtherCategories);
            $onlyUnlink    = array_intersect($postIds, $postIdsInOtherCategories);

            // ✅ Post thuộc nhiều category → chỉ gỡ relationship, KHÔNG xóa post
            if (!empty($onlyUnlink)) {
                DB::table('post_postcategory')
                    ->whereIn('post_id', $onlyUnlink)
                    ->where('postcategory_id', $categoryId)
                    ->delete();
            }

            if (empty($safeToDelete)) return 0;

            // ✅ Post chỉ thuộc category này → xóa hoàn toàn
            // post_translations  → tự cascade khi xóa post (không cần xóa thủ công)
            // post_postcategory  → tự cascade khi xóa post (không cần xóa thủ công)
            $deleted = Post::whereIn('id', $safeToDelete)->delete();

            return $deleted;
        });
    }
}
