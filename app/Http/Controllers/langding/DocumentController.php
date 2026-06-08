<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    private const BASE_PATH = 'uploads/documents/posts';

    /**
     * Slug root danh mục thông tin đăng kiểm trong DB.
     */
    private const ROOT_CATEGORY_SLUG = 'thong-tin-dang-kiem';
    /**
     * Các folder đặc biệt: vào thẳng là hiện file, không duyệt folder con.
     */
    private const DIRECT_FILE_FOLDERS = [
        'catalog',
        'tai-lieu-khac'
    ];
    /**
     * Fallback map khi DB chưa có dữ liệu đầy đủ.
     * Key   = slug dùng trong URL  (/document/sam-lop-o-to-tai)
     * Value = slug dài trong DB/storage (thong-tin-dang-kiem-sam-lop-o-to-tai)
     */
    private const DEFAULT_FOLDER_MAP = [
        'sam-lop-o-to-tai'    => 'thong-tin-dang-kiem-sam-lop-o-to-tai',
        'sam-lop-xe-ap'       => 'thong-tin-dang-kiem-sam-lop-xe-dap',
        'sam-lop-xe-may'      => 'thong-tin-dang-kiem-sam-lop-xe-may',
        'lop-avenza-pcr'      => 'thong-tin-dang-kiem-lop-avenza-pcr',
        'sam-lop-xe-ien'      => 'thong-tin-dang-kiem-sam-lop-xe-dien',
        'sam-lop-chuyen-dung' => 'thong-tin-dang-kiem-sam-lop-chuyen-dung',
    ];

    public function index(Request $request, string $path = '')
    {
        $path     = rawurldecode($path);
        $path     = $this->sanitizePath($path);
        $segments = $path !== '' ? explode('/', $path) : [];
        $locale   = app()->getLocale();

        // Resolve path thực tế trong storage
        $realPath = $this->resolveRealPath($segments);
        $fullPath = $this->absPath($realPath);

        if ($path !== '' && !is_dir($fullPath)) {
            return redirect()->route('home')->with('toast_error', __('messages.document_not_found'));
        }

        $breadcrumb = $this->buildBreadcrumb($segments, $locale);

        /**
         * Nếu là folder đặc biệt như /document/catalog
         * thì bỏ qua logic hiện subfolder, lấy file luôn.
         */
        $isDirectFileFolder = count($segments) === 1
            && in_array($segments[0], self::DIRECT_FILE_FOLDERS, true);

        if (!$isDirectFileFolder) {
            $subFolders = empty($segments)
                ? $this->getRootFolders($locale)
                : $this->getSubFolders($fullPath, $path);

            if (!empty($subFolders)) {
                return view('langding.documents', [
                    'folders'            => $subFolders,
                    'parentCategory'     => $this->currentCategory($segments, $locale),
                    'childCategory'      => null,
                    'dangKiemCategories' => [],
                    'dangKiemCategory'   => null,
                    'breadcrumb'         => $breadcrumb,
                    'isFolderView'       => true,
                    'parentSlug'         => $path,
                ]);
            }
        }

        $files = $this->getPdfFiles($fullPath);

        return view('langding.documents', [
            'folders'            => null,
            'parentCategory'     => $this->currentCategory($segments, $locale),
            'childCategory'      => null,
            'dangKiemCategories' => $files,
            'dangKiemCategory'   => [
                'name' => $this->getCategoryNameFromDb(end($segments) ?: '', $locale)
                    ?: $this->folderDisplayName(end($segments) ?: 'Tài liệu'),
                'slug' => $path,
            ],
            'breadcrumb'         => $breadcrumb,
            'isFolderView'       => false,
            'parentSlug'         => $path,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Root folders (cấp 1) — dùng FOLDER_MAP, tên lấy từ DB
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Hiển thị folder cấp 1 tại /document.
     * Chỉ hiện folder nào thực sự tồn tại trong storage.
     */
    private function getRootFolders(string $locale): array
    {
        $folders = [];
        $map     = $this->getFolderMap($locale);

        foreach ($map as $urlSlug => $realFolder) {
            $fullPath = $this->absPath($realFolder);

            // Bỏ qua nếu folder không tồn tại trong storage
            if (!is_dir($fullPath)) {
                continue;
            }

            // Ưu tiên tên từ DB, fallback về slug ngắn
            $name = $this->getCategoryNameFromDb($urlSlug, $locale)
                ?: $this->folderDisplayName($urlSlug);

            $folders[] = [
                'id'         => null,
                'parent_id'  => null,
                'sort_order' => 0,
                'name'       => $name,
                'slug'       => $urlSlug, // dùng slug ngắn trong URL
            ];
        }

        return $folders;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sub folders (cấp 2+) — tên folder thực tế trong storage
    // ─────────────────────────────────────────────────────────────────────────

    private function getSubFolders(string $fullPath, string $currentPath): array
    {
        if (!is_dir($fullPath)) {
            return [];
        }

        $dirs = array_filter(glob($fullPath . '/*'), 'is_dir');

        return array_values(array_map(function (string $dir) use ($currentPath) {
            $name            = basename($dir);
            $encodedName     = rawurlencode($name);
            $encodedCurrent  = $currentPath !== ''
                ? implode('/', array_map('rawurlencode', explode('/', $currentPath)))
                : '';

            $slug = ($encodedCurrent !== '' ? $encodedCurrent . '/' : '') . $encodedName;

            return [
                'id'         => null,
                'parent_id'  => null,
                'sort_order' => 0,
                'name'       => $this->folderDisplayName($name),
                'slug'       => $encodedName,
            ];
        }, $dirs));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Database helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lấy tên danh mục từ DB theo slug và locale.
     * Tìm trong postcategory_translations theo slug.
     */
    private function getCategoryNameFromDb(string $slug, string $locale): ?string
    {
        if ($slug === '') {
            return null;
        }

        // Thử slug ngắn trước (sam-lop-o-to-tai)
        $row = DB::table('postcategory_translations')
            ->where('slug', $slug)
            ->where('language', $locale)
            ->value('name');

        if ($row) {
            return $row;
        }

        // Thử slug dài (thong-tin-dang-kiem-sam-lop-o-to-tai)
        $map      = $this->getFolderMap($locale);
        $realSlug = $map[$slug] ?? null;
        if ($realSlug) {
            $row = DB::table('postcategory_translations')
                ->where('slug', $realSlug)
                ->where('language', $locale)
                ->value('name');

            if ($row) {
                return $row;
            }
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Path helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Segment[0] là slug URL ngắn → đổi thành tên folder thực tế.
     * Segment[1+] giữ nguyên (là tên folder con thực tế).
     */
    private function resolveRealPath(array $segments): string
    {
        if (empty($segments)) {
            return '';
        }

        $realSegments = $segments;

        $map = $this->getFolderMap(app()->getLocale());
        if (isset($segments[0]) && isset($map[$segments[0]])) {
            $realSegments[0] = $map[$segments[0]];
        }

        return implode('/', $realSegments);
    }

    private function absPath(string $path = ''): string
    {
        $base = storage_path('app/public/' . self::BASE_PATH);
        return $path !== '' ? $base . '/' . $path : $base;
    }

    private function getPdfFiles(string $fullPath): array
    {
        if (!is_dir($fullPath)) {
            return [];
        }

        $allFiles = scandir($fullPath);
        $pdfs     = array_filter(
            $allFiles,
            fn($f) => is_file($fullPath . '/' . $f)
                && strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf'
        );

        return array_values(array_map(function (string $fileName) use ($fullPath) {
            $filePath = $fullPath . '/' . $fileName;
            $size     = filesize($filePath);
            $relative = str_replace(storage_path('app/public') . '/', '', $filePath);

            return [
                'id'            => null,
                'title'         => $this->folderDisplayName(pathinfo($fileName, PATHINFO_FILENAME)),
                'slug'          => $fileName,
                'file_url'      => asset('storage/' . $relative),
                'file_name'     => $fileName,
                'file_size'     => $size,
                'file_size_fmt' => $this->formatSize($size),
                'mime_type'     => 'application/pdf',
                'published_at'  => null,
                'image_urls'    => null,
            ];
        }, $pdfs));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Breadcrumb & display helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function buildBreadcrumb(array $segments, string $locale): array
    {
        $items = [
            ['label' => __('messages.documents'), 'url' => url('/document')],
        ];

        $builtPath = '';
        foreach ($segments as $index => $segment) {
            $builtPath .= ($builtPath !== '' ? '/' : '') . rawurlencode($segment);
            $isLast    = $index === count($segments) - 1;

            $label = $this->getCategoryNameFromDb($segment, $locale)
                ?: $this->folderDisplayName($segment);

            $items[] = [
                'label' => $label,
                'url'   => $isLast ? null : url('/document/' . $builtPath),
            ];
        }

        return $items;
    }

    private function currentCategory(array $segments, string $locale): ?object
    {
        if (empty($segments)) {
            return null;
        }

        $last = end($segments);

        return (object) [
            'id'   => null,
            'name' => $this->getCategoryNameFromDb($last, $locale)
                ?: $this->folderDisplayName($last),
            'slug' => implode('/', $segments),
        ];
    }

    private function folderDisplayName(string $name): string
    {
        if ($name !== strtolower($name)) {
            return $name;
        }
        return ucwords(str_replace('-', ' ', $name));
    }

    /**
     * Lấy map slug URL ngắn → slug dài thực tế từ DB.
     * Nếu không tìm được root hoặc không có con, fallback về DEFAULT_FOLDER_MAP.
     */
    private function getFolderMap(string $locale): array
    {
        // Tìm ID root theo slug "thong-tin-dang-kiem"
        $rootId = DB::table('postcategories as c')
            ->join('postcategory_translations as t', 'c.id', '=', 't.postcategory_id')
            ->where('t.slug', self::ROOT_CATEGORY_SLUG)
            ->where('t.language', $locale)
            ->where('c.is_active', true)
            ->value('c.id');

        if (!$rootId) {
            return self::DEFAULT_FOLDER_MAP;
        }

        $rows = DB::table('postcategories as c')
            ->join('postcategory_translations as t', 'c.id', '=', 't.postcategory_id')
            ->where('c.parent_id', $rootId)
            ->where('t.language', $locale)
            ->where('c.is_active', true)
            ->select('t.slug')
            ->get();

        if ($rows->isEmpty()) {
            return self::DEFAULT_FOLDER_MAP;
        }

        $map = [];
        foreach ($rows as $row) {
            $longSlug  = $row->slug; // vd: thong-tin-dang-kiem-sam-lop-o-to-tai
            $shortSlug = Str::after($longSlug, self::ROOT_CATEGORY_SLUG . '-');
            if ($shortSlug === '' || $shortSlug === $longSlug) {
                $shortSlug = $longSlug;
            }
            $map[$shortSlug] = $longSlug;
        }

        // Merge fallback (ưu tiên giá trị từ DB)
        return $map + self::DEFAULT_FOLDER_MAP;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1) . ' MB';
        }
        return number_format($bytes / 1024, 1) . ' KB';
    }

    private function sanitizePath(string $path): string
    {
        $path = str_replace("\0", '', $path);
        $path = preg_replace('/\.\./', '', $path);
        return trim($path, '/');
    }
}
