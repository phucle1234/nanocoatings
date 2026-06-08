@extends('langding.index')

@section('title', ($selectedRootCategory['name'] ?? __('messages.page_title')) . ' - Casumina')

@section('langding_content')
<div class="category-avenza-banner bg-img-cover" style="background-image: url('{{ $firstBannerPost['image'] ?? asset('langding/imgs/category/bg-search.png') }}');">
    <div class="category-avenza-banner-info">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="fs-15 text-white">{{ __('messages.home') }}</a></li>
                    <li class="breadcrumb-item"><a href="#" class="fs-15 text-white">{{ __('messages.product') }}</a></li>
                    @if($selectedRootCategory)
                    <li class="breadcrumb-item active"><span class="fs-15 text-white">{{ $selectedRootCategory['name'] ?? 'N/A' }}</span></li>
                    @endif
                </ol>
            </nav>
        </div>
    </div>
</div>

@include('langding.components.video')

<!-- <div class="category-avenza-about bg-img-cover" style="background-image: url('{{ $twoBannerPost['image'] ?? $firstBannerPost['image'] ?? asset("langding/imgs/bg-avenza-about.png") }}');">
    <div class="container-fluid">
        <div class="about-content">
            <div class="title-with-line fs-18 text-center text-light-red lh-1">{{ $twoBannerPost['title'] ?? ''  }}</div>
            <h2 class="font-hanzel fs-32 fw-400 text-center mb-0 mt-3 text-white">
                {!! html_entity_decode(data_get($twoBannerPost, 'excerpt', '')) !!}
            </h2>
            <div class="fs-16 text-center text-white fw-300 intro mx-auto mt-3">
                {!! html_entity_decode(data_get($twoBannerPost, 'content', '')) !!}</div>
            <div class="about-items">
                <div class="row">
                    @if(!empty($otherBannerPosts))
                    @foreach($otherBannerPosts as $otherBannerPost)
                    <div class="col-6 col-xl-3">
                        <div class="about-item d-flex justify-content-center">
                            <div class="about-item-content">
                                <img width="33" height="33" src="{{ asset($otherBannerPost['image']) }}" alt="{{ $otherBannerPost['title'] ?? '' }}" class="img-fluid">
                                <div class="fs-20 font-hanzel text-white title mt-2">{{ $otherBannerPost['title'] ?? '' }}</div>
                                <div class="fs-15 fw-300 text-white intro">
                                    {!! html_entity_decode(data_get($otherBannerPost, 'content', '')) !!}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="col-12">
                        <div class="text-center py-5">
                            <p class="fs-18 text-white"></p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div> -->

