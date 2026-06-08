@extends('langding.index')

@section('title', isset($parent_category) ? $parent_category['name'] . ' - ' . __('messages.news') :
__('messages.news_list'))

@section('langding_content')
<div class="page-posts-category">
    <div class="box-media"
        data-tab-feed-root
        data-tab-feed-url="{{ route('langding.tab-feed') }}"
        data-tab-feed-type="news"
        data-tab-feed-per-page="4">
        <div class="container-fluid">
            <nav aria-label="breadcrumb" class="mt-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"
                            class="fs-14 text-black">{{ __('messages.home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('post.category') }}"
                            class="fs-14 text-black">{{ __('messages.media') }}</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0)"
                            class="fs-14 text-black">{{ $parent_category['name'] ?? __('messages.news') }}</a></li>
                </ol>
            </nav>
            <h2 class="font-hanzel fs-32 mt-4 fw-400 text-center main-title">
                {{ $parent_category['name'] ?? __('messages.news') }}
            </h2>

            @if (!empty($categories))
            <div class="tabs-scroll-wrap">
                <ul class="nav nav-tabs tabs-scroll" id="box-media-title" role="tablist">
                    @foreach ($categories as $index => $category)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fs-14 font-hanzel {{ $index === 0 ? 'active' : '' }}"
                            id="media-{{ $category['id'] }}" data-bs-toggle="tab"
                            data-bs-target="#media-{{ $category['id'] }}-pane" type="button" role="tab"
                            aria-controls="media-{{ $category['id'] }}-pane" aria-selected="false">
                            {{ strtoupper($category['name']) }}
                        </button>
                    </li>
                    @endforeach
                </ul>
                <span class="tabs-scroll-hint" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="9 6 15 12 9 18"></polyline>
                    </svg>
                </span>
            </div>
            <div class="tab-content" id="box-media-content">
                @if (!empty($categories))
                @foreach ($categories as $category)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                    id="media-{{ $category['id'] }}-pane" role="tabpanel"
                    aria-labelledby="media-{{ $category['id'] }}" tabindex="0"
                    data-tab-feed-category-id="{{ $category['id'] }}">
                    <div class="box-media-slider">
                        @include('langding.components.news-tab-feed-surface', [
                            'posts' => $category['feed_posts'] ?? [],
                            'pagination' => $category['feed_pagination'] ?? [],
                        ])
                    </div>
                </div>
                @endforeach
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="category-search-list" style="margin-top: 50px;">
        <div class="container-fluid">

            <div class="posts-list">
                <div class="row">
                    @if (!empty($PostAll))
                    @foreach ($PostAll as $PostAll)
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="posts-item">
                            <a href="{{ route('post.detail', ['slug' => $PostAll['slug']]) }}"
                                class="link-overlay">
                                <div class="bg-img-cover"
                                    style="background-image: url('{{ $PostAll['image'] ?? asset('images/no-image.png') }}');">
                                </div>
                            </a>

                            <div class="posts-item-content">
                                <div class="d-flex align-items-center gap-2 fw-500">
                                    <!-- <a href="#" class="link-to-category fs-12 text-red">{{ $PostAll['category_name'] ?? '' }}</a> -->
                                    <div class="calendar d-flex align-items-center gap-1 text-red">
                                        <img src="{{ asset('langding/imgs/calendar-white.svg') }}"
                                            alt="Calendar" class="img-fluid" width="18">
                                        <span
                                            class="fs-12">{{ \Carbon\Carbon::parse($PostAll['published_at'] ?? $PostAll['created_at'])->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                <h3 class="posts-title line-3 fs-18 fw-500">
                                    @if (!empty($PostAll['slug']))
                                    <a class="text-black"
                                        href="{{ route('post.detail', ['slug' => $PostAll['slug']]) }}">{{ $PostAll['title'] ?? '' }}</a>
                                    @else
                                    <span class="text-black">{{ $PostAll['title'] ?? '' }}</span>
                                    @endif
                                </h3>
                                <div class="posts-footer d-flex align-items-center justify-content-between">
                                    <div class="posts-author d-flex align-items-center gap-2">
                                        {{-- <div class="posts-author-img d-flex align-items-center justify-content-center" style="background: #F52618; border-radius: 50%; width: 30px; height: 30px;">
                                            <i class="bi bi-person-circle text-white" style="font-size: 24px;"></i>
                                        </div>
                                        <div class="posts-author-info">
                                            <div class="fs-16 fw-600">{{ __('messages.admin') }}
                                    </div>
                                    <div class="fs-14 fw-300">{{ __('messages.casumina') }}</div>
                                </div> --}}
                            </div>
                            @if (!empty($PostAll['slug']))
                            <a href="{{ route('post.detail', ['slug' => $PostAll['slug']]) }}"
                                class="posts-view-more d-flex align-items-center justify-content-center">
                                <svg width="60" height="32" viewBox="0 0 80 32"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect y="15" width="60.1622" height="2" fill="#F52618" />
                                    <circle cx="59.1348" cy="16" r="16"
                                        fill="#F52618" />
                                    <path
                                        d="M63.6783 16.6283L55.7637 24.5403C55.4164 24.8867 54.8537 24.8867 54.5056 24.5403C54.1583 24.1939 54.1583 23.6312 54.5056 23.2848L61.7924 16.0006L54.5064 8.71637C54.1591 8.36996 54.1591 7.80729 54.5064 7.46C54.8537 7.11359 55.4173 7.11359 55.7646 7.46L63.6792 15.3719C64.0211 15.7148 64.0211 16.2863 63.6783 16.6283Z"
                                        fill="white" />
                                </svg>
                            </a>
                            @else
                            <span
                                class="posts-view-more d-flex align-items-center justify-content-center">
                                <svg width="80" height="32" viewBox="0 0 80 32"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect y="15" width="60.1622" height="2" fill="#F52618" />
                                    <circle cx="59.1348" cy="16" r="16"
                                        fill="#F52618" />
                                    <path
                                        d="M63.6783 16.6283L55.7637 24.5403C55.4164 24.8867 54.8537 24.8867 54.5056 24.5403C54.1583 24.1939 54.1583 23.6312 54.5056 23.2848L61.7924 16.0006L54.5064 8.71637C54.1591 8.36996 54.1591 7.80729 54.5064 7.46C54.8537 7.11359 55.4173 7.11359 55.7646 7.46L63.6792 15.3719C64.0211 15.7148 64.0211 16.2863 63.6783 16.6283Z"
                                        fill="white" />
                                </svg>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <div class="col-12">
                <div class="text-center py-5">
                    <p class="fs-18 text-muted">{{ __('messages.no_category_news') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<div class="category-search-hot-link">
    <a class="fs-16 text-uppercase font-hanzel" href="#">{{ __('messages.truck_tires') }}</a>
    <a class="fs-16 text-uppercase font-hanzel" href="#">{{ __('messages.bicycle_tires') }}</a>
    <a class="fs-16 text-uppercase font-hanzel" href="#">{{ __('messages.motorcycle_tires') }}</a>
    <a class="fs-16 text-uppercase font-hanzel" href="#">{{ __('messages.advenza_pcr') }}</a>
    <a class="fs-16 text-uppercase font-hanzel" href="#">{{ __('messages.specialized_tires') }}</a>
    <a class="fs-16 text-uppercase font-hanzel" href="#">{{ __('messages.electric_tires') }}</a>
    <span class="scroll-if-overflow d-none" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
            stroke-width="2">
            <polyline points="9 6 15 12 9 18"></polyline>
        </svg>
    </span>
</div>

@include('langding.components.promotion-slide')

@endsection