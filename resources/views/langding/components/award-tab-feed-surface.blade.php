<div class="js-tab-feed-surface js-award-feed-surface" data-tab-feed-surface="1">
    <div class="row g-3 g-md-4">
        @forelse(($awardTiles ?? []) as $tile)
            @php
                $tile = is_array($tile) ? $tile : (array) $tile;
                $imageUrl = $tile['image_url'] ?? '';
                $title = $tile['title'] ?? '';
                $content = $tile['content'] ?? '';
                $showCaption = !empty($tile['show_caption']);
            @endphp
            <div class="col-12 col-sm-4 col-lg-3">
                <a href="{{ $imageUrl }}"
                    data-fancybox="award-gallery"
                    data-caption="{{ $title }}"
                    class="award-gallery-item text-decoration-none">
                    <div class="award-thumbnail position-relative d-flex align-items-center justify-content-center"
                        style="height: 400px; background: linear-gradient(135deg, #fafbfc 0%, #f0f3f7 100%); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); border: 1px solid rgba(0, 0, 0, 0.05); transition: all 0.4s cubic-bezier(0.33, 0.66, 0.66, 1);">
                        <img src="{{ $imageUrl }}"
                            alt="{{ $title }}"
                            style="width: 100%; height: 100%; object-fit: contain; padding: 16px; transition: transform 0.4s cubic-bezier(0.33, 0.66, 0.66, 1);"
                            class="img-fluid award-image">
                        <div class="award-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                            style="background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.4) 100%); border-radius: 12px; opacity: 0; transition: opacity 0.4s cubic-bezier(0.33, 0.66, 0.66, 1);">
                            <div class="text-center text-white">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3 mx-auto" style="filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <p class="mb-0 fw-600 fs-14" style="letter-spacing: 0.3px; text-transform: uppercase;">{{ __('messages.view_larger') }}</p>
                            </div>
                        </div>
                    </div>
                    @if($showCaption && !empty($content))
                        <div class="award-caption mt-3 text-center">
                            <p class="fw-500 fs-13 text-dark mb-0"
                                style="min-height: 2.8em; display: -webkit-box; line-clamp: 2; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.6; letter-spacing: 0.2px;">
                                {!! Str::limit(strip_tags($content), 60) !!}
                            </p>
                        </div>
                    @endif
                </a>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center font-hanzel fs-18 py-4 mb-0">{{ __('messages.no_posts') }}</p>
            </div>
        @endforelse
    </div>
    @php
        $lastPage = (int) ($pagination['last_page'] ?? 1);
        $currentPage = (int) ($pagination['current_page'] ?? 1);
    @endphp
    @if($lastPage > 1)
    <div class="js-tab-feed-pager d-flex justify-content-center align-items-center gap-2 flex-wrap mt-4 pb-2">
        <button type="button" class="btn btn-outline-secondary btn-sm js-tab-feed-prev" {{ $currentPage <= 1 ? 'disabled' : '' }} aria-label="Trước">‹</button>
        <span class="font-hanzel fs-14 px-2 js-tab-feed-page-label">{{ $currentPage }} / {{ $lastPage }}</span>
        <button type="button" class="btn btn-outline-secondary btn-sm js-tab-feed-next" {{ $currentPage >= $lastPage ? 'disabled' : '' }} aria-label="Sau">›</button>
    </div>
    @endif
</div>
