<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Services\SectorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Seeds the 6 wood-industry ("Vật liệu gỗ") articles from
 * 00-Sitemap-va-Ke-hoach-Noi-dung.docx + noidungbaiviet/*.docx into the
 * sector's own news hub (sector-vat-lieu-go-news), split into 3 topic
 * categories per the sitemap plan's grouping (Kỹ thuật / Giới thiệu sản
 * phẩm / Giải pháp), auto-created if missing. Idempotent: re-running
 * updates existing posts (matched by slug) instead of duplicating them.
 *
 * IMPORTANT — do not publish as-is. Every article still contains literal
 * placeholders: [THƯƠNG HIỆU], [TÊN SẢN PHẨM], [DÒNG TRONG SUỐT],
 * [DÒNG CÓ MÀU], [SỐ ĐIỆN THOẠI]. All 6 are seeded with status=draft on
 * purpose — find/replace the placeholders, verify every technical figure
 * against the real product's current TDS/MSDS, add real project photos,
 * then flip status to "published" via /hethongquantriAdmin/post.
 *
 * Run: php artisan db:seed --class=WoodIndustryArticlesSeeder
 */
class WoodIndustryArticlesSeeder extends Seeder
{
    protected const SECTOR_SLUG = 'vat-lieu-go';

    public function run(): void
    {
        $sectorService = app(SectorService::class);
        $sector = $sectorService->findSectorBySlug(self::SECTOR_SLUG);

        if (! $sector) {
            Log::warning("WoodIndustryArticlesSeeder: sector '".self::SECTOR_SLUG."' not found — skipped. Create/publish the sector first, then re-run this seeder.");
            $this->command?->error("Không tìm thấy ngành '".self::SECTOR_SLUG."'. Tạo ngành trước rồi chạy lại seeder.");

            return;
        }

        // Auto-provisions the sector's news hub + default "-chung" child
        // category if they don't exist yet (same mechanism triggered when
        // an admin opens the sector's layout page).
        $sectorService->syncNewsCategories($sector);

        $hub = $sectorService->getOrCreateNewsHub($sector);

        if (! $hub) {
            Log::warning('WoodIndustryArticlesSeeder: could not resolve or create the news hub for the sector — skipped.');
            $this->command?->error('Không tạo được hub tin tức cho ngành. Vào trang layout của ngành 1 lần rồi chạy lại seeder.');

            return;
        }

        $hubSlugVi = $sectorService->getNewsHubSlug($sector, 'vi');

        // The sitemap plan (00-Sitemap-va-Ke-hoach-Noi-dung.docx, section 2)
        // groups the 6 articles into 3 editorial topics — mirror that as 3
        // real news sub-categories so the "media" block's tab UI shows them
        // separately instead of one flat feed.
        $categories = [
            'ky-thuat' => $this->ensureNewsSubCategory($hub, $hubSlugVi, 'ky-thuat', 'Kỹ thuật', 'Technical', 1),
            'gioi-thieu-san-pham' => $this->ensureNewsSubCategory($hub, $hubSlugVi, 'gioi-thieu-san-pham', 'Giới thiệu sản phẩm', 'Product introduction', 2),
            'giai-phap' => $this->ensureNewsSubCategory($hub, $hubSlugVi, 'giai-phap', 'Giải pháp', 'Solutions', 3),
        ];

        foreach ($this->articles() as $article) {
            $this->seedArticle($categories[$article['group']]->id, $article);
        }

        // The generic "-chung" placeholder child was auto-created by
        // syncNewsCategories() above before any of our 3 topic categories
        // existed. All 6 posts now live in their topic category instead, so
        // deactivate it — media.blade.php renders one tab per active child
        // regardless of post count, and would otherwise show a permanently
        // empty "Tin tức chung" tab.
        $this->deactivateEmptyChungCategory($hubSlugVi);

        // Sitemap plan section "Vị trí hiển thị cụ thể trên trang
        // /applications/wood-materials": Khối 2 (video-introduction block)
        // "Chi tiết" button → KT-01; Khối 3 (bestseller block) heading gets
        // a "Tìm hiểu thêm" link → SP-01. Both blocks already read an
        // optional per-category CTA url (see HasBanners::getBannersBySlug()
        // + components/block-banner-heading.blade.php) — just wire it here
        // instead of requiring a manual admin step on every deploy.
        $kt01 = $this->kt01();
        $sp01 = $this->sp01();
        $this->setCategoryCtaUrl($sectorService, $sector, 'video-introduction', $kt01['slug'], $kt01['slug_en']);
        $this->setCategoryCtaUrl($sectorService, $sector, 'home-bestseller', $sp01['slug'], $sp01['slug_en']);

        $this->command?->info('Đã seed 6 bài viết ngành Vật liệu gỗ vào 3 danh mục (Kỹ thuật / Giới thiệu sản phẩm / Giải pháp) dưới hub id '.$hub->id.' (status: draft — cần thay placeholder rồi mới publish).');
    }

