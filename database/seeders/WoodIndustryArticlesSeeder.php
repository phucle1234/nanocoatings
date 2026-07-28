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
 * sector's own news category (sector-vat-lieu-go-news-chung), auto-created
 * if missing. Idempotent: re-running updates existing posts (matched by
 * slug) instead of duplicating them.
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

        $newsHubSlug = $sectorService->getNewsHubSlug($sector, 'vi');
        $childCategorySlugVi = $newsHubSlug.'-chung';

        $newsCategory = PostCategory::withoutGlobalScopes()
            ->whereHas('translations', fn ($q) => $q->where('slug', $childCategorySlugVi))
            ->first();

        if (! $newsCategory) {
            // Sector already had a news hub with other, non-default
            // children — fall back to attaching directly to the hub itself.
            $newsCategory = PostCategory::withoutGlobalScopes()
                ->whereHas('translations', fn ($q) => $q->where('slug', $newsHubSlug))
                ->first();
        }

        if (! $newsCategory) {
            Log::warning('WoodIndustryArticlesSeeder: could not resolve or create a news category for the sector — skipped.');
            $this->command?->error('Không tạo được danh mục tin tức cho ngành. Vào trang layout của ngành 1 lần rồi chạy lại seeder.');

            return;
        }

        foreach ($this->articles() as $article) {
            $this->seedArticle($newsCategory->id, $article);
        }

        $this->command?->info('Đã seed 6 bài viết ngành Vật liệu gỗ vào category id '.$newsCategory->id.' (status: draft — cần thay placeholder rồi mới publish).');
    }

    /**
     * @param  array{slug: string, title: string, excerpt: string, meta_title: string, meta_description: string, content: string}  $article
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

        if (! $post->postcategories()->where('postcategories.id', $categoryId)->exists()) {
            $post->postcategories()->attach($categoryId, ['is_primary' => true, 'sort_order' => 0]);
        }

        $post->handleTranslations([
            'title_vi' => $article['title'],
            'content_vi' => $article['content'],
            'excerpt_vi' => $article['excerpt'],
            'meta_title_vi' => $article['meta_title'],
            'meta_description_vi' => $article['meta_description'],
        ]);

        // Post::handleTranslations() always derives slug from Str::slug(title),
        // which won't match the exact SEO slug specified in the sitemap plan
        // — overwrite it directly with the intended one.
        $post->translations()->where('language', 'vi')->update(['slug' => $article['slug']]);
    }

    /**
     * @return array<int, array{slug: string, title: string, excerpt: string, meta_title: string, meta_description: string, content: string}>
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
            ['href' => '/post/thong-so-ky-thuat-lop-phu-nano-cho-go', 'label' => 'Hồ sơ thông số kỹ thuật & tiêu chuẩn kiểm định'],
            ['href' => '/post/giai-phap-phu-go-ngoai-troi-khang-uv', 'label' => 'Giải pháp phủ gỗ ngoài trời: kháng UV và để gỗ thở được'],
            ['href' => '/applications/vat-lieu-go', 'label' => 'Trang trụ: Vật liệu gỗ'],
        ]);

        return [
            'slug' => 'co-che-bao-ve-go-cua-lop-phu-nano',
            'title' => 'Vì sao lớp phủ nano bảo vệ được gỗ mà sơn tạo màng thì không',
            'excerpt' => 'Sơn PU, vecni và dầu lau tạo màng kín và nhốt ẩm trong gỗ. Lớp phủ nano thẩm thấu vào thớ gỗ, kháng UV và vẫn cho gỗ thở được.',
            'meta_title' => 'Cơ chế lớp phủ nano bảo vệ gỗ',
            'meta_description' => 'Vì sao sơn tạo màng làm gỗ mục nhanh hơn, và lớp phủ nano thẩm thấu giải quyết vấn đề ở tầng khác. Cơ chế, số liệu kiểm định và so sánh chi tiết.',
            'content' => $content,
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
            ['href' => '/post/quy-trinh-thi-cong-lop-phu-nano-tren-go', 'label' => 'Quy trình thi công chuẩn: từ chuẩn bị bề mặt đến nghiệm thu QC'],
            ['href' => '/post/gioi-thieu-dong-san-pham-nano-cho-go', 'label' => 'Giới thiệu dòng sản phẩm nano cho vật liệu gỗ'],
            ['href' => '/document/catalog', 'label' => 'Catalog & tài liệu kỹ thuật'],
        ]);

        return [
            'slug' => 'thong-so-ky-thuat-lop-phu-nano-cho-go',
            'title' => 'Hồ sơ thông số kỹ thuật & tiêu chuẩn kiểm định lớp phủ nano cho gỗ',
            'excerpt' => 'Bảng đầy đủ thông số hiệu năng, độ dày màng, định mức, bảo quản và tiêu chuẩn chứng nhận — dùng làm tài liệu bán hàng B2B.',
            'meta_title' => 'Thông số kỹ thuật sơn nano gỗ',
            'meta_description' => 'TDS đầy đủ: độ bóng, độ cứng, mài mòn Taber, kháng UV, kháng muối, VOC, định mức thi công và tiêu chuẩn ASTM/ISO cho lớp phủ nano gỗ.',
            'content' => $content,
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
            ['href' => '/post/thong-so-ky-thuat-lop-phu-nano-cho-go', 'label' => 'Hồ sơ thông số kỹ thuật & tiêu chuẩn kiểm định'],
            ['href' => '/post/giai-phap-nano-theo-hang-muc-go-ngoai-troi', 'label' => 'Giải pháp nano theo từng hạng mục gỗ ngoài trời'],
            ['href' => '/contact', 'label' => 'Đặt mẫu thử miễn phí'],
        ]);

        return [
            'slug' => 'quy-trinh-thi-cong-lop-phu-nano-tren-go',
            'title' => 'Quy trình thi công chuẩn: từ chuẩn bị bề mặt đến nghiệm thu QC',
            'excerpt' => '7 bước thi công chuẩn cho đội thợ: chuẩn bị bề mặt, điều kiện môi trường, thiết bị, thời gian khô, xử lý sự cố và nghiệm thu QC.',
            'meta_title' => 'Quy trình thi công sơn nano trên gỗ',
            'meta_description' => 'Quy trình 7 bước cho thợ thi công: chuẩn bị bề mặt, định mức sơn nano, thi công HVLP/Airless, thời gian khô, xử lý lỗi và nghiệm thu QC.',
            'content' => $content,
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
            ['href' => '/post/co-che-bao-ve-go-cua-lop-phu-nano', 'label' => 'Vì sao lớp phủ nano bảo vệ được gỗ mà sơn tạo màng thì không'],
            ['href' => '/post/giai-phap-phu-go-ngoai-troi-khang-uv', 'label' => 'Giải pháp phủ gỗ ngoài trời: kháng UV và để gỗ thở được'],
            ['href' => '/category/noi-that', 'label' => 'Xem sản phẩm — Nội thất'],
            ['href' => '/category/ngoai-that', 'label' => 'Xem sản phẩm — Ngoại thất'],
        ]);

        return [
            'slug' => 'gioi-thieu-dong-san-pham-nano-cho-go',
            'title' => '[THƯƠNG HIỆU] cho vật liệu gỗ: bảo vệ từ bên trong thớ gỗ',
            'excerpt' => 'Dòng phủ nano cho gỗ nội và ngoại thất — thẩm thấu vào thớ gỗ, kháng UV, giữ vân gỗ tự nhiên, gỗ vẫn thở được.',
            'meta_title' => 'Sơn nano cho gỗ — bảo vệ từ bên trong',
            'meta_description' => 'Dòng phủ nano cho gỗ nội và ngoại thất - thẩm thấu vào thớ gỗ, kháng UV, giữ vân gỗ tự nhiên, gỗ vẫn thở được. VOC thấp, bảo hành 5–10 năm.',
            'content' => $content,
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
            ['href' => '/post/co-che-bao-ve-go-cua-lop-phu-nano', 'label' => 'Vì sao lớp phủ nano bảo vệ được gỗ mà sơn tạo màng thì không'],
            ['href' => '/post/giai-phap-nano-theo-hang-muc-go-ngoai-troi', 'label' => 'Giải pháp nano theo từng hạng mục gỗ ngoài trời'],
            ['href' => '/post-category/case-study', 'label' => 'Ví dụ thực tế / Case study'],
        ]);

        return [
            'slug' => 'giai-phap-phu-go-ngoai-troi-khang-uv',
            'title' => 'Gỗ ngoài trời: vì sao mọi lớp sơn đều bong, và giải pháp nằm ở đâu',
            'excerpt' => 'Sàn deck bạc màu, lan can bong sơn, facade nứt chân chim — nguyên nhân và giải pháp phủ nano kháng UV cho gỗ ngoài trời, giữ cho gỗ thở được.',
            'meta_title' => 'Sơn gỗ ngoài trời chống UV',
            'meta_description' => 'Sàn deck bạc màu, lan can bong sơn, facade nứt chân chim - nguyên nhân và giải pháp phủ nano kháng UV cho gỗ ngoài trời, giữ cho gỗ thở được.',
            'content' => $content,
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
            ['href' => '/post/giai-phap-phu-go-ngoai-troi-khang-uv', 'label' => 'Gỗ ngoài trời: vì sao mọi lớp sơn đều bong, và giải pháp nằm ở đâu'],
            ['href' => '/post/quy-trinh-thi-cong-lop-phu-nano-tren-go', 'label' => 'Quy trình thi công chuẩn: từ chuẩn bị bề mặt đến nghiệm thu QC'],
            ['href' => '/category/ngoai-that', 'label' => 'Xem sản phẩm — Ngoại thất'],
        ]);

        return [
            'slug' => 'giai-phap-nano-theo-hang-muc-go-ngoai-troi',
            'title' => 'Giải pháp nano theo từng hạng mục gỗ ngoài trời',
            'excerpt' => 'Sàn deck, facade gỗ, lan can, cầu cảng, đình chùa, di tích — mỗi hạng mục gỗ ngoài trời có một bài toán riêng và cấu hình phủ nano tương ứng.',
            'meta_title' => 'Sơn sàn gỗ ngoài trời',
            'meta_description' => 'Sàn deck, facade gỗ, lan can, cầu cảng, đình chùa, di tích - mỗi hạng mục gỗ ngoài trời có một bài toán riêng. Giải pháp phủ nano tương ứng cho từng loại.',
            'content' => $content,
        ];
    }
}
