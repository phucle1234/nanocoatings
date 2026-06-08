{{-- Một surface: nội dung + pager (AJAX thay cả khối này) --}}
<div class="js-tab-feed-surface" data-tab-feed-surface="1">
    @include('langding.components.news-tab-feed-body', ['posts' => $posts ?? []])
    @php
        $lastPage = (int) ($pagination['last_page'] ?? 1);
        $currentPage = (int) ($pagination['current_page'] ?? 1);
    @endphp
    @if($lastPage > 1)
    <div class="js-tab-feed-pager d-flex justify-content-center align-items-center gap-2 flex-wrap mt-3 mb-2">
        <button type="button" class="btn btn-outline-secondary btn-sm js-tab-feed-prev" {{ $currentPage <= 1 ? 'disabled' : '' }} aria-label="Trước">‹</button>
        <span class="font-hanzel fs-14 px-2 js-tab-feed-page-label">{{ $currentPage }} / {{ $lastPage }}</span>
        <button type="button" class="btn btn-outline-secondary btn-sm js-tab-feed-next" {{ $currentPage >= $lastPage ? 'disabled' : '' }} aria-label="Sau">›</button>
    </div>
    @endif
</div>