    /**
     * Points an already-provisioned sector banner category's "Chi tiết"/CTA
     * link at one of the 6 seeded articles instead of the generic default
     * (e.g. video-introduction's button used to hardcode /about).
     */
    protected function setCategoryCtaUrl(SectorService $sectorService, PostCategory $sector, string $bannerKey, string $slugVi, string $slugEn): void
    {
        $categorySlugVi = $sectorService->getBannerCategorySlug($sector, $bannerKey, 'vi');

        $category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $categorySlugVi))
            ->first();

        if (! $category) {
            return;
        }

        $category->translations()->where('language', 'vi')->update(['url' => url('/post/'.$slugVi)]);
        $category->translations()->where('language', 'en')->update(['url' => url('/post/'.$slugEn)]);
    }

    protected function ensureNewsSubCategory(PostCategory $hub, string $hubSlugVi, string $slugSuffix, string $nameVi, string $nameEn, int $sortOrder): PostCategory
    {
        $slugVi = $hubSlugVi.'-'.$slugSuffix;

        $category = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $slugVi))
            ->first();

        if (! $category) {
            $category = PostCategory::withoutGlobalScopes()->create([
                'parent_id' => $hub->id,
                'is_active' => true,
                'is_featured' => false,
                'is_banner' => false,
                'is_sector' => false,
                'sort_order' => $sortOrder,
            ]);

            $category->handleTranslations([
                'name_vi' => $nameVi,
                'name_en' => $nameEn,
                'slug_vi' => $slugVi,
                'slug_en' => $slugVi.'-en',
            ]);
        }

        return $category;
    }

    protected function deactivateEmptyChungCategory(string $hubSlugVi): void
    {
        $chungSlugVi = $hubSlugVi.'-chung';

        $chung = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $chungSlugVi))
            ->first();

        if ($chung && $chung->is_active && $chung->posts()->count() === 0) {
            $chung->update(['is_active' => false]);
        }
    }

    /**
     * @param  array{slug: string, title: string, excerpt: string, meta_title: string, meta_description: string, content: string, group: string}  $article
     */
    protected function seedArticle(int $categoryId, array $article): void
    {
        $existingTranslation = \App\Models\PostTranslation::where('slug', $article['slug'])->first();
        $post = $existingTranslation ? Post::find($existingTranslation->post_id) : null;

        if (! $post) {
            $post = Post::create([
                'status' => 'draft',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 0,
            ]);
        }

        // sync() (not attach()) — this seeder is the single source of truth
        // for which category an article belongs to. Without it, re-running
        // after moving an article between topic categories (or after an
        // earlier version of this seeder attached everything to the old
        // flat "-chung" category) would leave the post attached to both.
        $post->postcategories()->sync([$categoryId => ['is_primary' => true, 'sort_order' => 0]]);

        $post->handleTranslations([
            'title_vi' => $article['title'],
            'content_vi' => $article['content'],
            'excerpt_vi' => $article['excerpt'],
            'meta_title_vi' => $article['meta_title'],
            'meta_description_vi' => $article['meta_description'],
            'title_en' => $article['title_en'],
            'content_en' => $article['content_en'],
            'excerpt_en' => $article['excerpt_en'],
            'meta_title_en' => $article['meta_title_en'],
            'meta_description_en' => $article['meta_description_en'],
        ]);

        // Post::handleTranslations() always derives slug from Str::slug(title),
        // which won't match the exact SEO slug specified in the sitemap plan
        // — overwrite it directly with the intended one. Also required for
        // /post/{slug} to resolve at all in a given locale: PostService::
        // getPostBySlugOrId() looks up the slug scoped to translations.language,
        // so a post with only a vi row is unreachable while the site defaults
        // to the en locale.
        $post->translations()->where('language', 'vi')->update(['slug' => $article['slug']]);
        $post->translations()->where('language', 'en')->update(['slug' => $article['slug_en']]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function articles(): array
    {
        return [
            $this->kt01(),
            $this->kt02(),
            $this->kt03(),
            $this->sp01(),
            $this->gp01(),
            $this->gp02(),
        ];
    }

    protected function relatedLinks(array $links): string
    {
        $items = collect($links)->map(fn ($l) => '<li><a href="'.$l['href'].'">'.$l['label'].'</a></li>')->implode('');

        return '<div class="related-articles"><h3>Bài viết liên quan</h3><ul>'.$items.'</ul></div>';
    }

    protected function relatedLinksEn(array $links): string
    {
        $items = collect($links)->map(fn ($l) => '<li><a href="'.$l['href'].'">'.$l['label'].'</a></li>')->implode('');

        return '<div class="related-articles"><h3>Related articles</h3><ul>'.$items.'</ul></div>';
    }

    protected function kt01(): array
    {
        $content = <<<'HTML'
<p><strong>Đối tượng đọc:</strong> kỹ sư vật liệu, quản lý xưởng gỗ, thợ thi công và đại lý kỹ thuật.</p>
<p><em>Ghi chú biên tập: thay các placeholder <code>[THƯƠNG HIỆU]</code>, <code>[TÊN SẢN PHẨM]</code>, <code>[DÒNG TRONG SUỐT]</code>, <code>[DÒNG CÓ MÀU]</code> trước khi xuất bản. Mọi số liệu cần đối chiếu TDS/MSDS của lô hàng đang phân phối.</em></p>

<h2>Tóm tắt cho người bận</h2>
<p>Sơn PU, vecni và dầu lau đều thất bại trên gỗ ngoài trời theo cùng một kịch bản: chúng tạo một lớp màng kín trên bề mặt. Gỗ bên dưới vẫn hút và nhả ẩm theo mùa, vẫn giãn nở và co ngót, còn lớp màng thì không co giãn kịp — nên nứt, hơi ẩm bị nhốt lại, và gỗ mục từ bên trong lớp sơn trong khi bề mặt vẫn trông còn tốt.</p>
<p>Lớp phủ nano giải quyết vấn đề ở tầng khác: thay vì phủ lên, nó thẩm thấu vào và đóng rắn thành mạng lưới liên kết chéo mật độ cao ngay trong cấu trúc lỗ rỗng của gỗ. Kết quả là một bề mặt chắn được nước lỏng và tia UV nhưng vẫn cho hơi nước đi qua — thứ mà giới kỹ thuật gọi là bề mặt "thở được".</p>

<h2>1. Gỗ hỏng ngoài trời như thế nào</h2>
<p>Gỗ là vật liệu hữu cơ có hai phần: phần xơ (cellulose và hemicellulose làm nên khung sườn) và phần nhựa/lignin (chất kết dính giữa các sợi). Ngoài trời, ba cơ chế phá hoại chạy song song.</p>

<h3>1.1. Quang phân huỷ lignin</h3>
<p>Lignin là thành phần hấp thụ tia cực tím mạnh nhất trong gỗ. Photon UV mang đủ năng lượng để cắt đứt liên kết hoá học trong phân tử lignin, tạo ra các gốc tự do. Lignin bị phân huỷ chuyển thành các mảnh phân tử tan trong nước, bị mưa rửa trôi, để lộ lại lớp cellulose trần màu xám bạc.</p>
<p>Đây chính là hiện tượng "bạc màu" mà ai cũng thấy trên sàn deck sau một mùa nắng. Nó không phải bụi bẩn — nó là mất chất. Khi lignin đã đi, các sợi cellulose mất chất kết dính và bắt đầu tróc ra thành xơ.</p>

<h3>1.2. Chu kỳ trương nở và co ngót</h3>
<p>Gỗ luôn tìm cân bằng ẩm với không khí xung quanh. Ban ngày nắng gắt, bề mặt gỗ nóng lên và mất ẩm nhanh hơn phần lõi, gây giãn nở không đều theo chiều dày. Ban đêm và khi mưa, quá trình đảo ngược.</p>
<p>Nước mưa ở đô thị và khu công nghiệp không trung tính — nó mang CO₂, SOₓ, NOₓ hoà tan, tạo thành các axit yếu tấn công cấu trúc gỗ. Sau vài trăm chu kỳ, ứng suất tích luỹ vượt ngưỡng và bề mặt nứt chân chim. Với gỗ đã sơn màng, chính các vết nứt này là nơi màng sơn bắt đầu bong.</p>

<h3>1.3. Vi sinh vật</h3>
<p>Nấm, mốc, rêu và vi khuẩn dùng gỗ làm nguồn thức ăn. Trong quá trình trao đổi chất chúng thải ra axit sunphuric và nitric, làm mục rã phần xơ gỗ. Điều kiện tiên quyết để chúng phát triển là độ ẩm đọng lại trên bề mặt — và đây là lý do khả năng thoát hơi nước quan trọng ngang với khả năng chống thấm.</p>

<h2>2. Vì sao lớp màng kín là lời giải sai</h2>
<p>Một lớp PU hai thành phần điển hình tạo màng dày 60–100 µm, gần như không thấm hơi nước. Nghe có vẻ tốt, cho đến khi xét chuyện sau.</p>
<p>Gỗ nội thất cân bằng ở khoảng 8–12% độ ẩm; gỗ ngoài trời ở Việt Nam dao động 14–20% theo mùa. Lượng ẩm này phải đi đâu đó. Khi bề mặt trên bị bít, hơi nước di chuyển ngang và tích tụ ngay dưới màng sơn. Áp suất hơi tăng lên tạo bọt khí, rồi phồng rộp, rồi bong từng mảng.</p>
<p>Tệ hơn: gỗ ẩm bị nhốt dưới màng kín chính là môi trường lý tưởng cho nấm mục. Bề mặt vẫn bóng đẹp trong khi phần gỗ bên dưới đã xốp. Đến lúc phát hiện thì việc sửa không còn là sơn lại mà là thay ván.</p>
<p>Chu trình bảo trì của lớp màng cũng tốn kém: muốn sơn lại phải chà nhám bóc sạch lớp cũ, mỗi lần chà là mất một lượng vật liệu gỗ. Sau ba đến bốn chu kỳ, mặt gỗ mỏng đi thấy rõ, đầu đinh vít lộ ra, vân gỗ mất nét.</p>

<h2>3. Cơ chế của lớp phủ nano</h2>
<h3>3.1. Thành phần — và một hiểu lầm phổ biến</h3>
<p>Có hai họ công nghệ khác nhau đang cùng được gọi là "nano" trên thị trường, cần phân biệt rõ khi tư vấn:</p>
<ul>
<li>Hệ nhựa lai Polyurethane/Polyurea cấu trúc nano, một thành phần (1K). Điểm cần nhấn mạnh: dòng này không chứa hạt nano rắn như nhiều người vẫn tưởng. Chữ "nano" ở đây nói về cấu trúc mạng lưới ở thang nanomet, không phải về hạt độn. Tính năng đến từ mật độ liên kết chéo cực cao giữa các phân tử polyme.</li>
<li>Hệ gốc nước công nghệ fluoride, không chứa silicone. Dòng thẩm thấu, không tạo màng, thiên về khả năng chống thấm và cho hơi nước đi qua.</li>
</ul>
<p>Khi khách hỏi "có hạt nano trong đó không", câu trả lời trung thực cho dòng thứ nhất là: không, và đó là ưu điểm — hạt rắn phân tán trong màng thường là điểm khởi đầu của nứt và tách lớp.</p>

<h3>3.2. Thẩm thấu và neo bám</h3>
<p>Phân tử hoạt chất có kích thước ở thang 10⁻⁹ m — nhỏ hơn nhiều bậc so với đường kính mao quản và lỗ rỗng trên bề mặt gỗ. Nhờ vậy chúng đi vào bên trong thớ gỗ thay vì nằm lại trên mặt. Sau khi vào trong, phản ứng đóng rắn tạo ra mạng lưới ba chiều neo cơ học vào chính cấu trúc lỗ rỗng.</p>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th></th><th>Sơn phủ thông thường</th><th>Lớp phủ cấu trúc nano</th></tr></thead>
<tbody>
<tr><td>Hình học phân tử</td><td>Chuỗi tuyến tính</td><td>Mạng lưới liên kết chéo ba chiều</td></tr>
<tr><td>Mật độ liên kết chéo</td><td>Thấp</td><td>2,17 × 10³ mol/m³</td></tr>
<tr><td>Vị trí lớp bảo vệ</td><td>Nằm trên bề mặt</td><td>Nằm trong lỗ rỗng và trên bề mặt</td></tr>
<tr><td>Cơ chế bám dính</td><td>Bám dính bề mặt</td><td>Neo cơ học vào cấu trúc gỗ</td></tr>
</tbody>
</table>
<p>Mật độ liên kết chéo là chỉ số quyết định gần như mọi tính năng còn lại: độ cứng, kháng hoá chất, kháng mài mòn, và độ bền thời tiết. Mạng lưới càng dày đặc, càng ít có đường cho phân tử ngoại lai len vào và càng khó bị năng lượng UV bẻ gãy.</p>

<h3>3.3. Kháng UV</h3>
<p>Cơ chế kháng UV ở đây là cơ chế rào cản, không phải cơ chế hấp thụ hoá học đơn thuần. Mạng lưới liên kết chéo mật độ cao hoạt động như một hàng rào bền vững hấp thụ và tiêu tán năng lượng UV-A/UV-B trước khi nó chạm tới lignin bên dưới.</p>
<p>Điểm khác biệt so với các chất hấp thụ UV thêm vào sơn thông thường: chất hấp thụ UV dạng phụ gia sẽ tiêu hao dần theo thời gian phơi sáng và di trú ra khỏi màng. Rào cản cấu trúc thì không tiêu hao — nó là chính bộ khung của lớp phủ.</p>
<p><strong>Số liệu kiểm chứng:</strong> thử nghiệm gia tốc thời tiết theo SASO ISO 16474-2 trong 5.000 giờ cho thấy thay đổi màu sắc và độ bóng dưới 2%.</p>

<h3>3.4. Khả năng "thở"</h3>
<p>Đây là điểm bán hàng dễ bị nói sai nhất, nên cần diễn đạt chính xác. Bề mặt được phủ nano chặn nước ở thể lỏng nhưng cho nước ở thể hơi đi qua. Cơ chế vật lý: kích thước phân tử hơi nước nhỏ hơn nhiều so với giọt nước lỏng, vốn bị giữ lại bởi sức căng bề mặt trên một bề mặt kỵ nước. Lớp phủ thẩm thấu không tạo màng liên tục nên vẫn tồn tại đường thoát cho phân tử hơi.</p>
<p>Hệ quả thực tế:</p>
<ul>
<li>Ẩm trong gỗ thoát ra được → không tích tụ áp suất hơi → không phồng rộp, không bong tróc.</li>
<li>Bề mặt luôn khô ráo → nấm mốc và rêu không có điều kiện bám.</li>
<li>Gỗ vẫn giãn nở co ngót tự nhiên mà lớp phủ đi theo, vì lớp phủ nằm trong gỗ chứ không phải là một tấm phim dán lên gỗ.</li>
</ul>
<p><em>Lưu ý khi viết nội dung marketing:</em> nói "gỗ thở được" là chấp nhận được trong văn nói, nhưng trong tài liệu kỹ thuật nên diễn đạt là "duy trì độ thấm hơi nước của bề mặt". Bộ tài liệu nguồn không cung cấp giá trị WVTR (g/m²·24h) cụ thể — nếu cần con số này để đưa vào hồ sơ dự thầu, phải yêu cầu nhà sản xuất cung cấp kết quả đo theo ASTM E96 hoặc ISO 7783.</p>

<h2>4. Bảng so sánh cho tư vấn bán hàng</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Tiêu chí</th><th>Lớp phủ nano</th><th>Sơn PU / Vecni / Dầu lau</th></tr></thead>
<tbody>
<tr><td>Cơ chế bảo vệ</td><td>Thẩm thấu sâu, không tạo màng bịt kín</td><td>Tạo lớp màng kín trên bề mặt</td></tr>
<tr><td>Khả năng thoát hơi ẩm</td><td>Hơi nước thoát được, bề mặt khô ráo</td><td>Bịt kín, ẩm tích tụ dưới màng</td></tr>
<tr><td>Kiểu hỏng đặc trưng</td><td>Mòn dần đều, không bong mảng</td><td>Phồng rộp, nứt, bong từng mảng</td></tr>
<tr><td>Tuổi thọ bảo vệ</td><td>Trên 10 năm</td><td>Ngắn, xuống cấp nhanh ngoài trời</td></tr>
<tr><td>Khi xuống cấp</td><td>Phủ chồng lớp mới, không cần bóc</td><td>Phải chà nhám bóc sạch lớp cũ</td></tr>
<tr><td>Ảnh hưởng tới vân gỗ</td><td>Giữ nguyên vân và bề mặt thật</td><td>Che phủ, làm mất bề mặt thật</td></tr>
<tr><td>Gỗ đã bạc màu / mục</td><td>Phục hồi được về trạng thái gần ban đầu</td><td>Chỉ che đi, gỗ vẫn mục bên dưới</td></tr>
<tr><td>Chi phí vòng đời</td><td>Thấp — ít chu kỳ bảo trì</td><td>Cao — chà nhám và sơn lại định kỳ</td></tr>
</tbody>
</table>

<h2>5. Ba câu hỏi khách hàng kỹ thuật hay hỏi</h2>
<p><strong>"Lớp phủ nano có làm đổi màu gỗ không?"</strong> Dòng trong suốt giữ nguyên vân và tông màu gốc, thường làm màu gỗ đậm và sâu hơn một chút giống hiệu ứng khi gỗ bị ẩm — đó là do chỉ số khúc xạ của lớp phủ trong lỗ rỗng. Muốn chủ động đổi màu thì dùng dòng có màu.</p>
<p><strong>"Đã sơn PU rồi thì có phủ nano lên được không?"</strong> Được, nếu lớp cũ còn bám chắc và đã được chà nhám tạo nhám, tẩy nhờn sạch. Nhưng lưu ý: khi đó lớp nano bảo vệ <em>lớp sơn cũ</em>, không bảo vệ gỗ, và khả năng thở của hệ vẫn bị giới hạn bởi màng PU bên dưới. Muốn có đầy đủ lợi ích thì phải bóc lớp cũ.</p>
<p><strong>"Có bảo vệ được chống mối không?"</strong> Tài liệu nguồn đề cập khả năng kháng vi sinh vật và ngăn mục rữa thông qua cơ chế giữ bề mặt khô ráo, nhưng không có dữ liệu thử nghiệm chống mối riêng. Không nên tuyên bố chống mối trên website — đây là rủi ro pháp lý về quảng cáo sai.</p>
HTML;

        $content .= $this->relatedLinks([
            ['href' => '/post/ho-so-thong-so-ky-thuat-tieu-chuan-kiem-dinh-lop-phu-nano-cho-go', 'label' => 'Hồ sơ thông số kỹ thuật & tiêu chuẩn kiểm định'],
            ['href' => '/post/go-ngoai-troi-vi-sao-moi-lop-son-deu-bong-va-giai-phap-nam-o-dau', 'label' => 'Giải pháp phủ gỗ ngoài trời: kháng UV và để gỗ thở được'],
            ['href' => '/applications/vat-lieu-go', 'label' => 'Trang trụ: Vật liệu gỗ'],
        ]);

        $contentEn = <<<'HTML'
<p><strong>Audience:</strong> materials engineers, workshop managers, applicators and technical distributors.</p>
<p><em>Editorial note: replace the <code>[BRAND]</code>, <code>[PRODUCT NAME]</code>, <code>[CLEAR LINE]</code>, <code>[TINTED LINE]</code> placeholders before publishing. Every figure must be checked against the TDS/MSDS of the batch you actually distribute.</em></p>

<h2>Summary for the busy reader</h2>
<p>PU paint, varnish and oil finishes all fail on outdoor wood the same way: they form a sealed film on the surface. The wood underneath keeps absorbing and releasing moisture with the seasons, keeps expanding and contracting — the film can't keep up, so it cracks, moisture gets trapped, and the wood rots from inside the paint layer while the surface still looks fine.</p>
<p>Nano coating solves the problem at a different level: instead of sitting on top, it penetrates and cures into a high-density cross-linked network right inside the wood's porous structure. The result is a surface that blocks liquid water and UV while still letting water vapour pass through — what the industry calls a "breathable" surface.</p>

<h2>1. How outdoor wood actually fails</h2>
<p>Wood is an organic material with two parts: the fibre (cellulose and hemicellulose forming the skeleton) and the resin/lignin (the binder between fibres). Outdoors, three degradation mechanisms run in parallel.</p>

<h3>1.1. UV photodegradation of lignin</h3>
<p>Lignin is the strongest UV absorber in wood. UV photons carry enough energy to break chemical bonds in the lignin molecule, creating free radicals. The degraded lignin turns into water-soluble fragments that rain washes away, exposing the bare grey cellulose underneath.</p>
<p>That's the "greying" everyone sees on a deck after one sunny season. It isn't dirt — it's lost material. Once the lignin is gone, the cellulose fibres lose their binder and start fraying.</p>

<h3>1.2. Swelling and shrinking cycles</h3>
<p>Wood constantly seeks moisture equilibrium with the surrounding air. On hot days the surface heats up and loses moisture faster than the core, causing uneven expansion through the thickness. At night and in rain, the process reverses.</p>
<p>Urban and industrial rainwater isn't neutral — it carries dissolved CO₂, SOₓ, NOₓ that form weak acids attacking the wood structure. After a few hundred cycles, accumulated stress exceeds the threshold and the surface develops fine cracks. On painted wood, these cracks are exactly where the film starts to lift.</p>

<h3>1.3. Micro-organisms</h3>
<p>Fungi, mould, algae and bacteria feed on wood. Their metabolism releases sulphuric and nitric acid, breaking down the fibre. The one precondition they need is standing moisture on the surface — which is exactly why vapour permeability matters as much as water resistance.</p>

<h2>2. Why a sealed film is the wrong answer</h2>
<p>A typical two-component PU builds a 60–100 µm film, nearly impermeable to water vapour. That sounds good until you consider what happens next.</p>
<p>Indoor wood equilibrates around 8–12% moisture content; outdoor wood in Vietnam swings 14–20% by season. That moisture has to go somewhere. When the top surface is sealed, vapour migrates sideways and accumulates right under the paint film. Vapour pressure builds, forming bubbles, then blisters, then peeling sheets.</p>
<p>Worse: moist wood trapped under a sealed film is the ideal environment for rot fungi. The surface still looks glossy while the wood underneath is already spongy. By the time it's discovered, the fix is no longer repainting — it's replacing the board.</p>
<p>The film's maintenance cycle is also expensive: repainting means sanding the old layer completely off, and every sanding pass removes real wood material. After three or four cycles, the board is visibly thinner, screw heads are exposed, and the grain is gone.</p>

<h2>3. How nano coating actually works</h2>
<h3>3.1. Composition — and a common misconception</h3>
<p>Two different technology families are both marketed as "nano," and it's worth telling them apart when advising customers:</p>
<ul>
<li>A one-component (1K) nano-structured Polyurethane/Polyurea hybrid resin system. Important point: this line does <em>not</em> contain solid nanoparticles as many people assume. "Nano" here describes a network structure at the nanometre scale, not filler particles. Performance comes from an extremely high cross-link density between polymer molecules.</li>
<li>A water-based, silicone-free fluoride-technology system. Penetrating, non-film-forming, geared toward water resistance and vapour permeability.</li>
</ul>
<p>When a customer asks "does it contain nanoparticles," the honest answer for the first family is: no — and that's an advantage. Solid particles dispersed in a film are typically where cracking and delamination start.</p>

<h3>3.2. Penetration and mechanical anchoring</h3>
<p>The active molecules are sized around 10⁻⁹ m — orders of magnitude smaller than the capillaries and pores on the wood surface. That lets them travel into the wood fibre instead of sitting on top. Once inside, the curing reaction builds a three-dimensional network mechanically anchored into the pore structure itself.</p>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th></th><th>Conventional coating</th><th>Nano-structured coating</th></tr></thead>
<tbody>
<tr><td>Molecular geometry</td><td>Linear chains</td><td>Three-dimensional cross-linked network</td></tr>
<tr><td>Cross-link density</td><td>Low</td><td>2.17 × 10³ mol/m³</td></tr>
<tr><td>Protective layer location</td><td>On the surface</td><td>Inside the pores and on the surface</td></tr>
<tr><td>Adhesion mechanism</td><td>Surface adhesion</td><td>Mechanical anchoring into the wood structure</td></tr>
</tbody>
</table>
<p>Cross-link density drives almost every other property: hardness, chemical resistance, abrasion resistance, and weathering durability. The denser the network, the fewer paths for foreign molecules to enter and the harder it is for UV energy to break it apart.</p>

<h3>3.3. UV resistance</h3>
<p>The UV-resistance mechanism here is a barrier mechanism, not simple chemical absorption. The dense cross-linked network acts as a durable barrier that absorbs and dissipates UV-A/UV-B energy before it reaches the lignin underneath.</p>
<p>The difference from additive UV absorbers in ordinary paint: additive absorbers deplete over exposure time and migrate out of the film. A structural barrier doesn't deplete — it <em>is</em> the coating's own framework.</p>
<p><strong>Verified data:</strong> accelerated weathering per SASO ISO 16474-2 over 5,000 hours shows colour and gloss change under 2%.</p>

<h3>3.4. Breathability</h3>
<p>This is the easiest selling point to state incorrectly, so precision matters. A nano-coated surface blocks liquid water but lets water vapour through. The physical mechanism: water vapour molecules are far smaller than a liquid droplet, which is held back by surface tension on a hydrophobic surface. A penetrating coating that doesn't form a continuous film still leaves an escape path for vapour molecules.</p>
<p>Practical consequences:</p>
<ul>
<li>Moisture in the wood can escape → no vapour pressure buildup → no blistering, no peeling.</li>
<li>The surface stays dry → mould and algae have no foothold.</li>
<li>Wood still expands and contracts naturally, with the coating moving with it, because the coating lives inside the wood rather than as a film glued on top.</li>
</ul>
<p><em>Note for marketing copy:</em> saying "the wood breathes" is fine in conversation, but technical documentation should read "maintains the surface's water-vapour permeability." The source technical file does not provide a specific WVTR value (g/m²·24h) — if you need that figure for a tender submission, request test results per ASTM E96 or ISO 7783 from the manufacturer.</p>

<h2>4. Comparison table for sales conversations</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Criterion</th><th>Nano coating</th><th>PU paint / Varnish / Oil finish</th></tr></thead>
<tbody>
<tr><td>Protection mechanism</td><td>Deep penetration, no sealed film</td><td>Forms a sealed surface film</td></tr>
<tr><td>Moisture-vapour escape</td><td>Vapour escapes, surface stays dry</td><td>Sealed, moisture accumulates under the film</td></tr>
<tr><td>Typical failure mode</td><td>Gradual, even wear, no sheet peeling</td><td>Blistering, cracking, peeling in sheets</td></tr>
<tr><td>Protective lifespan</td><td>Over 10 years</td><td>Short, degrades quickly outdoors</td></tr>
<tr><td>When it degrades</td><td>Recoat over the top, no stripping needed</td><td>Must sand off the old layer completely</td></tr>
<tr><td>Effect on wood grain</td><td>Keeps the real grain and surface</td><td>Covers and hides the real surface</td></tr>
<tr><td>Greyed / rotted wood</td><td>Can be restored close to original condition</td><td>Only covers it up — the wood keeps rotting underneath</td></tr>
<tr><td>Life-cycle cost</td><td>Low — few maintenance cycles</td><td>High — periodic sanding and repainting</td></tr>
</tbody>
</table>

<h2>5. Three questions technical customers ask</h2>
<p><strong>"Does nano coating change the wood colour?"</strong> The clear line keeps the original grain and tone, usually deepening the colour slightly the way wetting wood does — that's the coating's refractive index inside the pores. Use the tinted line if you want to actively change colour.</p>
<p><strong>"Can I apply nano coating over existing PU paint?"</strong> Yes, if the old layer is still firmly bonded and has been sanded for tooth and degreased. But note: the nano layer then protects <em>the old paint</em>, not the wood, and the system's breathability is still limited by the PU film underneath. For the full benefit, strip the old layer first.</p>
<p><strong>"Does it protect against termites?"</strong> The source documentation covers microbial resistance and decay prevention through keeping the surface dry, but there's no dedicated termite-resistance test data. Don't claim termite protection on the website — that's a real false-advertising legal risk, not a theoretical one.</p>
HTML;

        $contentEn .= $this->relatedLinksEn([
            ['href' => '/post/technical-specifications-nano-coating-for-wood', 'label' => 'Technical Specifications & Certification Standards'],
            ['href' => '/post/outdoor-wood-coating-solution-uv-resistant', 'label' => 'Outdoor Wood Coating: UV Resistance While Letting Wood Breathe'],
            ['href' => '/applications/wood-materials', 'label' => 'Pillar page: Wood Materials'],
        ]);

        return [
            'group' => 'ky-thuat',
            'slug' => 'vi-sao-lop-phu-nano-bao-ve-duoc-go-ma-son-tao-mang-thi-khong',
            'title' => 'Vì sao lớp phủ nano bảo vệ được gỗ mà sơn tạo màng thì không',
            'excerpt' => 'Sơn PU, vecni và dầu lau tạo màng kín và nhốt ẩm trong gỗ. Lớp phủ nano thẩm thấu vào thớ gỗ, kháng UV và vẫn cho gỗ thở được.',
            'meta_title' => 'Cơ chế lớp phủ nano bảo vệ gỗ',
            'meta_description' => 'Vì sao sơn tạo màng làm gỗ mục nhanh hơn, và lớp phủ nano thẩm thấu giải quyết vấn đề ở tầng khác. Cơ chế, số liệu kiểm định và so sánh chi tiết.',
            'content' => $content,
            'slug_en' => 'how-nano-coating-protects-wood-not-film-forming-paint',
            'title_en' => 'Why Nano Coating Protects Wood While Film-Forming Paint Does Not',
            'excerpt_en' => 'PU paint, varnish and oil finishes form a sealed film that traps moisture in wood. Nano coating penetrates the wood fibre, resists UV, and still lets the wood breathe.',
            'meta_title_en' => 'How Nano Coating Protects Wood',
            'meta_description_en' => 'Why film-forming paint makes wood rot faster, and how penetrating nano coating solves the problem differently. Mechanism, test data and detailed comparison.',
            'content_en' => $contentEn,
        ];
    }

    protected function kt02(): array
    {
        $content = <<<'HTML'
<p>Bài này dùng làm tài sản bán hàng B2B: chủ đầu tư, tư vấn giám sát và bộ phận mua hàng đều cần một trang duy nhất tổng hợp số liệu và tiêu chuẩn tham chiếu.</p>

<h2>1. Bảng thông số hiệu năng</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Chỉ tiêu</th><th>Phương pháp thử</th><th>Kết quả</th><th>Ý nghĩa thực tế</th></tr></thead>
<tbody>
<tr><td>Độ bóng ở góc 60°</td><td>ASTM D523 / SASO 2833</td><td>92,0 GU</td><td>Bóng cao, phản ánh độ dày màng đạt yêu cầu</td></tr>
<tr><td>Độ cứng bút chì</td><td>ASTM D3363 / SASO ISO 15184</td><td>4H (tới 7H với phụ gia mờ)</td><td>Kháng xước từ cát, ghế kéo, vali</td></tr>
<tr><td>Mài mòn Taber</td><td>ASTM D4060 (1 kg, 1.000 vòng)</td><td>Hao hụt 8,4 – 19,85 mg</td><td>Chịu được lối đi bộ mật độ cao</td></tr>
<tr><td>Độ bền va đập</td><td>ASTM D2794</td><td>&gt; 140 kg·cm</td><td>Không nứt khi vật rơi</td></tr>
<tr><td>Mật độ liên kết chéo (XLD)</td><td>DMA</td><td>2,17 × 10³ mol/m³</td><td>Nền tảng của mọi tính năng còn lại</td></tr>
<tr><td>Nhiệt độ chuyển thuỷ tinh (Tg)</td><td>DMA</td><td>57,7 – 96,1 °C</td><td>Không mềm dưới nắng trực xạ</td></tr>
<tr><td>Dải nhiệt độ làm việc</td><td>Chu trình DMA</td><td>−50 °C đến 200 °C</td><td>Dư sức cho khí hậu Việt Nam</td></tr>
<tr><td>Tính dễ cháy</td><td>ASTM E84 / BS 476</td><td>Class A / Hạng 1</td><td>Đủ điều kiện cho công trình công cộng</td></tr>
<tr><td>Ngâm nước</td><td>ISO 2812-2 (240 h @ 50 °C)</td><td>Đạt, không đổi màu</td><td>Chịu được đọng nước kéo dài</td></tr>
<tr><td>Kháng hoá chất</td><td>ASTM D4752 (lau MEK)</td><td>&gt; 1.500 lần lau</td><td>Chịu hoá chất tẩy rửa công nghiệp</td></tr>
<tr><td>Gia tốc thời tiết QUV</td><td>ASTM D4587 (1.500 h)</td><td>Giữ 99–100% độ bóng</td><td>Tương đương nhiều năm phơi ngoài trời</td></tr>
<tr><td>Gia tốc thời tiết Xenon</td><td>ASTM G155 (4.000 h)</td><td>Giữ 99–100% độ bóng; ΔE = 0,63</td><td>ΔE &lt; 1 nghĩa là mắt thường không thấy đổi màu</td></tr>
<tr><td>Gia tốc thời tiết tổng hợp</td><td>SASO ISO 16474-2 (5.000 h)</td><td>Đổi màu và độ bóng &lt; 2%</td><td>Mức đánh giá Xuất sắc</td></tr>
<tr><td>Phun muối</td><td>ASTM B117 / SASO ISO 11997</td><td>4.000 – 5.000 h, không phồng rộp</td><td>Dùng được ở công trình ven biển</td></tr>
<tr><td>Độ bám dính — cắt ô</td><td>ISO 2409 / ASTM D3359</td><td>Bậc 0 / 5B (100%)</td><td>Không bong khi bóc băng keo</td></tr>
<tr><td>Độ bám dính — kéo giật</td><td>—</td><td>9 MPa</td><td>Mức Xuất sắc</td></tr>
<tr><td>VOC</td><td>—</td><td>156 g/L</td><td>Thấp, đáp ứng yêu cầu công trình xanh</td></tr>
<tr><td>Khả năng phân huỷ sinh học</td><td>—</td><td>&gt; 95%</td><td>Hồ sơ môi trường cho hàng xuất khẩu</td></tr>
</tbody>
</table>
<p><strong>Điểm cần làm rõ với nhà sản xuất trước khi xuất bản:</strong> bộ tài liệu nguồn xuất hiện hai giá trị góc tiếp xúc nước khác nhau — một chỗ ghi khoảng 70°, một chỗ ghi &gt; 100°. Hai con số này mô tả hai trạng thái bề mặt khác nhau (70° là kỵ nước nhẹ, &gt; 100° mới là kỵ nước thật sự với hiệu ứng lá sen). Không đưa số nào lên website cho tới khi xác nhận được giá trị nào ứng với dòng sản phẩm nào.</p>

<h2>2. Độ dày màng và định mức</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Thông số</th><th>Giá trị</th></tr></thead>
<tbody>
<tr><td>Độ dày màng ướt mỗi lớp</td><td>2 – 3 mil (51 – 76 µm)</td></tr>
<tr><td>Tổng độ dày màng khô (DFT)</td><td>1,5 – 2,5 mil (38 – 63 µm)</td></tr>
<tr><td>Định mức lý thuyết</td><td>31 m² / 3,8 lít ở DFT 2,00 mil ≈ 8,1 m²/lít</td></tr>
</tbody>
</table>
<p>Định mức trên là định mức lý thuyết trên bề mặt đã kín. Trên gỗ mộc chưa xử lý, lớp đầu tiên bị hút vào lỗ rỗng nên hao hụt thực tế cao hơn đáng kể — khi lập dự toán nên cộng thêm 20–35% tuỳ độ xốp của loại gỗ, và luôn chạy một tấm mẫu để đo hao hụt thật.</p>

<h2>3. Đóng gói, bảo quản, hạn dùng</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Quy cách</th><th>Dung tích</th><th>Khối lượng</th></tr></thead>
<tbody>
<tr><td>Gallon</td><td>3,8 lít</td><td>3,63 kg</td></tr>
<tr><td>Pail (thùng)</td><td>19 lít</td><td>18,14 kg</td></tr>
<tr><td>Drum (phuy)</td><td>208 lít</td><td>~198 kg</td></tr>
</tbody>
</table>
<p>Sản phẩm gốc isocyanate rất nhạy với ẩm. Hạn dùng phụ thuộc mạnh vào nhiệt độ kho:</p>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Điều kiện</th><th>Chưa mở nắp</th><th>Đã mở nắp</th></tr></thead>
<tbody>
<tr><td>4 – 22 °C</td><td>12 tháng</td><td>—</td></tr>
<tr><td>27 °C</td><td>6 tháng</td><td>Tối đa 2 tháng</td></tr>
<tr><td>38 °C</td><td>2 tháng</td><td>—</td></tr>
<tr><td>Vận chuyển</td><td>4 – 30 °C, thời gian ngắn</td><td>—</td></tr>
</tbody>
</table>
<p>Ba nguyên tắc kho vận bắt buộc:</p>
<ul>
<li>Đóng chặt nắp ngay sau khi rót. Nắp mở lâu là nguyên nhân số một gây đóng gel trong thùng.</li>
<li>Không chiết sang thùng nhỏ nếu không có khả năng đắp khí nitơ lên khoảng trống.</li>
<li>Ở kho không điều hoà tại miền Nam và miền Trung, nhiệt độ mùa hè dễ vượt 35 °C — thực tế hạn dùng rút xuống còn 2–3 tháng. Cần quản lý tồn kho theo FIFO nghiêm ngặt.</li>
</ul>

<h2>4. Tiêu chuẩn hệ thống và chứng nhận</h2>
<ul>
<li>ISO 9001:2015 — hệ thống quản lý chất lượng.</li>
<li>ISO 45001:2018 — hệ thống quản lý an toàn và sức khoẻ nghề nghiệp.</li>
<li>ASTM E84 Class A — phân hạng lan truyền lửa bề mặt, điều kiện cần cho công trình công cộng.</li>
<li>VOC 156 g/L, không chứa silicone, phân huỷ sinh học &gt; 95% — hồ sơ môi trường phục vụ khách hàng chế biến gỗ xuất khẩu sang EU và Bắc Mỹ.</li>
</ul>

<h2>5. Bảo hành</h2>
<p>Chính sách bảo hành ghi nhận trong tài liệu là 05 – 10 năm, trong khi hiệu quả bảo vệ thực tế được báo cáo là trên 10 năm.</p>
<p>Khi truyền thông, giữ hai con số này tách bạch: bảo hành là cam kết pháp lý, tuổi thọ là kết quả quan sát. Gộp chung sẽ tạo kỳ vọng sai và rủi ro tranh chấp.</p>
HTML;

        $content .= $this->relatedLinks([
            ['href' => '/post/quy-trinh-thi-cong-chuan-tu-chuan-bi-be-mat-den-nghiem-thu-qc', 'label' => 'Quy trình thi công chuẩn: từ chuẩn bị bề mặt đến nghiệm thu QC'],
            ['href' => '/post/thuong-hieu-cho-vat-lieu-go-bao-ve-tu-ben-trong-tho-go', 'label' => 'Giới thiệu dòng sản phẩm nano cho vật liệu gỗ'],
            ['href' => '/document/catalog', 'label' => 'Catalog & tài liệu kỹ thuật'],
        ]);

        $contentEn = <<<'HTML'
<p>This article doubles as a B2B sales asset: developers, supervising consultants and procurement teams all need one page consolidating the data and reference standards.</p>

<h2>1. Performance specification table</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Property</th><th>Test method</th><th>Result</th><th>Practical meaning</th></tr></thead>
<tbody>
<tr><td>Gloss at 60°</td><td>ASTM D523 / SASO 2833</td><td>92.0 GU</td><td>High gloss, reflects adequate film build</td></tr>
<tr><td>Pencil hardness</td><td>ASTM D3363 / SASO ISO 15184</td><td>4H (up to 7H with matting additive)</td><td>Scratch resistance against sand, chairs, luggage</td></tr>
<tr><td>Taber abrasion</td><td>ASTM D4060 (1 kg, 1,000 cycles)</td><td>8.4 – 19.85 mg loss</td><td>Withstands high foot-traffic areas</td></tr>
<tr><td>Impact resistance</td><td>ASTM D2794</td><td>&gt; 140 kg·cm</td><td>No cracking on impact</td></tr>
<tr><td>Cross-link density (XLD)</td><td>DMA</td><td>2.17 × 10³ mol/m³</td><td>Foundation of every other property</td></tr>
<tr><td>Glass transition temperature (Tg)</td><td>DMA</td><td>57.7 – 96.1 °C</td><td>Doesn't soften under direct sun</td></tr>
<tr><td>Working temperature range</td><td>DMA cycle</td><td>−50 °C to 200 °C</td><td>Ample margin for Vietnam's climate</td></tr>
<tr><td>Flammability</td><td>ASTM E84 / BS 476</td><td>Class A / Class 1</td><td>Meets public-building requirements</td></tr>
<tr><td>Water immersion</td><td>ISO 2812-2 (240 h @ 50 °C)</td><td>Pass, no colour change</td><td>Withstands prolonged standing water</td></tr>
<tr><td>Chemical resistance</td><td>ASTM D4752 (MEK rub)</td><td>&gt; 1,500 double rubs</td><td>Resists industrial cleaning chemicals</td></tr>
<tr><td>QUV accelerated weathering</td><td>ASTM D4587 (1,500 h)</td><td>Retains 99–100% gloss</td><td>Equivalent to years of outdoor exposure</td></tr>
<tr><td>Xenon accelerated weathering</td><td>ASTM G155 (4,000 h)</td><td>Retains gloss; ΔE = 0.63</td><td>ΔE &lt; 1 means the naked eye can't detect colour change</td></tr>
<tr><td>Combined accelerated weathering</td><td>SASO ISO 16474-2 (5,000 h)</td><td>Colour and gloss change &lt; 2%</td><td>Rated Excellent</td></tr>
<tr><td>Salt spray</td><td>ASTM B117 / SASO ISO 11997</td><td>4,000 – 5,000 h, no blistering</td><td>Suitable for coastal projects</td></tr>
<tr><td>Adhesion — cross-cut</td><td>ISO 2409 / ASTM D3359</td><td>Grade 0 / 5B (100%)</td><td>No lifting on tape-pull test</td></tr>
<tr><td>Adhesion — pull-off</td><td>—</td><td>9 MPa</td><td>Excellent rating</td></tr>
<tr><td>VOC</td><td>—</td><td>156 g/L</td><td>Low, meets green-building requirements</td></tr>
<tr><td>Biodegradability</td><td>—</td><td>&gt; 95%</td><td>Environmental profile for export customers</td></tr>
</tbody>
</table>
<p><strong>To confirm with the manufacturer before publishing:</strong> the source documentation shows two different water contact-angle values — one around 70°, another &gt; 100°. These describe two different surface states (70° is mild hydrophobicity, &gt; 100° is true hydrophobic lotus-effect). Don't publish either figure until you've confirmed which value applies to which product line.</p>

<h2>2. Film build and coverage rate</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Parameter</th><th>Value</th></tr></thead>
<tbody>
<tr><td>Wet film thickness per coat</td><td>2 – 3 mil (51 – 76 µm)</td></tr>
<tr><td>Total dry film thickness (DFT)</td><td>1.5 – 2.5 mil (38 – 63 µm)</td></tr>
<tr><td>Theoretical coverage</td><td>31 m² / 3.8 L at 2.00 mil DFT ≈ 8.1 m²/L</td></tr>
</tbody>
</table>
<p>The figure above is theoretical coverage on an already-sealed surface. On bare, untreated wood the first coat is absorbed into the pores, so real-world consumption is significantly higher — add 20–35% depending on how porous the wood species is when estimating, and always run a sample panel to measure actual consumption.</p>

<h2>3. Packaging, storage, shelf life</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Packaging</th><th>Volume</th><th>Weight</th></tr></thead>
<tbody>
<tr><td>Gallon</td><td>3.8 L</td><td>3.63 kg</td></tr>
<tr><td>Pail</td><td>19 L</td><td>18.14 kg</td></tr>
<tr><td>Drum</td><td>208 L</td><td>~198 kg</td></tr>
</tbody>
</table>
<p>Isocyanate-based product is highly moisture-sensitive. Shelf life depends heavily on storage temperature:</p>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Condition</th><th>Unopened</th><th>Opened</th></tr></thead>
<tbody>
<tr><td>4 – 22 °C</td><td>12 months</td><td>—</td></tr>
<tr><td>27 °C</td><td>6 months</td><td>Up to 2 months</td></tr>
<tr><td>38 °C</td><td>2 months</td><td>—</td></tr>
<tr><td>Transport</td><td>4 – 30 °C, short duration</td><td>—</td></tr>
</tbody>
</table>
<p>Three mandatory storage rules:</p>
<ul>
<li>Reseal the lid tightly immediately after pouring. A lid left open is the number-one cause of gelling in the container.</li>
<li>Don't decant into smaller containers unless you can blanket the headspace with nitrogen.</li>
<li>In non-air-conditioned warehouses across southern and central Vietnam, summer temperatures easily exceed 35 °C — real shelf life drops to 2–3 months. Manage stock strictly on a FIFO basis.</li>
</ul>

<h2>4. System standards and certifications</h2>
<ul>
<li>ISO 9001:2015 — quality management system.</li>
<li>ISO 45001:2018 — occupational health and safety management system.</li>
<li>ASTM E84 Class A — surface flame-spread rating, a requirement for public buildings.</li>
<li>VOC 156 g/L, silicone-free, &gt; 95% biodegradable — the environmental profile export-oriented wood processors need for shipments to the EU and North America.</li>
</ul>

<h2>5. Warranty</h2>
<p>The documented warranty policy is 5–10 years, while the field-reported protective lifespan is over 10 years.</p>
<p>Keep these two numbers distinct when communicating: the warranty is a legal commitment, the lifespan is an observed result. Conflating them creates the wrong expectation and dispute risk.</p>
HTML;

        $contentEn .= $this->relatedLinksEn([
            ['href' => '/post/application-process-nano-coating-on-wood', 'label' => 'Standard Application Process: From Surface Prep to QC Sign-off'],
            ['href' => '/post/nano-coating-product-line-for-wood', 'label' => 'Nano Coating Product Line for Wood'],
            ['href' => '/document/catalog', 'label' => 'Catalog & Technical Documents'],
        ]);

        return [
            'group' => 'ky-thuat',
            'slug' => 'ho-so-thong-so-ky-thuat-tieu-chuan-kiem-dinh-lop-phu-nano-cho-go',
            'title' => 'Hồ sơ thông số kỹ thuật & tiêu chuẩn kiểm định lớp phủ nano cho gỗ',
            'excerpt' => 'Bảng đầy đủ thông số hiệu năng, độ dày màng, định mức, bảo quản và tiêu chuẩn chứng nhận — dùng làm tài liệu bán hàng B2B.',
            'meta_title' => 'Thông số kỹ thuật sơn nano gỗ',
            'meta_description' => 'TDS đầy đủ: độ bóng, độ cứng, mài mòn Taber, kháng UV, kháng muối, VOC, định mức thi công và tiêu chuẩn ASTM/ISO cho lớp phủ nano gỗ.',
            'content' => $content,
            'slug_en' => 'technical-specifications-nano-coating-for-wood',
            'title_en' => 'Technical Specifications & Certification Standards for Nano Wood Coating',
            'excerpt_en' => 'Full table of performance specs, film build, coverage rate, storage and certification standards — a ready-to-use B2B sales asset.',
            'meta_title_en' => 'Technical Specifications for Nano Wood Coating',
            'meta_description_en' => 'Full TDS: gloss, hardness, Taber abrasion, UV resistance, salt resistance, VOC, application coverage and ASTM/ISO standards for nano wood coating.',
            'content_en' => $contentEn,
        ];
    }

    protected function kt03(): array
    {
        $content = <<<'HTML'
<p>Trong lớp phủ hiệu năng cao, hơn 80% sự cố hiện trường không đến từ vật liệu mà đến từ chuẩn bị bề mặt và điều kiện môi trường. Quy trình dưới đây viết cho đội thi công thực hiện trực tiếp.</p>

<h2>Bước 1 — Chuẩn bị bề mặt</h2>
<p><strong>Gỗ đã sơn hoặc đã oxy hoá:</strong> chà nhám bằng máy chà nhám quỹ đạo với giấy nhám 400 grit. Mục tiêu không phải bóc sạch mà là tạo profile nhám cho lớp phủ neo vào, đồng thời loại bỏ lớp gỗ bạc màu đã mất lignin.</p>
<p><strong>Gỗ mộc mới:</strong> chà nhám theo trình tự tăng dần, dừng ở 320–400 grit. Chà mịn hơn mức này sẽ làm bịt lỗ rỗng và giảm khả năng thẩm thấu — đây là lỗi phổ biến ở thợ quen làm nội thất cao cấp.</p>
<p><strong>Làm sạch:</strong></p>
<ul>
<li>Tẩy nhờn toàn bộ bề mặt bằng chất tẩy nhờn chuyên dụng.</li>
<li>Rửa lại và lau khô hoàn toàn.</li>
<li>Với vết dầu, mỡ, silicone hoặc sáp cứng đầu: dùng acetone hoặc MEK để loại bỏ.</li>
</ul>
<p>Silicone là kẻ thù số một. Xưởng nào từng dùng sáp đánh bóng hoặc chất chống dính có silicone thì dư lượng còn lại sẽ gây lỗi mắt cá và mất bám dính cục bộ. Nếu nghi ngờ, tẩy nhờn hai lượt và chạy thử một tấm mẫu trước.</p>
<p><strong>Gỗ cũ bạc màu, mục hoặc mốc:</strong> công nghệ nano có khả năng xử lý và phục hồi gỗ đã lão hoá, bạc màu hoặc mục, đưa về gần trạng thái ban đầu mà không làm thay đổi tính chất vật liệu. Với hạng mục di tích và đồ gỗ quý, luôn thử nghiệm trên một vùng khuất trước khi làm diện rộng.</p>

<h2>Bước 2 — Điều kiện môi trường</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Thông số</th><th>Ngưỡng cho phép</th></tr></thead>
<tbody>
<tr><td>Nhiệt độ môi trường</td><td>4 °C – 32 °C (40 – 90 °F)</td></tr>
<tr><td>Nhiệt độ bề mặt</td><td>Cao hơn điểm sương tối thiểu 3 °C</td></tr>
<tr><td>Độ ẩm không khí</td><td>Xem lưu ý bên dưới</td></tr>
<tr><td>Độ ẩm gỗ</td><td>Bề mặt phải khô, không còn dung môi tồn dư</td></tr>
</tbody>
</table>
<p>Về độ ẩm không khí, cần hiểu đúng cơ chế: độ ẩm cao làm quá trình đóng rắn diễn ra nhanh hơn (vì hệ 1K đóng rắn nhờ hơi ẩm), còn độ ẩm thấp giúp lớp phủ dàn trải và cân bằng bề mặt tốt hơn. Không có ngưỡng "tốt nhất" tuyệt đối — có đánh đổi. Ở miền Bắc mùa nồm với độ ẩm trên 85%, phải rút ngắn thời gian thao tác và chấp nhận bề mặt kém phẳng hơn.</p>
<p>Bộ tài liệu nguồn không đưa ra con số phần trăm cụ thể cho độ ẩm gỗ. Theo thông lệ ngành cho gỗ ngoài trời tại Việt Nam, nên kiểm tra bằng máy đo độ ẩm gỗ và không thi công khi vượt 18%. Cần xác nhận ngưỡng chính thức với nhà sản xuất trước khi đưa vào quy trình nội bộ.</p>

<h2>Bước 3 — Thiết bị</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Thiết bị</th><th>Thông số</th></tr></thead>
<tbody>
<tr><td>Súng phun HVLP / LVLP</td><td>Béc 1,3 – 1,4 – 1,5 mm; áp suất tại súng 29 – 30 PSI; phun chồng lấp 50%</td></tr>
<tr><td>Máy phun Airless</td><td>Đầu phun cỡ 417 / 517 / 617; áp suất bơm ~800 PSI; tỷ lệ bơm 30:1 hoặc 40:1</td></tr>
<tr><td>Thi công thủ công</td><td>Miếng đệm lau chuyên dụng (deck pad) cho sàn ngoài trời</td></tr>
</tbody>
</table>
<p>Vệ sinh thiết bị: làm sạch ngay sau khi dùng bằng acetone hoặc MEK. Tuyệt đối không dùng nước hoặc cồn — cả hai đều phản ứng với hệ và làm đóng rắn trong đường dẫn.</p>

<h2>Bước 4 — Thi công và thời gian khô</h2>
<p>Áp dụng 3 – 4 lớp ướt. Vai trò từng lớp: hai lớp đầu xây dựng màu và lấp đầy lỗ rỗng; hai lớp sau củng cố mật độ màng và bảo vệ lâu dài.</p>
<p>Thời gian chờ giữa các lớp (flash-off): 2 – 5 phút ở 22 °C, đủ để dung môi bay hơi. Không phủ thêm lớp sau mốc 20 phút — quá mốc này màng đã bắt đầu đóng rắn và lớp mới sẽ bám kém. Nếu lỡ quá 20 phút, phải chờ khô cứng hoàn toàn rồi chà nhám tạo nhám trước khi phủ tiếp.</p>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Mốc (ở 22 – 32 °C, độ ẩm 50%)</th><th>Thời gian</th></tr></thead>
<tbody>
<tr><td>Khô bề mặt (không bám bụi)</td><td>10 – 30 phút</td></tr>
<tr><td>Khô chạm</td><td>20 – 40 phút</td></tr>
<tr><td>Có thể xê dịch, lật trở</td><td>3 – 4 giờ</td></tr>
<tr><td>Khô cứng</td><td>24 giờ</td></tr>
<tr><td>Đóng rắn hoàn toàn</td><td>48 giờ</td></tr>
</tbody>
</table>
<p>Không đưa hạng mục vào sử dụng trước mốc 48 giờ. Với sàn deck, không kê đồ nặng và không cho xe đẩy chạy trong 7 ngày đầu.</p>

<h2>Bước 5 — Xử lý sự cố hiện trường</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Hiện tượng</th><th>Nguyên nhân</th><th>Xử lý</th></tr></thead>
<tbody>
<tr><td>Vệt sọc, điểm cao</td><td>Kỹ thuật lau không đều</td><td>Gạt phẳng ngay khi màng còn ướt</td></tr>
<tr><td>Bám dính kém, bong cục bộ</td><td>Còn dầu, mỡ hoặc silicone trên nền</td><td>Bóc lại vùng lỗi, tẩy nhờn kỹ, thi công lại</td></tr>
<tr><td>Độ bóng thấp bất thường</td><td>Độ dày màng khô chưa đủ</td><td>Phủ thêm một lớp ướt</td></tr>
<tr><td>Mắt cá</td><td>Nhiễm silicone</td><td>Chà nhám vùng lỗi, tẩy nhờn hai lượt</td></tr>
<tr><td>Sản phẩm đóng gel trong thùng</td><td>Nhiễm ẩm, nắp mở quá lâu</td><td>Không dùng được; phòng ngừa bằng cách đóng nắp ngay và không chiết thùng nhỏ</td></tr>
<tr><td>Lớp sau bong khỏi lớp trước</td><td>Phủ chồng sau mốc 20 phút</td><td>Chờ khô cứng, chà nhám tạo nhám, phủ lại</td></tr>
</tbody>
</table>

<h2>Bước 6 — Nghiệm thu QC</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Hạng mục kiểm tra</th><th>Phương pháp</th><th>Tiêu chí đạt</th></tr></thead>
<tbody>
<tr><td>Trực quan</td><td>Quan sát độ bóng và độ đồng đều</td><td>Bóng cao, đều, không sọc, không mắt cá</td></tr>
<tr><td>Độ dày màng khô</td><td>Máy đo DFT</td><td>1,5 – 2,5 mil (38 – 63 µm)</td></tr>
<tr><td>Độ cứng</td><td>Bút chì, ASTM D3363</td><td>Tối thiểu 4H</td></tr>
<tr><td>Độ bám dính</td><td>Cắt ô, ISO 2409 / ASTM D3359</td><td>Bậc 0 hoặc 5B (100%)</td></tr>
<tr><td>Kháng hoá chất</td><td>Lau MEK, ASTM D4752</td><td>&gt; 1.500 lần lau, bề mặt không đổi</td></tr>
</tbody>
</table>
<p>Thực hiện kiểm tra bám dính và độ cứng trên tấm mẫu chứng thi công cùng lô, cùng điều kiện — không cắt ô lên bề mặt công trình đã nghiệm thu.</p>

<h2>Bước 7 — An toàn lao động</h2>
<p>Bộ tài liệu nguồn yêu cầu tham khảo SDS của sản phẩm và sử dụng đầy đủ PPE. Mức tối thiểu cho thi công phun trong không gian kín:</p>
<ul>
<li>Mặt nạ phòng độc có phin lọc hơi hữu cơ — khẩu trang vải và khẩu trang y tế không có tác dụng với hơi dung môi và isocyanate.</li>
<li>Găng tay chống hoá chất, kính bảo hộ kín.</li>
<li>Quạt hút cưỡng bức, thông gió liên tục trong suốt thời gian phun và tối thiểu 30 phút sau khi kết thúc.</li>
<li>Acetone và MEK dễ cháy — không hàn, cắt, hút thuốc trong khu vực thi công và khu vực lân cận.</li>
</ul>
<p>Trước mỗi dự án, tải bản SDS mới nhất từ nhà sản xuất và phổ biến cho toàn đội. Đây là yêu cầu bắt buộc, không phải khuyến nghị.</p>
HTML;

        $content .= $this->relatedLinks([
            ['href' => '/post/ho-so-thong-so-ky-thuat-tieu-chuan-kiem-dinh-lop-phu-nano-cho-go', 'label' => 'Hồ sơ thông số kỹ thuật & tiêu chuẩn kiểm định'],
            ['href' => '/post/giai-phap-nano-theo-tung-hang-muc-go-ngoai-troi', 'label' => 'Giải pháp nano theo từng hạng mục gỗ ngoài trời'],
            ['href' => '/contact', 'label' => 'Đặt mẫu thử miễn phí'],
        ]);

        $contentEn = <<<'HTML'
<p>In high-performance coatings, over 80% of field failures come not from the material but from surface preparation and environmental conditions. The process below is written directly for the application crew.</p>

<h2>Step 1 — Surface preparation</h2>
<p><strong>Previously painted or oxidised wood:</strong> sand with an orbital sander using 400-grit paper. The goal isn't to strip everything but to create a profile the coating can key into, while removing the greyed, lignin-depleted layer.</p>
<p><strong>New bare wood:</strong> sand in increasing grit sequence, stopping at 320–400 grit. Sanding finer than this closes the pores and reduces penetration — a common mistake among finishers used to high-end interior work.</p>
<p><strong>Cleaning:</strong></p>
<ul>
<li>Degrease the entire surface with a dedicated degreaser.</li>
<li>Rinse and dry completely.</li>
<li>For oil, grease, silicone or stubborn wax residue: use acetone or MEK to remove it.</li>
</ul>
<p>Silicone is public enemy number one. Any workshop that has used polishing wax or silicone-based release agents will have residue that causes fisheyes and localised adhesion loss. If in doubt, degrease twice and run a test panel first.</p>
<p><strong>Old, greyed, rotted or mouldy wood:</strong> nano technology can treat and restore aged, greyed or rotted wood close to its original condition without altering the material's properties. For heritage items and fine furniture, always test on a hidden area before working the full surface.</p>

<h2>Step 2 — Environmental conditions</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Parameter</th><th>Allowable range</th></tr></thead>
<tbody>
<tr><td>Ambient temperature</td><td>4 °C – 32 °C (40 – 90 °F)</td></tr>
<tr><td>Surface temperature</td><td>At least 3 °C above dew point</td></tr>
<tr><td>Air humidity</td><td>See note below</td></tr>
<tr><td>Wood moisture</td><td>Surface must be dry, no residual solvent</td></tr>
</tbody>
</table>
<p>On air humidity, understand the mechanism correctly: higher humidity speeds up curing (the 1K system cures via moisture), while lower humidity helps the coating level and self-even better. There's no single "best" threshold — it's a trade-off. In northern Vietnam's humid "nồm" season above 85% humidity, shorten working time and accept a slightly less level finish.</p>
<p>The source documentation doesn't give a specific wood-moisture percentage. Industry practice for outdoor wood in Vietnam is to check with a moisture meter and avoid applying above 18%. Confirm the official threshold with the manufacturer before writing it into your internal process.</p>

<h2>Step 3 — Equipment</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Equipment</th><th>Settings</th></tr></thead>
<tbody>
<tr><td>HVLP / LVLP spray gun</td><td>1.3 – 1.4 – 1.5 mm tip; 29 – 30 PSI at the gun; 50% overlap</td></tr>
<tr><td>Airless sprayer</td><td>417 / 517 / 617 tip size; ~800 PSI pump pressure; 30:1 or 40:1 pump ratio</td></tr>
<tr><td>Manual application</td><td>Dedicated applicator pad for outdoor decking</td></tr>
</tbody>
</table>
<p>Equipment cleaning: clean immediately after use with acetone or MEK. Never use water or alcohol — both react with the system and cure inside the lines.</p>

<h2>Step 4 — Application and dry times</h2>
<p>Apply 3–4 wet coats. Role of each layer: the first two coats build colour and fill the pores; the last two build film density and long-term protection.</p>
<p>Flash-off time between coats: 2–5 minutes at 22 °C, enough for solvent to evaporate. Don't apply another coat past the 20-minute mark — beyond that the film has started curing and the new coat will bond poorly. If you miss the window, let it cure hard, then sand for tooth before recoating.</p>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Milestone (at 22 – 32 °C, 50% humidity)</th><th>Time</th></tr></thead>
<tbody>
<tr><td>Surface dry (dust-free)</td><td>10 – 30 minutes</td></tr>
<tr><td>Touch dry</td><td>20 – 40 minutes</td></tr>
<tr><td>Can be moved / turned</td><td>3 – 4 hours</td></tr>
<tr><td>Hard dry</td><td>24 hours</td></tr>
<tr><td>Full cure</td><td>48 hours</td></tr>
</tbody>
</table>
<p>Don't put the item into service before the 48-hour mark. For decking, no heavy furniture and no cart traffic for the first 7 days.</p>

<h2>Step 5 — Field troubleshooting</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Symptom</th><th>Cause</th><th>Fix</th></tr></thead>
<tbody>
<tr><td>Streaks, high spots</td><td>Uneven wiping technique</td><td>Level it out immediately while the film is still wet</td></tr>
<tr><td>Poor adhesion, local peeling</td><td>Residual oil, grease or silicone on the substrate</td><td>Strip the affected area, degrease thoroughly, reapply</td></tr>
<tr><td>Unusually low gloss</td><td>Insufficient dry film thickness</td><td>Apply one more wet coat</td></tr>
<tr><td>Fisheyes</td><td>Silicone contamination</td><td>Sand the affected area, degrease twice</td></tr>
<tr><td>Product gelled in the container</td><td>Moisture ingress, lid left open too long</td><td>Not usable; prevent by resealing immediately and not decanting into small containers</td></tr>
<tr><td>Later coat lifting off the earlier one</td><td>Recoated past the 20-minute window</td><td>Let it cure hard, sand for tooth, reapply</td></tr>
</tbody>
</table>

<h2>Step 6 — QC sign-off</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Check item</th><th>Method</th><th>Pass criteria</th></tr></thead>
<tbody>
<tr><td>Visual</td><td>Observe gloss and uniformity</td><td>High, even gloss, no streaks, no fisheyes</td></tr>
<tr><td>Dry film thickness</td><td>DFT gauge</td><td>1.5 – 2.5 mil (38 – 63 µm)</td></tr>
<tr><td>Hardness</td><td>Pencil, ASTM D3363</td><td>4H minimum</td></tr>
<tr><td>Adhesion</td><td>Cross-cut, ISO 2409 / ASTM D3359</td><td>Grade 0 or 5B (100%)</td></tr>
<tr><td>Chemical resistance</td><td>MEK rub, ASTM D4752</td><td>&gt; 1,500 double rubs, no change</td></tr>
</tbody>
</table>
<p>Run adhesion and hardness checks on a witness panel sprayed from the same batch under the same conditions — never cross-cut a completed, signed-off project surface.</p>

<h2>Step 7 — Worker safety</h2>
<p>The source documentation requires referring to the product SDS and using full PPE. Minimum requirements for spray application in an enclosed space:</p>
<ul>
<li>An organic-vapour cartridge respirator — cloth and medical masks have no effect against solvent vapour and isocyanate.</li>
<li>Chemical-resistant gloves, sealed safety goggles.</li>
<li>Forced-air ventilation, running continuously throughout spraying and for at least 30 minutes after.</li>
<li>Acetone and MEK are flammable — no welding, cutting or smoking in or near the work area.</li>
</ul>
<p>Before every project, download the latest SDS from the manufacturer and brief the whole crew. This is mandatory, not a suggestion.</p>
HTML;

        $contentEn .= $this->relatedLinksEn([
            ['href' => '/post/technical-specifications-nano-coating-for-wood', 'label' => 'Technical Specifications & Certification Standards'],
            ['href' => '/post/nano-solutions-by-outdoor-wood-category', 'label' => 'Nano Solutions by Outdoor Wood Category'],
            ['href' => '/contact', 'label' => 'Request a Free Sample'],
        ]);

        return [
            'group' => 'ky-thuat',
            'slug' => 'quy-trinh-thi-cong-chuan-tu-chuan-bi-be-mat-den-nghiem-thu-qc',
            'title' => 'Quy trình thi công chuẩn: từ chuẩn bị bề mặt đến nghiệm thu QC',
            'excerpt' => '7 bước thi công chuẩn cho đội thợ: chuẩn bị bề mặt, điều kiện môi trường, thiết bị, thời gian khô, xử lý sự cố và nghiệm thu QC.',
            'meta_title' => 'Quy trình thi công sơn nano trên gỗ',
            'meta_description' => 'Quy trình 7 bước cho thợ thi công: chuẩn bị bề mặt, định mức sơn nano, thi công HVLP/Airless, thời gian khô, xử lý lỗi và nghiệm thu QC.',
            'content' => $content,
            'slug_en' => 'application-process-nano-coating-on-wood',
            'title_en' => 'Standard Application Process: From Surface Prep to QC Sign-off',
            'excerpt_en' => '7 standard application steps for the crew: surface prep, environmental conditions, equipment, dry times, troubleshooting and QC sign-off.',
            'meta_title_en' => 'Nano Wood Coating Application Process',
            'meta_description_en' => '7-step process for applicators: surface prep, coverage rate, HVLP/Airless application, dry times, defect handling and QC sign-off.',
            'content_en' => $contentEn,
        ];
    }

    protected function sp01(): array
    {
        $content = <<<'HTML'
<p><em>Ghi chú biên tập: thay <code>[THƯƠNG HIỆU]</code>, <code>[TÊN SẢN PHẨM]</code>, <code>[DÒNG TRONG SUỐT]</code>, <code>[DÒNG CÓ MÀU]</code>, <code>[SỐ ĐIỆN THOẠI]</code> trước khi xuất bản.</em></p>

<h2>Vấn đề mà mọi chủ đồ gỗ đều gặp</h2>
<p>Một bộ bàn ghế gỗ quý, một sàn deck bên hồ bơi, một cánh cửa gỗ nguyên khối — tất cả đều đẹp vào ngày bàn giao. Vấn đề bắt đầu từ tháng thứ mười tám.</p>
<p>Sơn PU nứt chân chim rồi bong thành mảng. Vecni ngả vàng. Dầu lau bay mùi và phải làm lại mỗi năm. Còn phần gỗ bên dưới, sau khi bị nhốt ẩm dưới lớp màng kín, thường đã xốp trước khi ai kịp nhận ra.</p>
<p>Nguyên nhân chung: cả ba phương pháp truyền thống đều phủ lên gỗ. Gỗ thì vẫn sống — vẫn hút và nhả ẩm theo mùa, vẫn giãn nở và co ngót. Không có lớp màng nào theo kịp chuyển động đó trong mười năm.</p>

<h2>Cách tiếp cận khác: bảo vệ từ bên trong</h2>
<p>[TÊN SẢN PHẨM] không tạo màng. Phân tử hoạt chất ở thang nanomet thẩm thấu vào bên trong lỗ rỗng và mao quản của gỗ, rồi đóng rắn thành mạng lưới liên kết chéo mật độ cao ngay trong cấu trúc thớ gỗ.</p>
<p>Kết quả là một bề mặt có ba tính chất cùng lúc — điều mà lớp màng không làm được:</p>
<ul>
<li><strong>Chặn nước lỏng.</strong> Mưa, nước đọng, đồ uống đổ đều không thấm vào gỗ.</li>
<li><strong>Chặn tia UV.</strong> Mạng lưới liên kết chéo hoạt động như rào cản, ngăn UV-A/UV-B phá huỷ lignin — thứ khiến gỗ bạc màu.</li>
<li><strong>Cho hơi nước thoát ra.</strong> Gỗ vẫn thở. Không tích ẩm, không phồng rộp, không mục từ bên trong.</li>
</ul>
<p>Và vì lớp bảo vệ nằm <em>trong</em> gỗ chứ không phải <em>trên</em> gỗ, vân gỗ và cảm giác bề mặt thật được giữ nguyên.</p>

<h2>Dòng sản phẩm</h2>
<h3>[DÒNG TRONG SUỐT] — giữ nguyên vẻ đẹp gốc</h3>
<p>Trong suốt hoàn toàn, giữ nguyên vân và tông màu tự nhiên của gỗ. Độ bóng điều chỉnh được từ bóng cao đến mờ tuỳ phụ gia.</p>
<p><strong>Phù hợp cho:</strong> gỗ quý, đồ nội thất cao cấp, di tích và hiện vật cần bảo tồn nguyên trạng, gỗ mới có vân đẹp.</p>
<h3>[DÒNG CÓ MÀU] — chủ động tạo diện mạo</h3>
<p>Hệ nhuộm màu nano với dải màu rộng, có thể pha theo yêu cầu. Điểm khác biệt so với sơn màu thông thường: hạt màu đi sâu vào lỗ rỗng và thớ gỗ thay vì nằm trên bề mặt, nên vân gỗ hiện rõ hơn chứ không bị che lấp.</p>
<p>Ứng dụng nổi bật là nâng cấp gỗ tạp: gỗ vân mờ, màu nhạt, chất mềm có thể được đưa về diện mạo gỗ cổ hàng trăm năm với vân nổi rõ và màu sâu — mà gỗ vẫn thở được.</p>
<p><strong>Phù hợp cho:</strong> nâng cấp gỗ công nghiệp và gỗ giá rẻ, phục hồi đồ gỗ đã bạc màu, tạo hiệu ứng gỗ cổ cho công trình mới.</p>

<h2>Dùng được trên nền gỗ nào</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Nhóm vật liệu</th><th>Chi tiết</th></tr></thead>
<tbody>
<tr><td>Gỗ tự nhiên</td><td>Cả gỗ cứng và gỗ mềm, gỗ quý lẫn gỗ thông thường</td></tr>
<tr><td>Gỗ công nghiệp</td><td>MDF, plywood, gỗ dán</td></tr>
<tr><td>Gỗ cũ xuống cấp</td><td>Gỗ bạc màu, mục, lão hoá — phục hồi về gần trạng thái ban đầu</td></tr>
<tr><td>Bề mặt đã sơn cũ</td><td>Lớp epoxy, polyurethane, latex đã oxy hoá (sau khi xử lý bề mặt đúng cách)</td></tr>
</tbody>
</table>

<h2>Số liệu chứng minh</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Chỉ tiêu</th><th>Phương pháp</th><th>Kết quả</th></tr></thead>
<tbody>
<tr><td>Gia tốc thời tiết</td><td>SASO ISO 16474-2, 5.000 giờ</td><td>Đổi màu và độ bóng &lt; 2%</td></tr>
<tr><td>Kháng UV</td><td>ASTM D4587 (QUV), 1.500 giờ</td><td>Giữ 99 – 100% độ bóng</td></tr>
<tr><td>Thời tiết Xenon</td><td>ASTM G155, 4.000 giờ</td><td>Giữ độ bóng; ΔE = 0,63 (mắt thường không phân biệt được)</td></tr>
<tr><td>Phun muối</td><td>ASTM B117</td><td>4.000 – 5.000 giờ, không phồng rộp</td></tr>
<tr><td>Độ cứng bề mặt</td><td>ASTM D3363</td><td>4H (tới 7H với phụ gia mờ)</td></tr>
<tr><td>Độ bám dính</td><td>ISO 2409 / ASTM D3359</td><td>Bậc 0 / 5B (100%)</td></tr>
<tr><td>Chống cháy</td><td>ASTM E84</td><td>Class A</td></tr>
<tr><td>VOC</td><td>—</td><td>156 g/L</td></tr>
<tr><td>Phân huỷ sinh học</td><td>—</td><td>&gt; 95%</td></tr>
</tbody>
</table>
<p>Bảo hành 05 – 10 năm. Hiệu quả bảo vệ ghi nhận trong thực tế: trên 10 năm.</p>

<h2>[THƯƠNG HIỆU] cho công việc của bạn</h2>
<p>Ba nhóm khách hàng, ba bài toán khác nhau.</p>
<h3>Chủ sở hữu đồ gỗ quý, đình chùa, di tích</h3>
<p><strong>Điều bạn cần:</strong> bảo tồn giá trị thật — không làm mất vân gỗ, không dùng hoá chất độc hại lên hiện vật, không biến một món đồ trăm tuổi thành đồ trông như mới.</p>
<p><strong>Giải pháp:</strong> công nghệ thẩm thấu cung cấp lại phần nhựa gỗ đã mất theo thời gian, phục hồi màu và sức sống từ bên trong. Bề mặt thật được giữ nguyên. Dòng trong suốt là lựa chọn mặc định cho nhóm này.</p>
<h3>Thợ gỗ và xưởng thi công nội ngoại thất</h3>
<p><strong>Điều bạn cần:</strong> biên lợi nhuận — nâng cấp gỗ rẻ thành sản phẩm bán được giá, thi công nhanh, ít khiếu nại bảo hành.</p>
<p><strong>Giải pháp:</strong> dòng nhuộm màu nano biến gỗ tạp thành sản phẩm có diện mạo gỗ cổ. Thi công bằng súng phun HVLP hoặc Airless tiêu chuẩn, không cần đầu tư thiết bị mới. Khô chạm sau 20–40 phút, xử lý được sau 3–4 giờ — vòng quay xưởng nhanh hơn nhiều so với PU nhiều lớp.</p>
<h3>Doanh nghiệp chế biến gỗ xuất khẩu</h3>
<p><strong>Điều bạn cần:</strong> qua được cửa tiêu chuẩn môi trường của thị trường nhập khẩu, và hàng đến nơi không bị mốc sau bốn tuần trên biển.</p>
<p><strong>Giải pháp:</strong> VOC 156 g/L, không chứa silicone, phân huỷ sinh học trên 95% — hồ sơ đủ để thay thế PU trong dây chuyền. Kháng muối biển vượt 4.000 giờ thử nghiệm, bảo vệ hàng trong suốt chu kỳ vận chuyển đường biển dài ngày.</p>

<h2>Thi công như thế nào</h2>
<p>Quy trình tương thích với thiết bị sẵn có trong hầu hết xưởng gỗ: chà nhám tạo profile (400 grit với bề mặt đã sơn hoặc oxy hoá), tẩy nhờn và lau khô hoàn toàn, phun 3–4 lớp ướt bằng HVLP (béc 1,3–1,5 mm, 29–30 PSI) hoặc Airless (đầu 417/517/617, ~800 PSI), chờ 2–5 phút giữa các lớp. Khô chạm 20–40 phút · khô cứng 24 giờ · đóng rắn hoàn toàn 48 giờ.</p>
<p>Định mức tham khảo: khoảng 8,1 m²/lít ở độ dày màng khô 2 mil. Trên gỗ mộc chưa xử lý, lớp đầu bị hút nhiều hơn nên nên cộng thêm 20–35% khi lập dự toán. Chi tiết đầy đủ có trong tài liệu <em>Quy trình thi công chuẩn</em>.</p>

<h2>Quy cách và bảo quản</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Quy cách</th><th>Dung tích</th><th>Khối lượng</th></tr></thead>
<tbody>
<tr><td>Gallon</td><td>3,8 lít</td><td>3,63 kg</td></tr>
<tr><td>Pail</td><td>19 lít</td><td>18,14 kg</td></tr>
<tr><td>Drum</td><td>208 lít</td><td>~198 kg</td></tr>
</tbody>
</table>
<p>Sản phẩm nhạy với hơi ẩm. Bảo quản ở 4–22 °C giữ được 12 tháng khi chưa mở nắp; ở 27 °C còn 6 tháng. Sau khi mở nắp, dùng trong tối đa 2 tháng và đóng chặt nắp ngay sau mỗi lần rót.</p>

<h2>Trải nghiệm trước khi quyết định</h2>
<p>Mỗi loại gỗ phản ứng khác nhau — độ xốp, hàm lượng dầu tự nhiên, lịch sử xử lý bề mặt đều ảnh hưởng tới kết quả. Vì vậy chúng tôi luôn khuyến nghị chạy mẫu thử trên chính vật liệu của bạn trước khi làm diện rộng.</p>
<p>Nhận tư vấn kỹ thuật và mẫu thử miễn phí. Hồ sơ TDS/MSDS đầy đủ được cung cấp cho khách hàng doanh nghiệp và đơn vị tư vấn thiết kế.</p>
<p><strong>Hotline: [SỐ ĐIỆN THOẠI]</strong> · Hoặc để lại thông tin tại trang Liên hệ.</p>
HTML;

        $content .= $this->relatedLinks([
            ['href' => '/post/vi-sao-lop-phu-nano-bao-ve-duoc-go-ma-son-tao-mang-thi-khong', 'label' => 'Vì sao lớp phủ nano bảo vệ được gỗ mà sơn tạo màng thì không'],
            ['href' => '/post/go-ngoai-troi-vi-sao-moi-lop-son-deu-bong-va-giai-phap-nam-o-dau', 'label' => 'Giải pháp phủ gỗ ngoài trời: kháng UV và để gỗ thở được'],
            ['href' => '/category/noi-that', 'label' => 'Xem sản phẩm — Nội thất'],
            ['href' => '/category/ngoai-that', 'label' => 'Xem sản phẩm — Ngoại thất'],
        ]);

        $contentEn = <<<'HTML'
<p><em>Editorial note: replace <code>[BRAND]</code>, <code>[PRODUCT NAME]</code>, <code>[CLEAR LINE]</code>, <code>[TINTED LINE]</code>, <code>[PHONE NUMBER]</code> before publishing.</em></p>

<h2>The problem every wood owner runs into</h2>
<p>A fine wood dining set, a poolside deck, a solid wood door — all beautiful on handover day. The problem starts around month eighteen.</p>
<p>PU paint develops fine cracks then peels off in sheets. Varnish yellows. Oil finish smells and needs redoing every year. And the wood underneath, trapped under a sealed film, is usually already spongy before anyone notices.</p>
<p>The common cause: all three traditional methods coat the wood. Wood is still alive — still absorbing and releasing moisture with the seasons, still expanding and contracting. No film keeps up with that movement over ten years.</p>

<h2>A different approach: protection from inside</h2>
<p>[PRODUCT NAME] doesn't form a film. Nanometre-scale active molecules penetrate into the wood's pores and capillaries, then cure into a high-density cross-linked network right inside the fibre structure.</p>
<p>The result is a surface with three properties at once — something a film can't deliver:</p>
<ul>
<li><strong>Blocks liquid water.</strong> Rain, standing water, spilled drinks don't soak into the wood.</li>
<li><strong>Blocks UV.</strong> The cross-linked network acts as a barrier, preventing UV-A/UV-B from destroying the lignin that causes greying.</li>
<li><strong>Lets vapour escape.</strong> The wood keeps breathing. No moisture buildup, no blistering, no rotting from inside.</li>
</ul>
<p>And because the protection lives <em>inside</em> the wood rather than <em>on</em> it, the real grain and surface feel are preserved.</p>

<h2>Product lines</h2>
<h3>[CLEAR LINE] — keeps the original beauty</h3>
<p>Fully transparent, keeping the wood's natural grain and tone. Gloss level adjustable from high gloss to matte depending on the additive.</p>
<p><strong>Best for:</strong> fine wood, high-end furniture, heritage items and artefacts needing preservation as-is, new wood with attractive grain.</p>
<h3>[TINTED LINE] — actively shapes the look</h3>
<p>A nano tinting system with a wide colour range, custom-mixable. The difference from ordinary wood stain: colour particles penetrate deep into the pores and fibre instead of sitting on the surface, so the grain shows through more clearly rather than being covered.</p>
<p>A standout use case is upgrading low-grade wood: dull-grained, pale, soft timber can be brought to an antique-wood look with pronounced grain and deep colour — while the wood still breathes.</p>
<p><strong>Best for:</strong> upgrading engineered and budget wood, restoring greyed furniture, creating an aged-wood effect on new construction.</p>

<h2>Which wood substrates it works on</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Material group</th><th>Detail</th></tr></thead>
<tbody>
<tr><td>Natural wood</td><td>Hardwood and softwood, fine and common species alike</td></tr>
<tr><td>Engineered wood</td><td>MDF, plywood, veneer board</td></tr>
<tr><td>Aged, degraded wood</td><td>Greyed, rotted, weathered — restorable close to original condition</td></tr>
<tr><td>Previously coated surfaces</td><td>Oxidised epoxy, polyurethane or latex layers (after correct surface prep)</td></tr>
</tbody>
</table>

<h2>Supporting data</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Property</th><th>Method</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Accelerated weathering</td><td>SASO ISO 16474-2, 5,000 hours</td><td>Colour and gloss change &lt; 2%</td></tr>
<tr><td>UV resistance</td><td>ASTM D4587 (QUV), 1,500 hours</td><td>Retains 99–100% gloss</td></tr>
<tr><td>Xenon weathering</td><td>ASTM G155, 4,000 hours</td><td>Retains gloss; ΔE = 0.63 (imperceptible to the naked eye)</td></tr>
<tr><td>Salt spray</td><td>ASTM B117</td><td>4,000 – 5,000 hours, no blistering</td></tr>
<tr><td>Surface hardness</td><td>ASTM D3363</td><td>4H (up to 7H with matting additive)</td></tr>
<tr><td>Adhesion</td><td>ISO 2409 / ASTM D3359</td><td>Grade 0 / 5B (100%)</td></tr>
<tr><td>Fire resistance</td><td>ASTM E84</td><td>Class A</td></tr>
<tr><td>VOC</td><td>—</td><td>156 g/L</td></tr>
<tr><td>Biodegradability</td><td>—</td><td>&gt; 95%</td></tr>
</tbody>
</table>
<p>Warranty 5–10 years. Field-reported protective effectiveness: over 10 years.</p>

<h2>[BRAND] for your business</h2>
<p>Three customer groups, three different problems.</p>
<h3>Owners of fine furniture, temples, heritage buildings</h3>
<p><strong>What you need:</strong> preserving real value — not losing the grain, not using harmful chemicals on artefacts, not turning a century-old piece into something that looks brand new.</p>
<p><strong>The solution:</strong> penetrating technology resupplies the natural resin the wood lost over time, restoring colour and vitality from within. The real surface is preserved — no film sits over it. The clear line is the default choice for this group.</p>
<h3>Carpenters and interior/exterior workshops</h3>
<p><strong>What you need:</strong> margin — upgrading cheap wood into a sellable product, fast turnaround, fewer warranty complaints.</p>
<p><strong>The solution:</strong> the tinted nano line turns common wood into an antique-look product. Applies with standard HVLP or Airless equipment, no new investment needed. Touch dry in 20–40 minutes, workable after 3–4 hours — a much faster shop turnaround than multi-coat PU.</p>
<h3>Export wood processors</h3>
<p><strong>What you need:</strong> clearing the environmental-standard bar in your import market, and cargo that arrives mould-free after four weeks at sea.</p>
<p><strong>The solution:</strong> VOC 156 g/L, silicone-free, &gt; 95% biodegradable — a profile that can replace PU in your line. Salt resistance beyond 4,000 test hours protects cargo through long sea-freight cycles. A 10+ year lifespan is a sales argument you can use with your own customers downstream.</p>

<h2>How it's applied</h2>
<p>The process fits equipment already in most wood workshops: sand for profile (400 grit on painted or oxidised surfaces), degrease and dry completely, spray 3–4 wet coats with HVLP (1.3–1.5 mm tip, 29–30 PSI) or Airless (417/517/617 tip, ~800 PSI), 2–5 minutes flash-off between coats. Touch dry 20–40 minutes · hard dry 24 hours · full cure 48 hours.</p>
<p>Reference coverage: about 8.1 m²/L at 2-mil dry film thickness. On bare, untreated wood the first coat absorbs more, so add 20–35% when estimating. Full detail is in the <em>Standard Application Process</em> guide.</p>

<h2>Packaging and storage</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Packaging</th><th>Volume</th><th>Weight</th></tr></thead>
<tbody>
<tr><td>Gallon</td><td>3.8 L</td><td>3.63 kg</td></tr>
<tr><td>Pail</td><td>19 L</td><td>18.14 kg</td></tr>
<tr><td>Drum</td><td>208 L</td><td>~198 kg</td></tr>
</tbody>
</table>
<p>Moisture-sensitive. Stored at 4–22 °C, shelf life is 12 months unopened; at 27 °C, 6 months. After opening, use within 2 months and reseal the lid tightly after every pour.</p>

<h2>Test it before you decide</h2>
<p>Every wood species responds differently — porosity, natural oil content and surface treatment history all affect the outcome. We always recommend running a sample on your own material before committing to a full-scale job.</p>
<p>Get free technical advice and a sample. Full TDS/MSDS documentation is provided for enterprise customers and design consultants.</p>
<p><strong>Hotline: [PHONE NUMBER]</strong> · Or leave your details on the Contact page.</p>
HTML;

        $contentEn .= $this->relatedLinksEn([
            ['href' => '/post/how-nano-coating-protects-wood-not-film-forming-paint', 'label' => 'Why Nano Coating Protects Wood While Film-Forming Paint Does Not'],
            ['href' => '/post/outdoor-wood-coating-solution-uv-resistant', 'label' => 'Outdoor Wood Coating: UV Resistance While Letting Wood Breathe'],
            ['href' => '/category/interior', 'label' => 'Browse Products — Interior'],
            ['href' => '/category/exterior', 'label' => 'Browse Products — Exterior'],
        ]);

        return [
            'group' => 'gioi-thieu-san-pham',
            'slug' => 'thuong-hieu-cho-vat-lieu-go-bao-ve-tu-ben-trong-tho-go',
            'title' => '[THƯƠNG HIỆU] cho vật liệu gỗ: bảo vệ từ bên trong thớ gỗ',
            'excerpt' => 'Dòng phủ nano cho gỗ nội và ngoại thất — thẩm thấu vào thớ gỗ, kháng UV, giữ vân gỗ tự nhiên, gỗ vẫn thở được.',
            'meta_title' => 'Sơn nano cho gỗ — bảo vệ từ bên trong',
            'meta_description' => 'Dòng phủ nano cho gỗ nội và ngoại thất - thẩm thấu vào thớ gỗ, kháng UV, giữ vân gỗ tự nhiên, gỗ vẫn thở được. VOC thấp, bảo hành 5–10 năm.',
            'content' => $content,
            'slug_en' => 'nano-coating-product-line-for-wood',
            'title_en' => '[BRAND] for Wood Materials: Protection From Inside the Fibre',
            'excerpt_en' => 'A nano coating line for interior and exterior wood — penetrates the fibre, resists UV, keeps the natural grain, and lets the wood breathe.',
            'meta_title_en' => 'Nano Coating for Wood — Protection From Within',
            'meta_description_en' => 'A nano coating line for interior and exterior wood - penetrates the wood fibre, resists UV, keeps natural grain, wood still breathes. Low VOC, 5-10 year warranty.',
            'content_en' => $contentEn,
        ];
    }

    protected function gp01(): array
    {
        $content = <<<'HTML'
<p><em>Ghi chú biên tập: thay <code>[THƯƠNG HIỆU]</code>, <code>[TÊN SẢN PHẨM]</code>, <code>[SỐ ĐIỆN THOẠI]</code> trước khi xuất bản.</em></p>

<h2>Ba tháng đẹp, ba năm sửa</h2>
<p>Sàn gỗ deck bên hồ bơi bàn giao tháng Ba. Tháng Sáu bắt đầu xỉn. Tháng Chín có vệt bạc ở khu vực nắng chiếu trực tiếp. Sang năm thứ hai, mép ván nứt và lớp sơn bắt đầu tróc quanh đầu vít.</p>
<p>Kịch bản này lặp lại ở gần như mọi công trình gỗ ngoài trời tại Việt Nam, bất kể dùng gỗ gì và sơn gì. Lý do là hai điều kiện khí hậu cộng dồn: bức xạ UV cao quanh năm và biên độ ẩm lớn theo mùa. Miền Trung và các dự án ven biển còn cộng thêm muối biển trong không khí.</p>

<h2>Gỗ ngoài trời hỏng theo ba hướng cùng lúc</h2>
<h3>Tia UV ăn mất lignin</h3>
<p>Lignin là chất kết dính giữ các sợi cellulose lại với nhau, và cũng là thành phần hấp thụ UV mạnh nhất trong gỗ. Photon UV cắt đứt liên kết hoá học trong phân tử lignin, biến nó thành các mảnh tan trong nước và bị mưa rửa trôi. Lớp gỗ xám bạc mà bạn thấy trên sàn deck cũ chính là cellulose trần đã mất chất kết dính.</p>
<h3>Chu kỳ ẩm phá vỡ cấu trúc</h3>
<p>Nắng làm bề mặt nóng và mất ẩm nhanh hơn phần lõi. Mưa làm ngược lại, lại mang theo CO₂, SOₓ, NOₓ hoà tan thành các axit yếu. Sau vài trăm chu kỳ giãn nở — co ngót không đều, ứng suất tích luỹ vượt ngưỡng và bề mặt nứt. Với gỗ đã sơn, chính các vết nứt này là đường cho nước len vào bên dưới màng sơn.</p>
<h3>Vi sinh vật ăn phần còn lại</h3>
<p>Nấm, mốc và rêu dùng gỗ làm thức ăn, thải ra axit sunphuric và nitric làm mục rã phần xơ. Điều kiện duy nhất chúng cần là ẩm đọng lại — và một lớp sơn bịt kín tạo ra chính xác điều đó ở mặt dưới của nó.</p>

<h2>Vì sao lớp màng kín làm mọi thứ tệ hơn</h2>
<p>Đây là nghịch lý mà ngành sơn gỗ mất nhiều năm mới thừa nhận: càng bịt kín, gỗ càng hỏng nhanh.</p>
<p>Gỗ ngoài trời ở Việt Nam cân bằng ẩm trong khoảng 14–20% tuỳ mùa. Khi mặt trên bị màng PU hoặc vecni bít lại, hơi nước tích tụ ngay dưới màng, áp suất hơi tăng, tạo bọt, rồi phồng, rồi bong — trong khi phần gỗ ẩm bị nhốt bên dưới là môi trường hoàn hảo cho nấm mục.</p>
<p>Hậu quả kép: bề mặt trông vẫn ổn trong khi gỗ bên dưới đã xốp. Khi phát hiện thì chi phí không còn là sơn lại mà là thay ván. Chu kỳ bảo trì cũng đắt: mỗi lần sơn lại phải chà nhám bóc sạch lớp cũ, sau ba đến bốn lần, mặt ván mỏng thấy rõ, đầu vít lộ ra, vân gỗ mất nét.</p>

<h2>Giải pháp: bảo vệ trong lòng gỗ, không phải trên mặt gỗ</h2>
<p>Công nghệ phủ nano đảo ngược cách tiếp cận. Phân tử hoạt chất ở thang nanomet thẩm thấu vào lỗ rỗng và mao quản của gỗ, đóng rắn thành mạng lưới liên kết chéo mật độ cao — 2,17 × 10³ mol/m³ — ngay bên trong cấu trúc thớ gỗ.</p>
<p>Ba tính chất đồng thời: chặn nước lỏng; chặn UV bằng rào cản cấu trúc không hao mòn theo thời gian (khác chất hấp thụ UV dạng phụ gia vốn tiêu hao và di trú khỏi màng); cho hơi nước thoát ra vì lớp phủ không tạo màng liên tục. Thêm một hệ quả quan trọng cho công trình ngoài trời: vì lớp phủ nằm <em>trong</em> gỗ, nó giãn nở và co ngót cùng gỗ — không có ranh giới màng/nền để bong tách.</p>

<h2>Con số chứng minh</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Thử nghiệm</th><th>Điều kiện</th><th>Kết quả</th></tr></thead>
<tbody>
<tr><td>Gia tốc thời tiết SASO ISO 16474-2</td><td>5.000 giờ</td><td>Đổi màu và độ bóng &lt; 2%</td></tr>
<tr><td>Kháng UV QUV — ASTM D4587</td><td>1.500 giờ</td><td>Giữ 99 – 100% độ bóng ban đầu</td></tr>
<tr><td>Xenon WOM — ASTM G155</td><td>4.000 giờ</td><td>Giữ độ bóng; ΔE = 0,63</td></tr>
<tr><td>Phun muối — ASTM B117 / SASO ISO 11997</td><td>4.000 – 5.000 giờ</td><td>Không phồng rộp, không rỉ</td></tr>
<tr><td>Dải nhiệt độ làm việc</td><td>Chu trình DMA</td><td>−50 °C đến 200 °C</td></tr>
<tr><td>Nhiệt độ chuyển thuỷ tinh (Tg)</td><td>DMA</td><td>57,7 – 96,1 °C</td></tr>
</tbody>
</table>
<p>Hai con số đáng chú ý với người làm kỹ thuật: <strong>ΔE = 0,63 sau 4.000 giờ Xenon</strong> — dưới ngưỡng 1,0 mà mắt người bình thường không phân biệt được sự khác biệt màu. Và <strong>Tg 57,7 – 96,1 °C</strong> — bề mặt gỗ ngoài trời ở Việt Nam vào trưa hè có thể đạt 55–65 °C, nên lớp phủ không mềm ra và bám dính không suy giảm dưới nắng trực xạ.</p>

<h2>So sánh cho quyết định đầu tư</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Tiêu chí</th><th>Lớp phủ nano</th><th>Sơn PU / Vecni / Dầu lau</th></tr></thead>
<tbody>
<tr><td>Cơ chế</td><td>Thẩm thấu, không tạo màng</td><td>Tạo màng kín trên bề mặt</td></tr>
<tr><td>Thoát hơi ẩm</td><td>Có — bề mặt luôn khô ráo</td><td>Không — ẩm tích dưới màng</td></tr>
<tr><td>Kiểu hỏng</td><td>Mòn dần đều</td><td>Phồng, nứt, bong mảng</td></tr>
<tr><td>Tuổi thọ bảo vệ</td><td>Trên 10 năm</td><td>Ngắn, xuống cấp nhanh</td></tr>
<tr><td>Bảo trì</td><td>Phủ chồng lớp mới</td><td>Chà nhám bóc sạch rồi sơn lại</td></tr>
<tr><td>Hao mòn vật liệu gỗ</td><td>Không</td><td>Mất một lớp gỗ mỗi chu kỳ</td></tr>
<tr><td>Xử lý gỗ đã bạc màu</td><td>Phục hồi về gần trạng thái gốc</td><td>Chỉ che, gỗ vẫn mục bên dưới</td></tr>
<tr><td>Vân gỗ</td><td>Giữ nguyên bề mặt thật</td><td>Bị che phủ</td></tr>
<tr><td>Chi phí vòng đời</td><td>Thấp</td><td>Cao do bảo trì lặp lại</td></tr>
</tbody>
</table>

<h2>Khí hậu Việt Nam và vùng ven biển</h2>
<p><strong>Nhiệt đới nóng ẩm.</strong> Mật độ liên kết chéo cực cao cho phép lớp phủ chịu dải nhiệt −50 °C đến 200 °C mà không giòn gãy hay biến tính. Nắng gắt miền Trung và miền Nam nằm hoàn toàn trong vùng an toàn.</p>
<p><strong>Ven biển.</strong> Kháng muối đạt 4.000 – 5.000 giờ phun muối liên tục. Với resort, cầu cảng và công trình sát biển, đây là chỉ tiêu quyết định.</p>
<p><strong>Mùa nồm miền Bắc.</strong> Bề mặt khô ráo và thoát hơi tốt là yếu tố ngăn rêu mốc trong những tuần độ ẩm không khí vượt 90%.</p>

<h2>Nên bắt đầu từ đâu</h2>
<p>Mỗi loại gỗ có độ xốp và hàm lượng dầu tự nhiên khác nhau, và lịch sử xử lý bề mặt của công trình cũ cũng ảnh hưởng tới kết quả. Cách duy nhất để biết chắc là chạy mẫu thử trên chính vật liệu của bạn.</p>
<p>Nhận tư vấn kỹ thuật và mẫu thử miễn phí — <strong>Hotline [SỐ ĐIỆN THOẠI]</strong>. Hồ sơ TDS/MSDS cung cấp đầy đủ cho chủ đầu tư và đơn vị tư vấn thiết kế.</p>
HTML;

        $content .= $this->relatedLinks([
            ['href' => '/post/vi-sao-lop-phu-nano-bao-ve-duoc-go-ma-son-tao-mang-thi-khong', 'label' => 'Vì sao lớp phủ nano bảo vệ được gỗ mà sơn tạo màng thì không'],
            ['href' => '/post/giai-phap-nano-theo-tung-hang-muc-go-ngoai-troi', 'label' => 'Giải pháp nano theo từng hạng mục gỗ ngoài trời'],
            ['href' => '/post-category/case-study', 'label' => 'Ví dụ thực tế / Case study'],
        ]);

        $contentEn = <<<'HTML'
<p><em>Editorial note: replace <code>[BRAND]</code>, <code>[PRODUCT NAME]</code>, <code>[PHONE NUMBER]</code> before publishing.</em></p>

<h2>Three months beautiful, three years of repairs</h2>
<p>A poolside wood deck is handed over in March. By June it starts dulling. By September there are grey patches where the sun hits directly. In year two, board edges crack and the paint starts flaking around screw heads.</p>
<p>This script repeats on almost every outdoor wood project in Vietnam, regardless of wood species or coating used. The reason is two compounding climate factors: year-round high UV radiation and large seasonal humidity swings. Central Vietnam and coastal projects add sea salt to the mix.</p>

<h2>Outdoor wood fails in three directions at once</h2>
<h3>UV eats away the lignin</h3>
<p>Lignin is the binder holding cellulose fibres together, and also the strongest UV absorber in wood. UV photons break chemical bonds in the lignin molecule, turning it into water-soluble fragments that rain washes away. The grey wood you see on an old deck is bare cellulose that's lost its binder.</p>
<h3>Moisture cycles break down the structure</h3>
<p>Sun heats the surface and dries it faster than the core. Rain reverses it — and carries dissolved CO₂, SOₓ, NOₓ that form weak acids. After a few hundred uneven expansion-contraction cycles, accumulated stress exceeds the threshold and the surface cracks. On painted wood, those cracks are exactly where water gets in under the film.</p>
<h3>Micro-organisms finish the job</h3>
<p>Fungi, mould and algae feed on wood, releasing sulphuric and nitric acid that breaks down the fibre. The only condition they need is standing moisture — and a sealed paint film creates exactly that on its underside.</p>

<h2>Why a sealed film makes everything worse</h2>
<p>This is the paradox the wood-coating industry took years to admit: the more sealed it is, the faster the wood fails.</p>
<p>Outdoor wood in Vietnam equilibrates at 14–20% moisture depending on season. When the top is sealed by PU or varnish, vapour accumulates right under the film, pressure builds, bubbles form, then blistering, then peeling — and the whole time, the moist wood trapped underneath is the perfect environment for rot fungi.</p>
<p>Double the damage: the surface still looks fine while the wood underneath is already spongy. By the time it's discovered, the cost is no longer repainting but replacing the board. The maintenance cycle is expensive too: every repaint means sanding the old layer completely off. After three or four cycles, the board is visibly thinner, screw heads show, and the grain is gone.</p>

<h2>The solution: protect inside the wood, not on top of it</h2>
<p>Nano coating technology reverses the approach. Nanometre-scale active molecules penetrate the wood's pores and capillaries, curing into a high-density cross-linked network — 2.17 × 10³ mol/m³ — right inside the fibre structure.</p>
<p>Three properties at once: blocks liquid water — rain and standing water don't soak in. Blocks UV through a structural barrier — the dense cross-linked network absorbs and dissipates UV-A/UV-B energy before it reaches the lignin. Unlike additive UV absorbers, which deplete over time and migrate out of the film, a structural barrier doesn't wear out — it is the coating's own framework. And it lets vapour escape — because the coating sits in the pores rather than forming a continuous film, water vapour molecules still have a way out, so the wood stays dry, no pressure buildup, no blistering, and mould has no foothold.</p>
<p>One more important consequence for outdoor projects: because the coating lives <em>inside</em> the wood, it expands and contracts with it — there's no film/substrate boundary to delaminate.</p>

<h2>The numbers</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Test</th><th>Condition</th><th>Result</th></tr></thead>
<tbody>
<tr><td>Accelerated weathering SASO ISO 16474-2</td><td>5,000 hours</td><td>Colour and gloss change &lt; 2%</td></tr>
<tr><td>UV resistance QUV — ASTM D4587</td><td>1,500 hours</td><td>Retains 99–100% of original gloss</td></tr>
<tr><td>Xenon WOM — ASTM G155</td><td>4,000 hours</td><td>Retains gloss; ΔE = 0.63</td></tr>
<tr><td>Salt spray — ASTM B117 / SASO ISO 11997</td><td>4,000 – 5,000 hours</td><td>No blistering, no corrosion</td></tr>
<tr><td>Working temperature range</td><td>DMA cycle</td><td>−50 °C to 200 °C</td></tr>
<tr><td>Glass transition temperature (Tg)</td><td>DMA</td><td>57.7 – 96.1 °C</td></tr>
</tbody>
</table>
<p>Two numbers worth flagging for technical audiences: <strong>ΔE = 0.63 after 4,000 hours of Xenon exposure</strong> — below the 1.0 threshold where the average human eye can't tell the colour has shifted. And <strong>Tg of 57.7 – 96.1 °C</strong> — outdoor wood surfaces in Vietnam can reach 55–65 °C at midday in summer, so the coating doesn't soften and adhesion doesn't degrade under direct sun.</p>

<h2>Comparison for the investment decision</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Criterion</th><th>Nano coating</th><th>PU paint / Varnish / Oil finish</th></tr></thead>
<tbody>
<tr><td>Mechanism</td><td>Penetrating, no film</td><td>Forms a sealed surface film</td></tr>
<tr><td>Moisture escape</td><td>Yes — surface stays dry</td><td>No — moisture accumulates under the film</td></tr>
<tr><td>Failure mode</td><td>Gradual, even wear</td><td>Blistering, cracking, peeling in sheets</td></tr>
<tr><td>Protective lifespan</td><td>Over 10 years</td><td>Short, degrades quickly</td></tr>
<tr><td>Maintenance</td><td>Recoat over the top</td><td>Sand off completely, then repaint</td></tr>
<tr><td>Wood material loss</td><td>None</td><td>Loses a layer of wood every cycle</td></tr>
<tr><td>Handling greyed wood</td><td>Restores close to original condition</td><td>Only covers it — the wood keeps rotting underneath</td></tr>
<tr><td>Wood grain</td><td>Keeps the real surface</td><td>Covered up</td></tr>
<tr><td>Life-cycle cost</td><td>Low</td><td>High due to repeated maintenance</td></tr>
</tbody>
</table>

<h2>Vietnam's climate and coastal areas</h2>
<p><strong>Hot, humid tropics.</strong> The extremely high cross-link density lets the coating withstand −50 °C to 200 °C without becoming brittle or degrading. Harsh sun in central and southern Vietnam sits well within the safe range.</p>
<p><strong>Coastal.</strong> Salt resistance reaches 4,000–5,000 hours of continuous salt spray. For resorts, piers and seafront projects, this is the deciding metric — airborne sea salt is a far stronger corrosive agent than ordinary sun and rain.</p>
<p><strong>Northern Vietnam's humid season.</strong> A dry, well-ventilated surface is what prevents moss and mould during the weeks when air humidity exceeds 90%.</p>

<h2>Where to start</h2>
<p>Every wood species has different porosity and natural oil content, and the surface treatment history of an existing structure also affects results. The only reliable way to know is to test on your own material.</p>
<p>Get free technical advice and a sample — <strong>Hotline [PHONE NUMBER]</strong>. Full TDS/MSDS documentation is provided for developers and design consultants.</p>
HTML;

        $contentEn .= $this->relatedLinksEn([
            ['href' => '/post/how-nano-coating-protects-wood-not-film-forming-paint', 'label' => 'Why Nano Coating Protects Wood While Film-Forming Paint Does Not'],
            ['href' => '/post/nano-solutions-by-outdoor-wood-category', 'label' => 'Nano Solutions by Outdoor Wood Category'],
            ['href' => '/post-category/case-study', 'label' => 'Case Studies'],
        ]);

        return [
            'group' => 'giai-phap',
            'slug' => 'go-ngoai-troi-vi-sao-moi-lop-son-deu-bong-va-giai-phap-nam-o-dau',
            'title' => 'Gỗ ngoài trời: vì sao mọi lớp sơn đều bong, và giải pháp nằm ở đâu',
            'excerpt' => 'Sàn deck bạc màu, lan can bong sơn, facade nứt chân chim — nguyên nhân và giải pháp phủ nano kháng UV cho gỗ ngoài trời, giữ cho gỗ thở được.',
            'meta_title' => 'Sơn gỗ ngoài trời chống UV',
            'meta_description' => 'Sàn deck bạc màu, lan can bong sơn, facade nứt chân chim - nguyên nhân và giải pháp phủ nano kháng UV cho gỗ ngoài trời, giữ cho gỗ thở được.',
            'content' => $content,
            'slug_en' => 'outdoor-wood-coating-solution-uv-resistant',
            'title_en' => 'Outdoor Wood: Why Every Paint Job Peels, and Where the Real Solution Is',
            'excerpt_en' => 'Greyed decking, peeling railings, cracked facades — causes and the UV-resistant nano coating solution for outdoor wood that still lets it breathe.',
            'meta_title_en' => 'UV-Resistant Outdoor Wood Coating',
            'meta_description_en' => 'Greyed decking, peeling railings, cracked facades - causes and the nano coating solution for outdoor wood: UV resistant, still lets the wood breathe.',
            'content_en' => $contentEn,
        ];
    }

    protected function gp02(): array
    {
        $content = <<<'HTML'
<p><em>Ghi chú biên tập: thay <code>[DÒNG TRONG SUỐT]</code>, <code>[DÒNG CÓ MÀU]</code>, <code>[SỐ ĐIỆN THOẠI]</code> trước khi xuất bản. Phần "Dự án tham khảo" ở cuối bài là dự án của thương hiệu tham chiếu trong bộ tài liệu gốc — chỉ giữ lại nếu bạn là đơn vị phân phối chính thức và có quyền sử dụng tên dự án; nếu không, xoá và thay bằng dự án của chính bạn.</em></p>

<p>Cùng là gỗ ngoài trời, nhưng một sàn deck resort chịu tải mài mòn hoàn toàn khác một cột gỗ đình chùa 200 tuổi. Bài này chia theo hạng mục để bạn tìm đúng cấu hình cho công trình của mình.</p>

<h2>1. Sàn gỗ ngoài trời (decking)</h2>
<p><strong>Điều kiện làm việc:</strong> nắng trực xạ cả ngày, mưa đọng, người đi lại liên tục, ghế và vali kéo trên bề mặt, hoá chất từ nước hồ bơi.</p>
<p><strong>Bài toán chính:</strong> mài mòn cơ học cộng với bạc màu do UV — sàn deck là hạng mục hỏng nhanh nhất trong mọi công trình gỗ ngoài trời.</p>
<p><strong>Cấu hình đề xuất:</strong> [DÒNG TRONG SUỐT] nếu gỗ có vân đẹp cần giữ; [DÒNG CÓ MÀU] nếu cần đồng nhất màu giữa các ván thay thế. 4 lớp ướt, DFT mục tiêu ở cận trên của dải: 2,0 – 2,5 mil. Thi công bằng miếng đệm lau chuyên dụng cho diện tích lớn, hoặc HVLP cho khu vực cần độ phẳng cao.</p>
<p><strong>Chỉ tiêu nghiệm thu:</strong> mài mòn Taber (hao hụt 8,4 – 19,85 mg theo ASTM D4060), độ cứng bút chì tối thiểu 4H.</p>
<p><strong>Lưu ý thi công:</strong> không đưa vào sử dụng trước 48 giờ; không kê đồ nặng và không cho xe đẩy chạy trong 7 ngày đầu.</p>

<h2>2. Facade và ốp tường gỗ ngoài trời</h2>
<p><strong>Điều kiện làm việc:</strong> phơi nắng trực tiếp diện rộng, gió, mưa hắt, biên độ ẩm thay đổi liên tục theo ngày đêm.</p>
<p><strong>Bài toán chính:</strong> giữ màu sắc đồng đều trên diện tích lớn và không bong tróc — một mảng bạc màu cục bộ phá hỏng toàn bộ mặt đứng nhìn từ xa hàng chục mét.</p>
<p><strong>Cấu hình đề xuất:</strong> [DÒNG TRONG SUỐT] để giữ nguyên bản chất vật liệu, hoặc [DÒNG CÓ MÀU] khi cần kiểm soát tông màu theo thiết kế. Ưu tiên thi công trọn vẹn từng mặt đứng trong cùng một ngày, cùng một lô sản phẩm, để tránh chênh màu giữa các mẻ.</p>
<p><strong>Chỉ tiêu quyết định:</strong> ΔE = 0,63 sau 4.000 giờ Xenon (ASTM G155) và độ bóng giữ trên 99% sau 1.500 giờ QUV.</p>
<p><strong>Lưu ý thi công:</strong> kiểm tra điểm sương trước khi phun — mặt đứng hướng đông vào sáng sớm dễ đọng ẩm, gây lỗi bám dính.</p>

<h2>3. Lan can, cầu thang, pergola ngoài trời</h2>
<p><strong>Điều kiện làm việc:</strong> tiếp xúc tay người liên tục, nắng mưa trực tiếp, và ở resort ven biển thì thêm hơi muối.</p>
<p><strong>Bài toán chính:</strong> vừa phải bền thời tiết vừa phải giữ được cảm giác bề mặt dễ chịu khi chạm — hạng mục người dùng tiếp xúc trực tiếp nhiều nhất.</p>
<p><strong>Cấu hình đề xuất:</strong> [DÒNG CÓ MÀU] cho hạng mục cần phục hồi màu đã xuống cấp; [DÒNG TRONG SUỐT] cho gỗ mới. Điều chỉnh độ bóng theo yêu cầu thiết kế — bóng cao dễ vệ sinh, mờ cho cảm giác tự nhiên hơn.</p>
<p><strong>Chỉ tiêu quyết định:</strong> kháng muối 4.000 – 5.000 giờ (ASTM B117) với công trình ven biển.</p>

<h2>4. Cầu cảng, bến thuyền, hạng mục tiếp xúc nước mặn</h2>
<p><strong>Điều kiện làm việc:</strong> khắc nghiệt nhất trong nhóm — muối biển liên tục, ẩm gần bão hoà, tia UV phản xạ từ mặt nước làm tăng liều lượng thực nhận.</p>
<p><strong>Bài toán chính:</strong> ăn mòn do muối và mục do ẩm thường trực.</p>
<p><strong>Cấu hình đề xuất:</strong> DFT ở cận trên của dải khuyến nghị. Ưu tiên khả năng thoát hơi ẩm — ở môi trường này, một lớp màng kín sẽ hỏng trong vòng một mùa.</p>
<p><strong>Chỉ tiêu quyết định:</strong> phun muối 4.000 – 5.000 giờ không phồng rộp (ASTM B117 / SASO ISO 11997); ngâm nước 240 giờ ở 50 °C không đổi màu (ISO 2812-2).</p>

<h2>5. Đình chùa, nhà thờ gỗ, kiến trúc tín ngưỡng</h2>
<p><strong>Điều kiện làm việc:</strong> kết cấu gỗ tuổi đời hàng chục đến hàng trăm năm, đã mất phần lớn nhựa gỗ tự nhiên, thường đã bạc màu hoặc mục cục bộ.</p>
<p><strong>Bài toán chính:</strong> phục hồi mà không được phép làm thay đổi bản chất vật liệu, không dùng hoá chất độc hại trong không gian có người sinh hoạt thường xuyên, và không được làm mất vẻ cổ.</p>
<p><strong>Cấu hình đề xuất:</strong> [DÒNG TRONG SUỐT] là lựa chọn mặc định. Bắt buộc thử nghiệm trên một vùng khuất trước khi làm diện rộng. Chà nhám ở mức tối thiểu cần thiết — mục tiêu là tạo profile bám, không phải làm mới bề mặt.</p>
<p>Lập luận thuyết phục ban quản lý di tích: VOC 156 g/L, không chứa silicone, phân huỷ sinh học trên 95%, và cơ chế thẩm thấu không tạo màng nghĩa là can thiệp có thể đảo ngược ở mức tối đa so với sơn phủ.</p>

<h2>6. Di tích và hiện vật gỗ cần bảo tồn</h2>
<p><strong>Điều kiện làm việc:</strong> giá trị vật liệu không thể thay thế, mọi can thiệp đều phải cân nhắc.</p>
<p><strong>Bài toán chính:</strong> đảm bảo tính toàn vẹn vật liệu — đây là nhóm mà sai sót không có đường sửa.</p>
<p><strong>Cấu hình đề xuất:</strong> chỉ [DÒNG TRONG SUỐT]. Quy trình phải có bước lập hồ sơ ảnh trước/sau và thử nghiệm trên mẫu tương đương. Phối hợp với chuyên gia bảo tồn — bài viết này không thay thế đánh giá chuyên môn về bảo tồn di sản.</p>

<h2>7. Gỗ công nghiệp và gỗ tạp dùng ngoài trời</h2>
<p><strong>Điều kiện làm việc:</strong> vật liệu nền có độ bền tự nhiên thấp, vân mờ, màu nhạt.</p>
<p><strong>Bài toán chính:</strong> vừa nâng cấp thẩm mỹ vừa kéo dài tuổi thọ — bài toán biên lợi nhuận của xưởng thi công.</p>
<p><strong>Cấu hình đề xuất:</strong> [DÒNG CÓ MÀU]. Hạt màu nano đi sâu vào lỗ rỗng làm vân gỗ nổi rõ hơn thay vì bị che lấp, cho phép đưa gỗ tạp về diện mạo gỗ cổ. MDF và plywood dùng ngoài trời cần xử lý kỹ cạnh cắt — đây là điểm hút ẩm mạnh nhất.</p>

<h2>Bảng tra nhanh</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Hạng mục</th><th>Dòng đề xuất</th><th>Chỉ tiêu quyết định</th></tr></thead>
<tbody>
<tr><td>Sàn deck</td><td>Trong suốt hoặc có màu</td><td>Mài mòn Taber, độ cứng 4H</td></tr>
<tr><td>Facade</td><td>Trong suốt hoặc có màu</td><td>ΔE &lt; 1 sau Xenon, giữ độ bóng QUV</td></tr>
<tr><td>Lan can, pergola</td><td>Có màu (phục hồi) / trong suốt (gỗ mới)</td><td>Kháng muối, cảm giác bề mặt</td></tr>
<tr><td>Cầu cảng</td><td>Ưu tiên khả năng thoát ẩm</td><td>Phun muối 4.000–5.000 h, ngâm nước</td></tr>
<tr><td>Đình chùa</td><td>Trong suốt</td><td>VOC thấp, không độc, giữ vẻ cổ</td></tr>
<tr><td>Di tích</td><td>Trong suốt</td><td>Toàn vẹn vật liệu</td></tr>
<tr><td>Gỗ công nghiệp</td><td>Có màu</td><td>Nâng cấp thẩm mỹ + xử lý cạnh cắt</td></tr>
</tbody>
</table>

<h2>Dự án tham khảo</h2>
<p><strong>Cảnh báo biên tập — đọc trước khi đăng:</strong> danh sách dưới đây là dự án của thương hiệu tham chiếu trong bộ tài liệu ngành gỗ, không phải dự án của bạn. Chỉ đăng nếu bạn là đơn vị phân phối chính thức và có văn bản cho phép sử dụng tên dự án. Nếu không, hãy xoá toàn bộ mục này và thay bằng dự án của chính bạn.</p>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Dự án</th><th>Hạng mục</th><th>Nội dung xử lý</th></tr></thead>
<tbody>
<tr><td>Amanoi, Ninh Thuận</td><td>Sàn deck ngoài trời</td><td>Phục hồi và bảo vệ sàn gỗ bạc màu do nắng và gió biển</td></tr>
<tr><td>Hoiana, Quảng Nam</td><td>Sàn deck ngoài trời</td><td>Bảo vệ bề mặt gỗ khu vực đi lại mật độ cao</td></tr>
<tr><td>InterContinental, Đà Nẵng</td><td>Lan can, cầu thang gỗ ngoài trời</td><td>Phục hồi bằng công nghệ nhuộm màu nano, kháng muối biển</td></tr>
<tr><td>Sân bay quốc tế Vân Đồn</td><td>Ghế gỗ ngoài trời</td><td>Xử lý và bảo vệ bề mặt</td></tr>
<tr><td>Nhà thờ gỗ Cẩm Mỹ, Đồng Nai</td><td>Kết cấu gỗ ~200 năm tuổi</td><td>Phục hồi công trình đã xuống cấp</td></tr>
<tr><td>Đền thờ Vua Đinh – Vua Lê</td><td>Kết cấu gỗ di tích</td><td>Bảo tồn, đảm bảo toàn vẹn vật liệu</td></tr>
<tr><td>Tháp Chăm Chiên Đàn</td><td>Hạng mục gỗ di tích</td><td>Bảo tồn</td></tr>
<tr><td>Nhà hàng Bò Thảo dược, Hà Giang</td><td>Nội thất gỗ tạp</td><td>Nhuộm màu nano tạo diện mạo gỗ cổ</td></tr>
<tr><td>Công trình tại Bỉ và Đan Mạch</td><td>Facade gỗ</td><td>Giữ màu và chống bong tróc trên mặt đứng</td></tr>
</tbody>
</table>

<h2>Bước tiếp theo</h2>
<p>Gửi cho chúng tôi thông tin hạng mục, loại gỗ và tình trạng hiện tại — chúng tôi sẽ đề xuất cấu hình cụ thể và gửi mẫu thử để bạn kiểm chứng trên chính vật liệu của mình.</p>
<p><strong>Hotline: [SỐ ĐIỆN THOẠI]</strong> · Hồ sơ TDS/MSDS cung cấp đầy đủ cho khách hàng doanh nghiệp.</p>
HTML;

        $content .= $this->relatedLinks([
            ['href' => '/post/go-ngoai-troi-vi-sao-moi-lop-son-deu-bong-va-giai-phap-nam-o-dau', 'label' => 'Gỗ ngoài trời: vì sao mọi lớp sơn đều bong, và giải pháp nằm ở đâu'],
            ['href' => '/post/quy-trinh-thi-cong-chuan-tu-chuan-bi-be-mat-den-nghiem-thu-qc', 'label' => 'Quy trình thi công chuẩn: từ chuẩn bị bề mặt đến nghiệm thu QC'],
            ['href' => '/category/ngoai-that', 'label' => 'Xem sản phẩm — Ngoại thất'],
        ]);

        $contentEn = <<<'HTML'
<p><em>Editorial note: replace <code>[CLEAR LINE]</code>, <code>[TINTED LINE]</code>, <code>[PHONE NUMBER]</code> before publishing. The "Reference projects" section at the end lists projects from the source brand's reference material — only keep it if you are an authorized distributor with permission to use those project names; otherwise remove it and replace with your own.</em></p>

<p>Outdoor wood covers very different use cases: a resort deck under constant foot traffic is a different problem from a 200-year-old temple column. This article is organised by category so you can find the right configuration for your project.</p>

<h2>1. Outdoor decking</h2>
<p><strong>Working conditions:</strong> direct sun all day, standing rainwater, constant foot traffic, chairs and luggage dragged across the surface, pool chemicals.</p>
<p><strong>Main problem:</strong> mechanical abrasion combined with UV fading — decking is the fastest-failing element on any outdoor wood project.</p>
<p><strong>Recommended configuration:</strong> [CLEAR LINE] if the wood grain is attractive and worth keeping; [TINTED LINE] when colour needs to be consistent across replacement boards. 4 wet coats, target DFT at the upper end of the range: 2.0–2.5 mil. Apply with a purpose-built applicator pad for large areas, or HVLP where a very flat finish is required.</p>
<p><strong>Acceptance criteria:</strong> Taber abrasion loss 8.4–19.85 mg per ASTM D4060, minimum pencil hardness 4H.</p>
<p><strong>Application note:</strong> keep off the surface for 48 hours; no heavy furniture or cart traffic for the first 7 days.</p>

<h2>2. Facades and outdoor wood cladding</h2>
<p><strong>Working conditions:</strong> large-area direct sun exposure, wind, wind-driven rain, constant day/night humidity swings.</p>
<p><strong>Main problem:</strong> keeping colour uniform across a large surface without peeling — a single patch of fading ruins the look of an entire facade viewed from tens of metres away.</p>
<p><strong>Recommended configuration:</strong> [CLEAR LINE] to preserve the natural material, or [TINTED LINE] when colour needs to match a design tone. Apply each full elevation in a single day from the same product batch to avoid batch-to-batch colour drift.</p>
<p><strong>Deciding metric:</strong> ΔE = 0.63 after 4,000 hours of Xenon exposure (ASTM G155) and gloss retention above 99% after 1,500 hours of QUV.</p>
<p><strong>Application note:</strong> check the dew point before spraying — east-facing elevations in early morning are prone to condensation, which causes adhesion defects.</p>

<h2>3. Railings, staircases, outdoor pergolas</h2>
<p><strong>Working conditions:</strong> constant hand contact, direct sun and rain, plus sea-salt exposure at coastal resorts.</p>
<p><strong>Main problem:</strong> balancing weather durability with a pleasant hand-feel — this is the category with the most direct user contact.</p>
<p><strong>Recommended configuration:</strong> [TINTED LINE] for restoring already-degraded colour; [CLEAR LINE] for new wood. Adjust gloss level to design intent — high gloss is easier to clean, matte feels more natural.</p>
<p><strong>Deciding metric:</strong> 4,000–5,000 hours salt resistance (ASTM B117) for coastal projects.</p>

<h2>4. Docks, marinas, saltwater-exposed elements</h2>
<p><strong>Working conditions:</strong> the harshest in this group — constant sea salt, near-saturation humidity, UV reflected off the water surface adds to the total dose received.</p>
<p><strong>Main problem:</strong> salt corrosion combined with constant moisture-driven decay.</p>
<p><strong>Recommended configuration:</strong> DFT at the upper end of the recommended range. Prioritise vapour permeability — a sealed film fails within a single season in this environment.</p>
<p><strong>Deciding metric:</strong> 4,000–5,000 hours salt spray with no blistering (ASTM B117 / SASO ISO 11997); 240 hours water immersion at 50 °C with no colour change (ISO 2812-2).</p>

<h2>5. Temples, wooden churches, heritage religious architecture</h2>
<p><strong>Working conditions:</strong> wood structures decades to centuries old, most of their natural resin already gone, often already faded or locally decayed.</p>
<p><strong>Main problem:</strong> restoring without altering the material's character, avoiding toxic chemicals in spaces used regularly by people, and preserving the aged appearance.</p>
<p><strong>Recommended configuration:</strong> [CLEAR LINE] is the default choice. Always test on a hidden area first. Sand only the minimum needed — the goal is an adhesion profile, not resurfacing.</p>
<p>To make the case to heritage management: VOC 156 g/L, silicone-free, over 95% biodegradable, and a non-film-forming penetrating mechanism means the intervention is as reversible as this category of treatment gets, compared with surface coatings.</p>

<h2>6. Conservation-grade wood relics and artifacts</h2>
<p><strong>Working conditions:</strong> irreplaceable material value, every intervention must be carefully weighed.</p>
<p><strong>Main problem:</strong> preserving material integrity — this is the category where mistakes cannot be undone.</p>
<p><strong>Recommended configuration:</strong> [CLEAR LINE] only. The process must include before/after photo documentation and testing on an equivalent sample. Coordinate with a conservation specialist — this article does not replace professional heritage-conservation assessment.</p>

<h2>7. Engineered and mixed-grade wood used outdoors</h2>
<p><strong>Working conditions:</strong> base material with naturally low durability, faint grain, pale colour.</p>
<p><strong>Main problem:</strong> upgrading appearance while extending service life — a margin problem for the installer.</p>
<p><strong>Recommended configuration:</strong> [TINTED LINE]. Nano colour particles penetrate into the pores, making the grain stand out rather than covering it, giving low-grade wood an aged-hardwood look. Outdoor MDF and plywood need careful treatment of cut edges — the point of highest moisture uptake.</p>

<h2>Quick reference table</h2>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Category</th><th>Recommended line</th><th>Deciding metric</th></tr></thead>
<tbody>
<tr><td>Decking</td><td>Clear or tinted</td><td>Taber abrasion, 4H hardness</td></tr>
<tr><td>Facade</td><td>Clear or tinted</td><td>ΔE &lt; 1 after Xenon, QUV gloss retention</td></tr>
<tr><td>Railings, pergola</td><td>Tinted (restoration) / clear (new wood)</td><td>Salt resistance, surface feel</td></tr>
<tr><td>Docks</td><td>Prioritise vapour permeability</td><td>4,000–5,000 h salt spray, water immersion</td></tr>
<tr><td>Temples</td><td>Clear</td><td>Low VOC, non-toxic, preserves aged look</td></tr>
<tr><td>Heritage relics</td><td>Clear</td><td>Material integrity</td></tr>
<tr><td>Engineered wood</td><td>Tinted</td><td>Aesthetic upgrade + cut-edge treatment</td></tr>
</tbody>
</table>

<h2>Reference projects</h2>
<p><strong>Editorial warning — read before publishing:</strong> the list below comes from the source brand's reference material in the wood-industry documentation, not your own projects. Only publish it if you are an authorized distributor with written permission to use these project names. Otherwise, remove this section entirely and replace it with your own projects.</p>
<table border="1" cellpadding="6" cellspacing="0">
<thead><tr><th>Project</th><th>Category</th><th>Work performed</th></tr></thead>
<tbody>
<tr><td>Amanoi, Ninh Thuan</td><td>Outdoor decking</td><td>Restoration and protection of decking faded by sun and sea wind</td></tr>
<tr><td>Hoiana, Quang Nam</td><td>Outdoor decking</td><td>Surface protection for high-traffic wood areas</td></tr>
<tr><td>InterContinental, Da Nang</td><td>Outdoor railings, staircases</td><td>Restoration using nano tinting technology, salt resistant</td></tr>
<tr><td>Van Don International Airport</td><td>Outdoor wood seating</td><td>Surface treatment and protection</td></tr>
<tr><td>Cam My wooden church, Dong Nai</td><td>~200-year-old wood structure</td><td>Restoration of a deteriorated structure</td></tr>
<tr><td>Dinh–Le Kings Temple</td><td>Heritage wood structure</td><td>Conservation, material integrity</td></tr>
<tr><td>Chien Dan Cham Towers</td><td>Heritage wood elements</td><td>Conservation</td></tr>
<tr><td>Bo Thao Duoc Restaurant, Ha Giang</td><td>Mixed-grade wood interior</td><td>Nano tinting for an aged-hardwood look</td></tr>
<tr><td>Projects in Belgium and Denmark</td><td>Wood facade</td><td>Colour retention and anti-peeling on elevations</td></tr>
</tbody>
</table>

<h2>Next step</h2>
<p>Send us your project details, wood species and current condition — we'll propose a specific configuration and send a sample so you can verify it on your own material.</p>
<p><strong>Hotline: [PHONE NUMBER]</strong> · Full TDS/MSDS documentation provided for business customers.</p>
HTML;

        $contentEn .= $this->relatedLinksEn([
            ['href' => '/post/outdoor-wood-coating-solution-uv-resistant', 'label' => 'Outdoor Wood: Why Every Paint Job Peels, and Where the Real Solution Is'],
            ['href' => '/post/application-process-nano-coating-on-wood', 'label' => 'Standard Application Process: From Surface Prep to QC Sign-Off'],
            ['href' => '/category/exterior', 'label' => 'View Products — Exterior'],
        ]);

        return [
            'group' => 'giai-phap',
            'slug' => 'giai-phap-nano-theo-tung-hang-muc-go-ngoai-troi',
            'title' => 'Giải pháp nano theo từng hạng mục gỗ ngoài trời',
            'excerpt' => 'Sàn deck, facade gỗ, lan can, cầu cảng, đình chùa, di tích — mỗi hạng mục gỗ ngoài trời có một bài toán riêng và cấu hình phủ nano tương ứng.',
            'meta_title' => 'Sơn sàn gỗ ngoài trời',
            'meta_description' => 'Sàn deck, facade gỗ, lan can, cầu cảng, đình chùa, di tích - mỗi hạng mục gỗ ngoài trời có một bài toán riêng. Giải pháp phủ nano tương ứng cho từng loại.',
            'content' => $content,
            'slug_en' => 'nano-solutions-by-outdoor-wood-category',
            'title_en' => 'Nano Solutions by Outdoor Wood Category',
            'excerpt_en' => 'Decking, wood facades, railings, docks, temples, heritage relics — every outdoor wood category has its own problem and matching nano coating configuration.',
            'meta_title_en' => 'Outdoor Wood Deck Coating',
            'meta_description_en' => 'Decking, wood facades, railings, docks, temples, heritage relics - every outdoor wood category has its own problem. Matching nano coating solutions for each.',
            'content_en' => $contentEn,
        ];
    }
}
