    <div id="box-media" class="box-media"
        data-tab-feed-root
        data-tab-feed-url="{{ route('langding.tab-feed') }}"
        data-tab-feed-type="news"
        data-tab-feed-per-page="4" style="background: url('{{ asset('langding_nano/imgs/Slection8.png') }}') no-repeat center center;background-size: cover;">
        <div class="container-fluid">
            <div class="scroll-animate" data-animate="fadeInUp">
                <div class="title-with-line fw-500 fs-18 text-center text-light-red">{{ __('messages.media') }}</div>
                <h2 class="font-hanzel fs-32 mt-3 fw-400 text-center text-white">{{ __('messages.news_and_events') }}</h2>
            </div>
            <div class="tabs-scroll-wrap">
                <ul class="nav nav-tabs tabs-scroll" id="box-media-title" role="tablist">
                    @if (isset($newsCategories) && $newsCategories->count() > 0)
                        @foreach ($newsCategories as $index => $category)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $index == 0 ? 'active' : '' }} fs-14 font-hanzel"
                                    id="media-{{ $category->id }}" data-bs-toggle="tab"
                                    data-bs-target="#media-{{ $category->id }}-pane" type="button" role="tab"
                                    aria-controls="media-{{ $category->id }}-pane"
                                    aria-selected="{{ $index == 0 ? 'true' : 'false' }}">
                                    {{ $category->category_name ?? $category->slug }}
                                </button>
                            </li>
                        @endforeach
                    @else
                        <li class="nav-item">
                            <button class="nav-link active fs-14 font-hanzel">{{ __('messages.no_categories') }}</button>
                        </li>
                    @endif
                </ul>
                <span class="tabs-scroll-hint" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="9 6 15 12 9 18"></polyline>
                    </svg>
                </span>
            </div>
            <div class="tab-content" id="box-media-content">
                @if (isset($newsCategories) && $newsCategories->count() > 0)
                    @foreach ($newsCategories as $index => $category)
                        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}"
                            id="media-{{ $category->id }}-pane" role="tabpanel"
                            aria-labelledby="media-{{ $category->id }}" tabindex="0"
                            data-tab-feed-category-id="{{ $category->id }}">
                            <div class="box-media-slider">
                                @include('langding.components.news-tab-feed-surface', [
                                    'posts' => $category->feed_posts ?? [],
                                    'pagination' => $category->feed_pagination ?? [],
                                ])
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-newspaper fa-3x text-muted"></i>
                        </div>
                        <h3 class="font-hanzel fs-32">{{ __('messages.no_news') }}</h3>
                        <p class="fs-18">{{ __('messages.no_news_available') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
