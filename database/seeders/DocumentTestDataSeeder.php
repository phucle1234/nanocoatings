<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\PostCategory;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\UploadedFile;

class DocumentTestDataSeeder extends Seeder
{
    /**
     * Dữ liệu test cho trang Tài liệu (dạng cây thư mục).
     * - 6 danh mục gốc (cha), mỗi cha có 2 danh mục con (folder).
     * - File (post + PDF) nằm trong danh mục con; vào /document/{cha} thấy cây folder, bấm folder mới thấy file.
     */
    public function run(): void
    {
        $this->command->info('📄 Tạo dữ liệu test trang Tài liệu (cây thư mục)...');

        $storagePath = 'uploads/documents/posts';
        $fullPath = storage_path('app/' . $storagePath);
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
            $this->command->info('  ✅ Đã tạo thư mục: ' . $storagePath);
        }

        $minimalPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF\n";

        $rootCategories = [
            ['name_vi' => 'Săm lốp ô tô tải', 'name_en' => 'Truck Tires', 'slug' => 'sam-lop-o-to-tai', 'children' => [
                ['name_vi' => 'Lốp tải nhẹ', 'name_en' => 'Light Truck', 'slug' => 'lop-tai-nhe'],
                ['name_vi' => 'Lốp tải nặng', 'name_en' => 'Heavy Truck', 'slug' => 'lop-tai-nang'],
            ]],
            ['name_vi' => 'Săm Lốp Xe Đạp', 'name_en' => 'Bicycle Tires', 'slug' => 'sam-lop-xe-dap', 'children' => [
                ['name_vi' => 'Lốp thể thao', 'name_en' => 'Sport', 'slug' => 'lop-the-thao'],
                ['name_vi' => 'Lốp truyền thống', 'name_en' => 'Traditional', 'slug' => 'lop-truyen-thong'],
            ]],
            ['name_vi' => 'Săm Lốp Xe Máy', 'name_en' => 'Motorcycle Tires', 'slug' => 'sam-lop-xe-may', 'children' => [
                ['name_vi' => 'Lốp Tubeless', 'name_en' => 'Tubeless', 'slug' => 'lop-tubeless'],
                ['name_vi' => 'Săm xe máy', 'name_en' => 'Inner Tubes', 'slug' => 'sam-xe-may'],
            ]],
            ['name_vi' => 'Lốp Avenza PCR', 'name_en' => 'Avenza PCR Tires', 'slug' => 'lop-avenza-pcr', 'children' => [
                ['name_vi' => 'Venturer', 'name_en' => 'Venturer', 'slug' => 'venturer'],
                ['name_vi' => 'Coverer', 'name_en' => 'Coverer', 'slug' => 'coverer'],
            ]],
            ['name_vi' => 'Săm Lốp Xe Điện', 'name_en' => 'Electric Vehicle Tires', 'slug' => 'sam-lop-xe-dien', 'children' => [
                ['name_vi' => 'Lốp xe đạp điện', 'name_en' => 'E-Bike', 'slug' => 'lop-xe-dap-dien'],
                ['name_vi' => 'Lốp xe máy điện', 'name_en' => 'E-Motorcycle', 'slug' => 'lop-xe-may-dien'],
            ]],
            ['name_vi' => 'Săm Lốp Chuyên Dụng', 'name_en' => 'Specialty Tires', 'slug' => 'sam-lop-chuyen-dung', 'children' => [
                ['name_vi' => 'Lốp công nghiệp', 'name_en' => 'Industrial', 'slug' => 'lop-cong-nghiep'],
                ['name_vi' => 'Lốp nông nghiệp', 'name_en' => 'Agricultural', 'slug' => 'lop-nong-nghiep'],
            ]],
        ];

