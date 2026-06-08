<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Hệ thống cơ bản
            LanguageSeeder::class,
            AdminUserSeeder::class,
            UserSeeder::class,

            // 2. Danh mục & Attributes (TRƯỚC sản phẩm)
            #ProductCategorySeeder::class,
            #ProductAttributeSeeder::class,
            #ProductFeatureAttributeSeeder::class, // thêm các thuộc tính "đặc điểm sản phẩm" của lốp xe tai trước khi import 

            // 3. Sản phẩm (sau khi có danh mục & attributes)
            #ProductFromJsonSeeder::class,

            // 4. Tin tức
            #PostCategorySeeder::class,
            #PostSeeder::class,

            // 5. Banner
            #BannerCategorySeeder::class,
            #BannerSeeder::class,
            #CategoryBannerSeeder::class, // ✅ Thêm seeder mới

            //footer và danh mục footer
            #FooterCategorySeeder::class,
            #FooterSeeder::class,

            // 6. Giới thiệu công ty
            #AboutCategorySeeder::class,
            #AboutPostSeeder::class,



            // 7. Seeder Hệ thống phân phối cái này làm trước để show ngoài web theo thứ tự 
            #QuocGiaHTPPMucSeeder::class,
            #TinhThanhHTPPMucSeeder::class,
            #HeThongPhanPhoiDanhMucSeeder::class,
            #HeThongPhanPhoiSeeder::class,


            // 8. Dữ liệu thực trang Tài liệu (Thông tin đăng kiểm)
            #DangKiemCategorySeeder::class, giữ danh mục cha thong-tin-dang-kiem
            #DangKiemDocumentSeeder::class,

            //seeder nhà phân phối vào bảng user để đăng nhập mua hàng 
            #php artisan db:seed --class=NppSeeder

            //  *   php artisan import:external --categories-only    # chỉ import danh mục
            //  *   php artisan import:external --category=01        # chỉ import 1 category (xóa cũ trước)
            //  *   php artisan import:external --skip-categories    # bỏ qua bước import danh mục
            //  *   php artisan import:external --sku=21060071 
            //  *php artisan db:seed --class=CountrySeederLocation
            //php artisan npp:import-showrooms đồng bộ toàn bộ showroom/NPP từ API Casumina.
            // php artisan images:optimize --path=images nén hình thành webp để nhẹ ảnh chạy 1 lần để nén hết ảnh cũ
            // # hoặc
            // php artisan images:optimize-existing --path=images nén hình thành webp để nhẹ ảnh chạy 1 lần để nén hết ảnh cũ
        ]);
    }
}
