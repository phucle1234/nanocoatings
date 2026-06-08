@extends('langding.index')
@section('title', __('messages.page_title'))
@section('langding_content')
<div class="page-about">
    <div class="box-media">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="fs-15 text-black">{{ __('messages.breadcrumb_home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('about') }}" class="fs-15 text-black">{{ __('messages.breadcrumb_about') }}</a></li>
                    <li class="breadcrumb-item active fs-15 text-black" aria-current="page">
                        {{ $categories[0]['title'] ?? __('messages.default_category') }}
                    </li>
                </ol>
            </nav>
            <h2 class="font-hanzel fs-32 mt-4 fw-400 text-center main-title">{{ __('messages.main_title') }}</h2>

            <div class="tabs-scroll-wrap"> 
                <ul class="nav nav-tabs tabs-scroll" id="box-media-title" role="tablist">
                    @foreach ($categories as $index => $post)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }} fs-14 font-hanzel text-uppercase"
                            id="media-{{ $post['id'] }}"
                            data-bs-toggle="tab"
                            data-bs-target="#media-{{ $post['id'] }}-pane"
                            type="button"
                            role="tab"
                            aria-controls="media-{{ $post['id'] }}-pane"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                            {{ $post['title'] }}
                        </button>
                    </li>
                    @endforeach
                </ul>
                <span class="tabs-scroll-hint" aria-hidden="true"> 
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"> 
                        <polyline points="9 6 15 12 9 18"></polyline> 
                    </svg> 
                </span> 
            </div>

            <div class="tab-content" id="box-media-content">
                @foreach ($categories as $index => $post)
                @php
                $isLibrary = stripos($post['title'] ?? '', 'Thư viện') !== false || stripos($post['slug'] ?? '', 'thu-vien') !== false;
                $isCommunity = stripos($post['title'] ?? '', 'Cộng đồng') !== false || stripos($post['slug'] ?? '', 'cong-dong') !== false;
                $isAward = stripos($post['title'] ?? '', 'Giải thưởng') !== false || stripos($post['slug'] ?? '', 'giai-thuong') !== false;
                $showList = $isLibrary || $isCommunity;
                $showSlide = $isAward;

                $listPosts = $isLibrary ? $libraryPosts : ($isCommunity ? $communityPosts : ($isAward ? $awardPosts : []));
                @endphp

                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                    id="media-{{ $post['id'] }}-pane"
                    role="tabpanel"
                    aria-labelledby="media-{{ $post['id'] }}"
                    tabindex="0">

                    @if($showSlide && ($awardCategoryId ?? null))
                    @include('langding.components.award-slide', [
                    'awardPosts' => collect($awardPosts ?? []),
                    'awardTiles' => $awardTiles ?? [],
                    'category' => $post,
                    'awardCategoryId' => $awardCategoryId,
                    'awardPagination' => $awardPagination ?? [],
                    ])
                    @elseif($showSlide && !empty($awardPosts))
                    @include('langding.components.award-slide', [
                    'awardPosts' => collect($awardPosts),
                    'awardTiles' => [],
                    'category' => $post,
                    'awardCategoryId' => null,
                    'awardPagination' => [],
                    ])
                    @elseif($showList && !empty($listPosts))
                    <section class="member-enterprises py-5">
                        <div class="container">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h2 class="section-title d-flex align-items-center">
                                        <img src="{{ asset('langding/imgs/icon-11.png') }}" alt="{{ __('messages.icon') }}" class="me-2">
                                        {{ $post['title'] }}
                                    </h2>
                                </div>
                            </div>

                            <div class="enterprise-list">
                                @foreach ($listPosts as $listPost)
                                @php
                                $imageUrl = !empty($listPost['image']['url'])
                                ? asset($listPost['image']['url'])
                                : (!empty($listPost['image']) ? asset($listPost['image']) : asset('langding/imgs/default-image.jpg'));
                                $title = $listPost['title'] ?? __('messages.no_title');
                                $excerpt = $listPost['excerpt'] ?? $listPost['description'] ?? '';
                                $slug = $listPost['slug'] ?? '';
                                @endphp

                                <div class="enterprise-item row g-0 mb-4 align-items-center">
                                    <div class="col-md-4 col-lg-3">
                                        <img src="{{ $imageUrl }}" class="img-fluid rounded shadow-sm" alt="{{ $title }}">
                                    </div>
                                    <div class="col-md-8 col-lg-9 ps-md-4 mt-3 mt-md-0">
                                        <h4 class="enterprise-name">{{ $title }}</h4>
                                        @if(!empty($excerpt))
                                        <p class="enterprise-address">{{ Str::limit(strip_tags($excerpt), 200) }}</p>
                                        @endif
                                        @if(!empty($slug))
                                        <div class="btn btn-outline-secondary">
                                            <a href="/post/{{ $slug }}" class="d-flex align-items-center gap-1" tabindex="0">
                                                <span class="fs-16 fw-500 text-red">{{ __('messages.detail') }}</span>
                                                <svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M2.00109 1.37635L9.28725 0.738899M9.28725 0.738899L9.92471 8.02506M9.28725 0.738899L0.738909 10.9264" stroke="#CF171C" stroke-width="1.47765" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                    @elseif($showList || $showSlide)
                    <div class="about-content">
                        <div class="about-content-text">
                            <h3 class="fs-24 font-hanzel text-center">{{ __('messages.no_posts') }}</h3>
                        </div>
                    </div>
                    @else
                    {!! $post['content'] ?? '' !!}
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@include('langding.components.video')

<!-- @include('langding.components.daily') -->
<div class="box-location">
    <div class="container-fluid">
        <div class="title-with-line fw-500 fs-18 text-center text-light-red">ĐẠI ĐIỆN ỦY QUYỀN</div>
        <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 mt-0 title">Hệ thống phân phối</h2>
        <div class="box-location-intro text-center fs-14 mt-4 mx-auto">
            Công ty NANO COATINGS hiện có 05 nhà máy ở Việt nam, 01 trung tâm nghiên cứu kỹ thuật –<br>R&D . Các sản
            phẩm được bán tại 64 tỉnh thành Việt Nam và trên 60 nước trên Thế giới.
        </div>
        <div class="box-location-search mx-auto">
            <div class="global-header d-flex align-items-center">
                <div class="search-box d-flex align-items-center w-50">
                    <img src="{{ asset('langding/imgs/world.png') }}" alt="Location" class="img-fluid">
                    <input type="text" class="form-control" placeholder="{{ __('messages.worldwide') }}">
                </div>
                <select class="form-select select-country" aria-label="Small select example">
                    <option selected>{{ __('messages.country') }}</option>
                    <option value="1">One</option>
                    <option value="2">Two</option>
                    <option value="3">Three</option>
                </select>
                <select class="form-select select-product" aria-label="Small select example">
                    <option selected>{{ __('messages.products') }}</option>
                    <option value="1">One</option>
                    <option value="2">Two</option>
                    <option value="3">Three</option>
                </select>
                <button class="btn btn-go fw-700 ms-auto" type="button">{{ __('messages.go') }}</button>
            </div>
        </div>
        <div class="bg-branch">
            <img src="{{ asset('langding/imgs/bg-branch-page.png') }}" alt="Background Branch"
                class="img-fluid w-100">
        </div>
    </div>
</div>

@include('langding.components.promotion-slide')
@endsection