        $sortOrder = 1;
        foreach ($rootCategories as $rootData) {
            $parent = PostCategory::whereHas('translations', function ($q) use ($rootData) {
                $q->where('slug', $rootData['slug'])->where('language', 'vi');
            })->first();

            if (!$parent) {
                $parent = PostCategory::create([
                    'parent_id' => null,
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => $sortOrder++,
                ]);
                $parent->handleTranslations([
                    'name_vi' => $rootData['name_vi'],
                    'name_en' => $rootData['name_en'],
                    'slug_vi' => $rootData['slug'],
                    'slug_en' => $rootData['slug'],
                    'description_vi' => 'Tài liệu ' . $rootData['name_vi'],
                    'description_en' => 'Documents - ' . $rootData['name_en'],
                    'meta_title_vi' => $rootData['name_vi'],
                    'meta_title_en' => $rootData['name_en'],
                    'meta_description_vi' => 'Tài liệu ' . $rootData['name_vi'],
                    'meta_description_en' => 'Documents for ' . $rootData['name_en'],
                ]);
                $this->command->info('  ✅ Danh mục gốc: ' . $rootData['name_vi']);
            }

            $childSort = 1;
            foreach ($rootData['children'] as $childData) {
                $child = PostCategory::whereHas('translations', function ($q) use ($childData) {
                    $q->where('slug', $childData['slug'])->where('language', 'vi');
                })->first();

                if (!$child) {
                    $child = PostCategory::create([
                        'parent_id' => $parent->id,
                        'is_active' => true,
                        'is_featured' => false,
                        'sort_order' => $childSort++,
                    ]);
                    $child->handleTranslations([
                        'name_vi' => $childData['name_vi'],
                        'name_en' => $childData['name_en'],
                        'slug_vi' => $childData['slug'],
                        'slug_en' => $childData['slug'],
                        'description_vi' => $childData['name_vi'],
                        'description_en' => $childData['name_en'],
                        'meta_title_vi' => $childData['name_vi'],
                        'meta_title_en' => $childData['name_en'],
                        'meta_description_vi' => $childData['name_vi'],
                        'meta_description_en' => $childData['name_en'],
                    ]);
                    $this->command->info('    └─ Thư mục con: ' . $childData['name_vi']);
                }

                foreach (['Catalog', 'Tiêu chuẩn chất lượng'] as $docIndex => $docTitle) {
                    $titleVi = $docTitle . ' - ' . $childData['name_vi'];
                    $titleEn = $docTitle . ' - ' . $childData['name_en'];
                    $filename = Str::slug($childData['name_vi']) . '-' . ($docIndex + 1) . '.pdf';
                    $storedName = 'seed-' . $rootData['slug'] . '-' . $childData['slug'] . '-' . ($docIndex + 1) . '-' . uniqid() . '.pdf';
                    $filePath = $storagePath . '/' . $storedName;

                    Storage::disk('local')->put($filePath, $minimalPdf);

                    $uploadedFile = UploadedFile::create([
                        'original_name' => $filename,
                        'stored_name' => $storedName,
                        'path' => $storagePath,
                        'mime_type' => 'application/pdf',
                        'size' => strlen($minimalPdf),
                        'sha256' => hash('sha256', $minimalPdf),
                        'uploaded_by' => null,
                    ]);

                    $post = Post::create([
                        'status' => 'published',
                        'is_active' => true,
                        'is_featured' => false,
                        'sort_order' => $docIndex + 1,
                        'published_at' => now(),
                        'document_file_id' => $uploadedFile->id,
                    ]);

                    foreach (['vi', 'en'] as $lang) {
                        $title = $lang === 'vi' ? $titleVi : $titleEn;
                        $slug = Str::slug($titleVi) . '-' . $childData['slug'] . '-' . $docIndex . ($lang === 'en' ? '-en' : '');
                        PostTranslation::updateOrCreate(
                            ['post_id' => $post->id, 'language' => $lang],
                            [
                                'title' => $title,
                                'slug' => $slug,
                                'excerpt' => 'Tài liệu - ' . $title,
                                'content' => '<p>Nội dung ' . $title . '.</p>',
                            ]
                        );
                    }

                    $post->postcategories()->attach($child->id, ['is_primary' => true, 'sort_order' => $docIndex + 1]);
                }
            }
        }

        $this->command->info('🎉 Xong. /document → cây 6 thư mục; /document/sam-lop-o-to-tai → cây con; /document/sam-lop-o-to-tai/lop-tai-nhe → danh sách file.');
    }
}