@if($selectedRootCategory)
<div class="category-avenza-cat bg-img-cover" style="background-image: url('{{ asset("langding/imgs/bg-avenza.png") }}');">
    <div class="container-fluid">
        <div class="title-with-line fs-18 text-center text-light-red">{{ $selectedRootCategory['name'] ?? 'N/A' }}</div>
        <h2 class="font-hanzel fs-32 fw-400 text-center mt-3">{!! $selectedRootCategory['meta_description'] !!}</h2>

        @if(!empty($childCategories))
        <div class="category-avenza-cat-items">
            <div class="row justify-content-center">
                @foreach($childCategories as $category)
                <div class="col-md-6 col-lg-6">
                    <div class="category-avenza-cat-item text-center">
                        <h3 class="fs-18 font-hanzel text-uppercase text-red mb-0">
                            {{ $category['name']  }}
                        </h3>
                        {{-- <div class="fw-300 text-16 mt-1 intro mx-auto limit2linecontect">
                            {{ $category['meta_description'] ?? '' }}
                        </div> --}}

                        <a href="{{ route('category', ['slug' => $category['slug']]) }}" class="link-overlay">
                            <img src="{{ $category['image'] ?? asset('images/no-image.png') }}" alt="{{ $category['name'] ?? __('messages.category') }}" class="img-fluid mx-auto mt-4">
                        </a>

                        <a href="{{ route('category', ['slug' => $category['slug']]) }}"
                            class="fs-16 d-flex align-items-center justify-content-center text-red gap-2 mt-4">
                            {{ __('messages.detail') }}
                            <svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.00109 1.37635L9.28725 0.738899M9.28725 0.738899L9.92471 8.02506M9.28725 0.738899L0.738909 10.9264" stroke="#2ccc81" stroke-width="1.47765" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="category-avenza-cat-items">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="text-center py-5">
                        <p class="fs-18">{{ __('messages.no_categories_in_parent') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif


<div class="category-avenza-product-highlight">
    <div class="container-fluid">
        <div class="title-with-line fs-18 text-center text-light-red">{{ __('messages.featured_products') }}</div>
        <h2 class="font-hanzel fs-32 fw-400 text-center mt-3 mb-4">{{ __('messages.featured_products_subtitle') }}</h2>
        <div class="row">
            <div class="highlight-items">

                @if(!empty($allProducts))
                @foreach(array_slice($allProducts, 0, 8) as $product)
                <div class="highlight-item">
                    <div class="product-item position-relative">
                        @if($product['is_new'] ?? false)
                        <div class="product-attr">
                            <span class="product-new">{{ __('messages.new') }}</span>
                        </div>
                        @endif

                        <div class="product-favourite">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="black" class="bi bi-heart" viewBox="0 0 16 16">
                                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                            </svg>
                        </div>

                        <a href="{{ route('product.detail', ['slug' => $product['slug']]) }}" class="link-overlay">
                            <div class="product-item-img bg-img-contain" style="background-image: url('{{ $product['image'] ?? asset('images/no-image.png') }}');"></div>
                        </a>
                        <div class="product-item-line"></div>


                        <div class="product-item-category d-flex align-items-center gap-2">
                            <a href="{{ route('category', ['slug' => $product['category_slug'] ?? '']) }}">{{ $category['name'] ?? __('messages.category') }}</a>
                            <div class="product-item-star text-nowrap">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <!-- <i class="bi bi-star"></i> -->
                            </div>
                        </div>

                        <h3 class="product-item-title text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                            <a class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2" href="{{ route('product.detail', ['slug' => $product['slug']]) }}">{{ $product['name'] ?? '' }}</a>
                        </h3>

                        <div class="product-price d-flex gap-3 align-items-center">
                            <span class="fs-24 font-hanzel text-red lh-1">
                                {{ $product['price_display'] ?? __('messages.contact') }}
                            </span>
                            <span class="fs-14 text-red">{{ __('messages.vat_included') }}</span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="product-item-view">
                                <a class="fs-16 text-red" href="{{ route('product.detail', ['slug' => $product['slug']]) }}">
                                    <span class="me-2">{{ __('messages.detail') }}</span>
                                    <svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.00109 1.37635L9.28725 0.738899M9.28725 0.738899L9.92471 8.02506M9.28725 0.738899L0.738909 10.9264" stroke="#2ccc81" stroke-width="1.47765" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </a>
                            </div>
                            <div class="cat-link d-flex align-items-center">
                                <a href="javascript:void(0)" class="w-100 add-to-cart-btn"
                                    data-product-id="{{ $product['id'] ?? '' }}"
                                    data-product-name="{{ $product['name'] ?? '' }}"
                                    data-add-to-cart-url="{{ route('cart.add') }}"
                                    title="{{ __('messages.add_to_cart') }}">
                                    <img src="{{ asset('langding/imgs/category/cart-icon.svg') }}" alt="{{ __('messages.cart') }}">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="category-avenza-cat-items">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="text-center py-5">
                                <p class="fs-18">{{ __('messages.no_products') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('langding.home.blocks.bestseller')

@include('langding.components.promotion-slide')

@include('langding.components.partner-slide')

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

<!-- @include('langding.components.daily') -->
@endsection
