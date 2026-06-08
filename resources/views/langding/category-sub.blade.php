@extends('langding.index')

@section('title', ($selectedRootCategory['name'] ?? __('messages.category')) . ' - Casumina')

@section('langding_content')

    <div class="page-venture">
        <div class="category-banner bg-img-cover"
            style="background-image: url('{{ $firstBannerPost['image'] ?? asset('langding/imgs/category/bg-search.png') }}');">
            <div class="category-banner-info">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}"
                                    class="fs-14 text-white">{{ __('messages.home') }}</a></li>
                            <li class="breadcrumb-item"><a href="#"
                                    class="fs-14 text-white">{{ __('messages.products') }}</a></li>
                            @if (isset($selectedRootCategory['parent']) && $selectedRootCategory['parent'])
                                <li class="breadcrumb-item">
                                    <a href="{{ route('category', ['slug' => $selectedRootCategory['parent']['slug']]) }}"
                                        class="fs-14 text-white">
                                        {{ $selectedRootCategory['parent']['name'] ?? 'N/A' }}
                                    </a>
                                </li>
                            @endif
                            @if ($selectedRootCategory)
                                <li class="breadcrumb-item active"><span
                                        class="fs-15 text-white">{{ $selectedRootCategory['name'] ?? 'N/A' }}</span></li>
                            @endif
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="product-detail-related">
            <div class="container-fluid">
                <div class="title-with-line fw-500 fs-20 text-center text-light-red text-uppercase">
                    {{ $selectedRootCategory['name'] }}</div>

                <h2 class="title-main font-hanzel fs-32 fw-400 text-center mt-2">
                    {{ __('messages.latest_products', ['category' => $selectedRootCategory['name']]) }}</h2>

            </div>
            <div class="category-search-list">
                <div class="container-fluid">
                    <div class="category-search-list-item">
                        <div class="row">
                            @if (!empty($allProducts))
                                @foreach (array_slice($allProducts, 0, 12) as $product)
                                    <div class="col-md-6 col-xl-4 col-xxl-3">
                                        <div class="product-item position-relative">
                                            @if ($product['is_new'] ?? false)
                                                <div class="product-attr">
                                                    <span class="product-new">{{ __('messages.new') }}</span>
                                                </div>
                                            @endif
                                            <div class="product-favourite">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                    fill="black" class="bi bi-heart" viewBox="0 0 16 16">
                                                    <path
                                                        d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                                                </svg>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                    fill="currentColor" class="bi bi-heart-fill" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd"
                                                        d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314" />
                                                </svg>
                                            </div>
                                            <a href="{{ route('product.detail', ['slug' => $product['slug'] ?? $product['id']]) }}"
                                                class="link-overlay">
                                                <div class="product-item-img bg-img-contain ratio-1-1"
                                                    style="background-image: url('{{ asset($product['image'] ?? 'langding/imgs/product/product-avata.png') }}');">
                                                </div>
                                            </a>

                                            <div class="product-item-line"></div>
                                            <div class="product-item-category d-flex align-items-center gap-2">
                                                <a
                                                    href="{{ route('category', ['slug' => $selectedRootCategory['slug'] ?? '']) }}">{{ $product['category_name'] ?? '' }}</a>
                                                <div class="product-item-star text-nowrap">
                                                    <i class="bi bi-star-fill"></i>
                                                    <i class="bi bi-star-fill"></i>
                                                    <i class="bi bi-star-fill"></i>
                                                    <i class="bi bi-star-fill"></i>
                                                    <i class="bi bi-star-fill"></i>
                                                    <!-- <i class="bi bi-star"></i> -->
                                                </div>
                                            </div>
                                            <h3
                                                class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                                                <a class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2"
                                                    href="{{ route('product.detail', ['slug' => $product['slug'] ?? $product['id']]) }}">{{ $product['name'] ?? 'N/A' }}</a>
                                            </h3>
                                            <div class="fs-12 text-uppercase">{{ $product['size'] ?? '' }}</div>
                                            <div class="product-price d-flex gap-3 align-items-center">
                                                <span
                                                    class="fs-24 font-hanzel text-red">{{ $product['price_display'] ?? __('messages.contact') }}</span>
                                                <span class="fs-14 text-red">{{ __('messages.vat_included') }}</span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-2">
                                                <div class="product-item-view">
                                                    <a class="fs-16 text-black"
                                                        href="{{ route('product.detail', ['slug' => $product['slug'] ?? $product['id']]) }}">
                                                        <span class="me-2">{{ __('messages.detail') }}</span>
                                                        <svg width="12" height="13" viewBox="0 0 12 13"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M1.96059 1.26893L10.0296 0.562985M10.0296 0.562985L10.7356 8.63204M10.0296 0.562985L0.562794 11.8451"
                                                                stroke="black" stroke-width="1.12583" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                    </a>
                                                </div>
                                                <div class="cat-link d-flex align-items-center">
                                                    <a href="javascript:void(0)" class="w-100 add-to-cart-btn"
                                                        data-product-id="{{ $product['id'] ?? '' }}"
                                                        data-product-name="{{ $product['name'] ?? '' }}"
                                                        data-add-to-cart-url="{{ route('cart.add') }}"
                                                        title="{{ __('messages.add_to_cart') }}">
                                                        <img src="{{ asset('langding/imgs/category/cart-icon.svg') }}">
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
                        {{-- Phân trang --}}
                        @include('langding.components.pagination', ['pagination' => $pagination ?? null])
                    </div>
                </div>
                <div class="category-search-hot-link mt-5">
                    @foreach ($siblingCategories as $category)
                        <a class="fs-16 text-uppercase font-hanzel"
                            href="{{ route('category', ['slug' => $category['slug']]) }}">{{ $category['name'] ?? 'N/A' }}</a>
                    @endforeach
                    <span class="scroll-if-overflow d-none" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <polyline points="9 6 15 12 9 18"></polyline>
                        </svg>
                    </span>
                </div>
            </div>
        </div>
    </div>


    @include('langding.components.video')

    @include('langding.components.promotion-slide')

    <!-- @include('langding.components.daily') -->

@endsection
