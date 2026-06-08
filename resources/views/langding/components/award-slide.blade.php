@php
$awardCategoryId = $awardCategoryId ?? null;
$awardPagination = $awardPagination ?? [];
$awardTiles = $awardTiles ?? [];
$useAjaxFeed = !empty($awardCategoryId);
$awardPerPage = (int) ($awardPagination['per_page'] ?? 4);
$awardPosts = isset($awardPosts) ? collect($awardPosts) : collect();
$showBlock = $useAjaxFeed || $awardPosts->isNotEmpty() || count($awardTiles) > 0;
@endphp
@if($showBlock)
<div id="box-award-slide"
    class="box-news-info"
    @if($useAjaxFeed)
    data-tab-feed-root
    data-tab-feed-url="{{ route('langding.tab-feed') }}"
    data-tab-feed-type="award"
    data-tab-feed-per-page="{{ $awardPerPage }}"
    data-tab-feed-category-id="{{ $awardCategoryId }}"
    @endif>
    <div class="container-fluid">
        <div class="font-hanzel fs-32 fw-400 text-center sub-title mb-4">{{ $category['title'] ?? 'Giải thưởng' }}</div>

        <div class="award-gallery-container scroll-animate" data-animate="fadeInUp">
            @if($useAjaxFeed)
                @include('langding.components.award-tab-feed-surface', [
                    'awardTiles' => $awardTiles,
                    'pagination' => $awardPagination,
                ])
            @else
                @php
                $tiles = $awardTiles;
                if ($awardPosts->isNotEmpty()) {
                    $tiles = app(\App\Services\PostService::class)->flattenAwardPostsToImageTiles($awardPosts->toArray());
                }
                @endphp
                @include('langding.components.award-tab-feed-surface', [
                    'awardTiles' => $tiles,
                    'pagination' => ['current_page' => 1, 'last_page' => 1],
                ])
            @endif
        </div>
    </div>
</div>

<style>
    .award-gallery-item:hover .award-overlay {
        opacity: 1 !important;
    }

    .award-gallery-item:hover .award-image {
        transform: scale(1.05);
    }

    .award-gallery-item:hover .award-thumbnail {
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
        transform: translateY(-4px);
    }

    .award-gallery-item {
        cursor: pointer;
        display: block;
        transition: all 0.3s ease;
    }

    .award-gallery-item:focus-visible {
        outline: 2px solid rgba(220, 53, 69, 0.6);
        outline-offset: 3px;
        border-radius: 12px;
    }

    .award-thumbnail {
        position: relative;
    }

    .award-caption {
        padding: 0 4px;
    }

    .fancybox__container {
        --fb-bg: rgba(0, 0, 0, 0.95);
    }

    .fancybox__button {
        color: #fff;
    }

    .fancybox__button:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .fancybox__caption {
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        padding: 12px 16px;
        text-align: center;
    }
</style>

<script>
    // Fancybox binding + rebind sau AJAX đã được handle ở public/langding/js/tab-feed.js
</script>
@endif
