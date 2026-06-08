<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\PostCategoryApiController;
use App\Models\PostCategory;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TabFeedController extends Controller
{
    public function __construct(
        protected PostCategoryApiController $postCategoryApiController,
        protected PostService $postService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:news,award',
            'category_id' => 'required|integer|min:1',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:30',
            'all' => 'nullable|boolean',
        ]);

        if (! PostCategory::query()->where('id', $validated['category_id'])->where('is_active', true)->exists()) {
            return response()->json(['success' => false, 'message' => 'Danh mục không hợp lệ'], 404);
        }

        $type = $validated['type'];
        // award per_page phải khớp initial load ở AboutController (AWARD_IMAGES_PER_PAGE = 4)
        $perPage = (int) ($request->get('per_page') ?: ($type === 'award' ? 4 : 4));
        $page = (int) ($request->get('page', 1));

        if ($type === 'award') {
            if ((bool) ($validated['all'] ?? false) === true) {
                $tiles = $this->postService->getAwardGalleryAllTiles(
                    (int) $validated['category_id'],
                    app()->getLocale()
                );

                return response()->json([
                    'success' => true,
                    'tiles' => $tiles,
                ]);
            }

            $award = $this->postService->getAwardGalleryImagePage(
                (int) $validated['category_id'],
                $page,
                $perPage,
                app()->getLocale()
            );
            $html = view('langding.components.award-tab-feed-surface', [
                'awardTiles' => $award['tiles'],
                'pagination' => $award['pagination'],
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $award['pagination'],
            ]);
        }

        $sub = $request->duplicate();
        $sub->merge([
            'sort_by' => 'sort_order',
            'per_page' => $perPage,
            'page' => $page,
        ]);

        $response = $this->postCategoryApiController->posts($sub, $validated['category_id']);
        $data = json_decode($response->getContent(), true);

        if (empty($data['success'])) {
            return response()->json([
                'success' => false,
                'message' => $data['message'] ?? 'Lỗi tải dữ liệu',
            ], 422);
        }

        $posts = $data['data']['posts'] ?? [];
        $pagination = $data['data']['pagination'] ?? [];

        $html = view('langding.components.news-tab-feed-surface', [
            'posts' => $posts,
            'pagination' => $pagination,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'pagination' => $pagination,
        ]);
    }
}
