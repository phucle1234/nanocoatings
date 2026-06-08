@extends('langding.index')

@section('title', __('messages.category_search'))

@section('langding_content')
@php
$parent = isset($categoryParentCode) ? trim((string) $categoryParentCode) : '';
$vehicleType = $vehicleType ?? request()->get('vehicle_type') ?? request()->get('vehicleType') ?? 'oto';
$vehicleType = str_replace('_', '-', strtolower((string) $vehicleType));
$bannerBg = match (true) {
str_starts_with($parent, '01') => asset('langding/imgs/category/xetai.png'),
str_starts_with($parent, '03') => asset('langding/imgs/category/xemay.png'),
str_starts_with($parent, '04') => asset('langding/imgs/category/oto.png'),
default => asset('langding/imgs/category/oto.png'),
};
@endphp
<div class="category-search-banner bg-img-cover"
    style="background-image: url('{{ $bannerBg }}');">
    <div class="category-search-banner-info">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"
                            class="fs-14 text-white">{{ __('messages.home') }}</a></li>
                    <li class="breadcrumb-item"><a href="#"
                            class="fs-14 text-white">{{ __('messages.products') }}</a></li>
                </ol>
            </nav>
            <div class="category-search-banner-content text-center">
            </div>
        </div>
    </div>
</div>
<div class="category-search">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-dark d-none">
                <li class="breadcrumb-item"><a href="{{ route('home') }}"
                        class="fs-14 text-black">{{ __('messages.home') }}</a></li>
                <li class="breadcrumb-item"><a href="#" class="fs-14 text-black">{{ __('messages.products') }}</a>
                </li>
            </ol>
        </nav>
    </div>

    <div class="category-search-list">
        @if (!empty($searchQuery))
        <h1 class="font-hanzel text-center lh-1 mt-3">{{ __('messages.found_products', ['count' => $searchTotal]) }}
        </h1>
        @else
        <h1 class="font-hanzel fs-46 text-center lh-1 mt-3">{{ __('messages.products') }}</h1>
        @endif
        <div class="tabs-scroll-wrap">
            <ul class="nav nav-tabs mt-4 tabs-scroll" id="category-search-list-title" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fs-14 font-hanzel" id="cat-all" data-bs-toggle="tab"
                        data-bs-target="#cat-all-pane" type="button" role="tab" aria-controls="cat-all-pane"
                        aria-selected="true">{{ __('messages.all_products') }}</button>
                </li>
                @foreach ($categories as $index => $category)
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel " id="cat-{{ $category->id }}" data-bs-toggle="tab"
                        data-bs-target="#cat-{{ $category->id }}-pane" type="button" role="tab"
                        aria-controls="cat-{{ $category->id }}-pane"
                        aria-selected="false">{{ $category->category_name ?? $category->code }}</button>
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

        <div class="container-fluid">
            <!-- <div class="category-search-filter d-lg-flex align-items-center justify-content-between mt-3">
                    <div class="category-search-filter-one d-flex align-items-center gap-3 gap-lg-4 mb-3 mb-lg-0">
                        <div class="category-search-filter-display d-flex">
                            <div class="category-search-filter-display-wrap d-flex align-items-center gap-1">
                                <div class="category-search-filter-display-icon active"><i class="bi bi-grid"></i></div>
                                <div class="category-search-filter-display-icon"><i class="bi bi-list-ul"></i></div>
                            </div>
                        </div>
                        <div class="category-search-filter-field">
                            <button type="button"
                                class="btn category-search-filter-field-wrap dropdown-toggle-split d-flex align-items-center gap-2"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                <i class="bi bi-funnel"></i><span
                                    class="fs-16 fw-500">{{ __('messages.product_filter') }}</span>
                            </button>
                            <ul class="dropdown-menu">
                                <div class="category-search-filter-field-cat">
                                    <select class="select2-filter w-100" id="select2-filter-cat" name="category[]"
                                        multiple="multiple" data-placeholder="{{ __('messages.product_category') }}"
                                        style="width: 100%;">
                                        <option value="AL"><i
                                                class="bi bi-check-square"></i>{{ __('messages.truck_tires') }}</option>
                                        <option value="WY"><i
                                                class="bi bi-check-square"></i>{{ __('messages.bicycle_tires') }}</option>
                                        <option value="WY"><i
                                                class="bi bi-check-square"></i>{{ __('messages.motorcycle_tires') }}
                                        </option>
                                        <option value="WY"><i
                                                class="bi bi-check-square"></i>{{ __('messages.advenza_pcr') }}</option>
                                        <option value="WY"><i
                                                class="bi bi-check-square"></i>{{ __('messages.specialized_tires') }}
                                        </option>
                                        <option value="WY"><i
                                                class="bi bi-check-square"></i>{{ __('messages.electric_tires') }}</option>
                                    </select>
                                </div>
                                <div class="category-search-filter-field-range mt-3">
                                    <select class="select2-filter w-100" id="select2-filter-range"
                                        data-placeholder="{{ __('messages.price_range') }}" style="width: 100%;">
                                        <option value="bb">{{ __('messages.price_range') }}</option>
                                        <option value="AL">{{ __('messages.from_1_3m') }}</option>
                                        <option value="WY">{{ __('messages.from_3_5m') }}</option>
                                    </select>
                                </div>
                                <div class="category-search-filter-field-property mt-3">
                                    <select class="select2-filter w-100" id="select2-filter-property"
                                        data-placeholder="{{ __('messages.product_features') }}" style="width: 100%;">
                                        <option value="aa">{{ __('messages.product_features') }}</option>
                                        <option value="AL">{{ __('messages.good') }}</option>
                                        <option value="WY">{{ __('messages.average') }}</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary mt-3 btn-search-product fs-16 text-white px-4">
                                    <span>{{ __('messages.filter_product') }}</span><i
                                        class="fs-20 bi bi-search ms-2"></i>
                                </button>
                            </ul>
                        </div>
                    </div>
                    <div class="category-search-filter-two d-flex align-items-center gap-3">
                        <div class="category-search-filter-sort">
                            <div class="category-search-filter-sort-order d-xxl-flex align-items-center gap-2">
                                <div class="fs-16 text-nowrap mb-1 mb-xxl-0">{{ __('messages.sort_by') }}</div>
                                <select class="select2-filter w-100" id="select2-filter-sort"
                                    data-placeholder="{{ __('messages.sort_by') }}" style="width: 100%;">
                                    <option value="aa">{{ __('messages.default') }}</option>
                                    <option value="AL">{{ __('messages.price_low_high') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="category-search-filter-pagination">
                            <div class="category-search-filter-pagination-item d-xxl-flex align-items-center gap-2">
                                <div class="fs-16 text-nowrap mb-1 mb-xxl-0"><span>15/ 89</span>
                                    {{ __('messages.products_display') }}</div>
                                <select class="select2-filter w-100" id="select2-filter-pagination"
                                    data-placeholder="{{ __('messages.products_display') }}" style="width: 100%;">
                                    <option value="">{{ __('messages.product_features') }}</option>
                                    <option value="AL">{{ __('messages.products_count', ['count' => 15]) }}</option>
                                    <option value="WY">{{ __('messages.products_count', ['count' => 30]) }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div> -->
            <div class="tab-content" id="category-search-list-content">
                <div class="tab-pane fade show active" id="cat-all-pane" role="tabpanel" aria-labelledby="cat-all"
                    tabindex="0">
                    <div class="category-search-list-item">
                        <div class="row">
                            @forelse($allProducts as $rowIndex => $productRow)
                            <div class="col-md-6 col-xl-4 col-xxl-3">
                                <div class="product-item position-relative">
                                    <div class="product-attr">
                                        @if ($productRow->created_at->diffInDays() < 30)
                                            <span class="product-new">{{ __('messages.new') }}</span>
                                            @endif
                                            @if ($productRow->hasValidSalePromotion())
                                            <span
                                                class="product-sale">-{{ number_format((($productRow->price - $productRow->sale_price) / $productRow->price) * 100) }}%</span>
                                            @endif
                                    </div>
                                    <div class="product-favourite">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="33"
                                            fill="black" class="bi bi-heart" viewBox="0 0 16 16">
                                            <path
                                                d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="33"
                                            fill="currentColor" class="bi bi-heart-fill" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd"
                                                d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314" />
                                        </svg>
                                    </div>
                                    <a href="{{ route('product.detail', ['slug' => $productRow->slug ?? $productRow->id]) }}"
                                        class="link-overlay">
                                        <div class="product-item-img bg-img-contain ratio-1-1"
                                            style="background-image: url('{{ $productRow->image ?? asset('images/no-image.png') }}');">
                                        </div>
                                    </a>
                                    <div class="product-item-line"></div>
                                    <div class="product-item-category d-flex align-items-center gap-2">
                                        <a
                                            href="{{ route('category', ['slug' => $productRow->category_slug ?? '']) }}">{{ $productRow->category_name ?? '' }}</a>
                                        <div class="product-item-star text-nowrap">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                    </div>
                                    <h3 class="product-item-title text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                                        <a class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2"
                                            href="{{ route('product.detail', ['slug' => $productRow->slug ?? $productRow->id]) }}">{{ $productRow->product_name ?? '' }}</a>
                                    </h3>
                                    <div class="product-price d-flex gap-3 align-items-center">
                                        <span
                                            class="fs-24 font-hanzel text-red">{{ $productRow->price_display ?? __('messages.contact') }}</span>
                                        <span class="fs-14 text-red">{{ __('messages.vat_included') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <div class="product-item-view">
                                            <a class="fs-16 text-black"
                                                href="{{ route('product.detail', ['slug' => $productRow->slug ?? $productRow->id]) }}">{{ __('messages.detail') }}</a>
                                        </div>
                                        <div class="cat-link d-flex align-items-center">
                                            <a href="javascript:void(0)" class="w-100 add-to-cart-btn"
                                                data-product-id="{{ $productRow->id ?? '' }}"
                                                data-product-name="{{ $productRow->product_name ?? '' }}"
                                                data-add-to-cart-url="{{ route('cart.add') }}"
                                                title="{{ __('messages.add_to_cart') }}">
                                                <img src="{{ asset('langding/imgs/category/cart-icon.svg') }}">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <p class="fs-18 text-muted">{{ __('messages.no_products') }}</p>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @foreach ($categories as $category)
                <div class="tab-pane fade" id="cat-{{ $category->id }}-pane" role="tabpanel"
                    aria-labelledby="cat-{{ $category->id }}" tabindex="0">
                    <div class="category-search-list-item">
                        <div class="row">
                            @foreach ($category->products as $rowIndex => $productRow)
                            <div class="col-md-6 col-xl-4 col-xxl-3">
                                <div class="product-item position-relative">
                                    <div class="product-attr">
                                        @if ($productRow->created_at->diffInDays() < 30)
                                            <span class="product-new">{{ __('messages.new') }}</span>
                                            @endif
                                            @if ($productRow->hasValidSalePromotion())
                                            <span
                                                class="product-sale">-{{ number_format((($productRow->price - $productRow->sale_price) / $productRow->price) * 100) }}%</span>
                                            @endif
                                    </div>
                                    <div class="product-favourite">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="33"
                                            fill="black" class="bi bi-heart" viewBox="0 0 16 16">
                                            <path
                                                d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="33"
                                            fill="currentColor" class="bi bi-heart-fill" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd"
                                                d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314" />
                                        </svg>
                                    </div>
                                    <a href="{{ route('product.detail', ['slug' => $productRow->slug ?? $productRow->id]) }}"
                                        class="link-overlay">
                                        <div class="product-item-img bg-img-contain ratio-1-1"
                                            style="background-image: url('{{ $productRow->image ?? asset('images/no-image.png') }}');">
                                        </div>
                                    </a>
                                    <div class="product-item-line"></div>
                                    <div class="product-item-category d-flex align-items-center gap-2">
                                        <a
                                            href="{{ route('category', ['slug' => $productRow->category_slug ?? '']) }}">{{ $productRow->category_name ?? '' }}</a>
                                        <div class="product-item-star text-nowrap">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                        </div>
                                    </div>
                                    <h3 class="product-item-title text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                                        <a class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2"
                                            href="{{ route('product.detail', ['slug' => $productRow->slug ?? $productRow->id]) }}">{{ $productRow->product_name ?? '' }}</a>
                                    </h3>
                                    <div class="fs-12 text-uppercase">{{ $productRow->short_description }}
                                    </div>
                                    <div class="product-price d-flex gap-3 align-items-center">
                                        <span
                                            class="fs-24 font-hanzel text-red">{{ $productRow->price_display ?? __('messages.contact') }}</span>
                                        <span class="fs-14 text-red">{{ __('messages.vat_included') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <div class="product-item-view">
                                            <a class="fs-16 text-black"
                                                href="{{ route('product.detail', ['slug' => $productRow->slug ?? $productRow->id]) }}">{{ __('messages.detail') }}</a>
                                        </div>
                                        <div class="cat-link d-flex align-items-center">
                                            <a href="javascript:void(0)" class="w-100 add-to-cart-btn"
                                                data-product-id="{{ $productRow->id ?? '' }}"
                                                data-product-name="{{ $productRow->product_name ?? '' }}"
                                                data-add-to-cart-url="{{ route('cart.add') }}"
                                                title="{{ __('messages.add_to_cart') }}">
                                                <img
                                                    src="{{ asset('langding/imgs/category/cart-icon.svg') }}">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
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
    </div>
</div>

@include('langding.components.promotion-slide')

@endsection