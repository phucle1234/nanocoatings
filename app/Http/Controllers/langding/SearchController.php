<?php

namespace App\Http\Controllers\langding;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword  = trim((string) $request->get('q', ''));
        $language = app()->getLocale();
        $perPage  = 12;
        $page     = max((int) $request->get('page', 1), 1);

        $results = collect();
        $pagination = null;
        $total = 0;

        if (mb_strlen($keyword) >= 2) {
            $normalizedKeyword = $this->normalizeKeyword($keyword);

            $cacheKey = 'search:landing:' . md5(json_encode([
                'q'    => $normalizedKeyword,
                'lang' => $language,
                'page' => $page,
            ], JSON_UNESCAPED_UNICODE));

            $cached = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($normalizedKeyword, $language, $perPage, $page) {
                return $this->runSearch($normalizedKeyword, $language, $perPage, $page);
            });

            $results = collect($cached['items']);
            $total   = (int) $cached['total'];

            $pagination = [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
            ];
        }

        if ($request->expectsJson()) {
            return response()->json([
                'keyword'    => $keyword,
                'results'    => $results,
                'pagination' => $pagination,
                'total'      => $total,
            ]);
        }

        return view('langding.search-result', compact(
            'keyword',
            'results',
            'pagination',
            'total'
        ));
    }

    protected function runSearch(string $keyword, string $language, int $perPage, int $page): array
    {
        $escapedKeyword = $this->escapeLike($keyword);

        $prefix  = $escapedKeyword . '%';
        $contain = '%' . $escapedKeyword . '%';

        $quotedPrefix  = DB::getPdo()->quote($prefix);
        $quotedContain = DB::getPdo()->quote($contain);

        $productQuery = DB::table('product_translations as pt')
            ->join('products as p', 'p.id', '=', 'pt.product_id')
            ->where('pt.language', $language)
            ->where(function ($q) use ($prefix, $contain) {
                $q->where('pt.name', 'LIKE', $prefix)
                    ->orWhere('pt.name', 'LIKE', $contain)
                    ->orWhere('pt.text_search', 'LIKE', $prefix)
                    ->orWhere('pt.text_search', 'LIKE', $contain);
            })
            ->select([
                DB::raw("'product' as type"),
                'pt.product_id as source_id',
                'pt.name as title',
                'pt.slug as slug',
                'pt.short_description as excerpt',
                'p.image_urls as image_urls',
                DB::raw('NULL as post_image_urls'),
                'p.created_at as created_at',
                DB::raw("
                    CASE
                        WHEN pt.name LIKE {$quotedPrefix} THEN 100
                        WHEN pt.name LIKE {$quotedContain} THEN 85
                        WHEN pt.text_search LIKE {$quotedPrefix} THEN 70
                        WHEN pt.text_search LIKE {$quotedContain} THEN 55
                        ELSE 0
                    END as priority
                "),
            ]);

        $postQuery = DB::table('post_translations as pot')
            ->join('posts as po', 'po.id', '=', 'pot.post_id')
            ->where('pot.language', $language)
            ->where('po.is_active', 1)
            ->where(function ($q) use ($prefix, $contain) {
                $q->where('pot.title', 'LIKE', $prefix)
                    ->orWhere('pot.title', 'LIKE', $contain)
                    ->orWhere('pot.excerpt', 'LIKE', $prefix)
                    ->orWhere('pot.excerpt', 'LIKE', $contain);
            })
            ->selectRaw("
                'post' as type,
                pot.post_id as source_id,
                MIN(pot.title) as title,
                MIN(pot.slug) as slug,
                MIN(pot.excerpt) as excerpt,
                NULL as image_urls,
                MIN(pot.image_urls) as post_image_urls,
                MAX(po.created_at) as created_at,
                MAX(
                    CASE
                        WHEN pot.title LIKE {$quotedPrefix} THEN 60
                        WHEN pot.title LIKE {$quotedContain} THEN 45
                        WHEN pot.excerpt LIKE {$quotedPrefix} THEN 30
                        WHEN pot.excerpt LIKE {$quotedContain} THEN 20
                        ELSE 0
                    END
                ) as priority
            ")
            ->groupBy('pot.post_id');

        $union = $productQuery->unionAll($postQuery);

        $baseQuery = DB::query()->fromSub($union, 'search_results');

        $total = (clone $baseQuery)->count();

        $items = $baseQuery
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->forPage($page, $perPage)
            ->get()
            ->map(function ($item) {
                $item->detail_url = $item->type === 'product'
                    ? route('product.detail', ['slug' => $item->slug ?? '#'])
                    : route('post.detail', ['slug' => $item->slug ?? '#']);

                return $item;
            })
            ->values()
            ->all();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    protected function normalizeKeyword(string $keyword): string
    {
        $keyword = preg_replace('/\s+/u', ' ', $keyword);
        return trim($keyword);
    }

    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
