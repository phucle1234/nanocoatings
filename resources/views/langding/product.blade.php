@extends('langding.index')

@section('title', $product['product_name'] ?? $product['sku'])

@section('langding_content')

<div class="product-detail-slider">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}"
                        class="fs-14 text-black">{{ __('messages.home') }}</a></li>

                <li class="breadcrumb-item">
                    <a href="{{ route('category', ['slug' => $category['slug'] ?? $category['id']]) }}"
                        class="fs-14 text-black">
                        {{ $category['name'] ?? __('messages.category') }}
                    </a>
                </li>

                <li class="breadcrumb-item fs-14 text-black" aria-current="page">
                    {{ $product['product_name'] ?? $product['sku'] }}
                </li>
            </ol>
        </nav>
        <div class="row">
            <div class="col-lg-6 col-xxl-7">
                <div class="banner-slider d-flex">
                    <div class="slider slider-for">
                        @if (!empty($product['all_images2']) && is_array($product['all_images2']))
                        @foreach ($product['all_images2'] as $image)
                        <div class="slider-banner-image">
                            <img src="{{ $image ?? asset('images/no-image.png') }}"
                                alt="{{ $product['product_name'] ?? $product['sku'] }}" class="img-fluid">
                        </div>
                        @endforeach
                        @else
                        <div class="slider-banner-image">
                            <img src="{{ asset('images/no-image.png') }}"
                                alt="{{ $product['product_name'] ?? $product['sku'] }}" class="img-fluid">
                        </div>
                        @endif
                    </div>
                    @if (!empty($product['all_images2']) && is_array($product['all_images2']) && count($product['all_images2']) > 1)
                    <div class="slider slider-nav thumb-image">
                        @if (!empty($product['all_images2']) && is_array($product['all_images2']))
                        @foreach ($product['all_images2'] as $image)
                        <div class="thumbnail-image">
                            <div class="thumbImg">
                                <img src="{{ $image }}" alt="slider-img" class="img-fluid">
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="thumbnail-image">
                            <div class="thumbImg">
                                <img src="{{ asset('images/no-image.png') }}" alt="slider-img"
                                    class="img-fluid">
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-6 col-xxl-5">
                <div class="product-infomation mx-xxl-5 mt-lg-0 mt-5">
                    <div class="product-infomation-stock d-flex align-items-center gap-2">
                        <span class="fs-12">{{ __('messages.in_stock') }}</span>
                        @if ($product['has_sale_promotion'] ?? false)
                        <span class="active fs-12">{{ __('messages.on_sale') }}</span>
                        @endif
                    </div>
                    <h1 class="fs-40 font-hanzel mt-3 text-uppercase">{{ $product['product_name'] ?? $product['sku'] }}
                    </h1>

                    <div class="product-price mt-3 d-flex gap-4 align-items-center">
                        <span
                            class="fs-24 font-hanzel text-red">{{ $product['price_display'] ?? __('messages.contact') }}</span>
                        <!-- <span class="fs-16 text-decoration-line-through text-muted">{{ number_format($product['price'] ?? 0, 0, ',', '.') }}đ</span> -->
                        <span class="fs-16 font-hanzel text-red">{{ __('messages.vat_included') }}</span>
                    </div>

                    <div class="product-review mt-3 d-flex gap-5 align-items-center">
                        <div class="product-review-star d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <img src="{{ asset('langding/imgs/product/star.svg') }}" alt="Star"
                                    class="img-fluid">
                                <img src="{{ asset('langding/imgs/product/star.svg') }}" alt="Star"
                                    class="img-fluid">
                                <img src="{{ asset('langding/imgs/product/star.svg') }}" alt="Star"
                                    class="img-fluid">
                                <img src="{{ asset('langding/imgs/product/star.svg') }}" alt="Star"
                                    class="img-fluid">
                                <img src="{{ asset('langding/imgs/product/star.svg') }}" alt="Star"
                                    class="img-fluid">
                            </div>
                            <span class="fs-16">5.0</span>
                        </div>
                        <div class="product-review-count fs-16 fw-700">{{ __('messages.product_review') }}</div>
                    </div>
                    <div class="product-sku mt-3 d-flex gap-5 align-items-center">
                        <span class="fs-16">SKU: {{ $product['sku'] }}</span>
                        <span class="fs-16">{{ __('messages.category') }}:
                            {{ $category['name'] ?? __('messages.uncategorized') }}</span>
                    </div>
                    <div class="product-tags mt-3 d-flex align-items-center gap-3">
                        <span>Tags:</span>
                        <a href="{{ route('category', ['slug' => $category['slug'] ?? $category['id']]) }}?tag={{ urlencode($category['name'] ?? ($product['category_code'] ?? '')) }}"
                            class="tag-text fs-12 text-black">{{ $category['name'] ?? __('messages.uncategorized') }}</a>
                    </div>
                    <div class="product-properties">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="product-properties-qty">
                                    <div class="fs-16 text">{{ __('messages.quantity') }}</div>
                                    <div class="quantity d-flex align-items-center mt-2">
                                        <div class="qty-box qty-decrease fs-20">-</div>
                                        <input type="tel" class="form-control qty-input" value="1">
                                        <div class="qty-box qty-increase fs-20">+</div>
                                    </div>
                                </div>
                            </div>
                            @php
                            // Key = product_categories.code (bảng DB). PDF tương ứng từng nhánh catalog.
                            $catalogCategoryCodes = [
                            '04' => 'CatalogADVENZA.pdf',
                            '03_0302' => 'CatalogEUROMINA.pdf',
                            '01_0103' => 'CatalogRADIA.pdf',
                            ];
                            $catalogPdf = null;
                            // path: gốc → lá; đảo lại → từ lá lên gốc (mọi danh mục con vẫn khớp tổ tiên)
                            if (!empty($category['path']) && is_array($category['path'])) {
                            foreach (array_reverse($category['path']) as $pathItem) {
                            $code = $pathItem['code'] ?? null;
                            if ($code !== null && $code !== '' && isset($catalogCategoryCodes[$code])) {
                            $catalogPdf = $catalogCategoryCodes[$code];
                            break;
                            }
                            }
                            }
                            if ($catalogPdf === null) {
                            $categoryCode = $category['code'] ?? null;
                            $parentCode = $category['parent']['code'] ?? null;
                            $rootCode = null;
                            if (is_string($categoryCode) && str_contains($categoryCode, '_')) {
                            $rootCode = explode('_', $categoryCode, 2)[0] ?: null;
                            }

                            foreach (
                            [
                            $categoryCode,
                            $parentCode,
                            $rootCode, // vd 04_2101 -> 04
                            $category['slug'] ?? null,
                            $category['parent']['slug'] ?? null,
                            ]
                            as $key
                            ) {
                            if ($key !== null && $key !== '' && isset($catalogCategoryCodes[$key])) {
                            $catalogPdf = $catalogCategoryCodes[$key];
                            break;
                            }
                            }
                            }
                            @endphp
                            @if ($catalogPdf)
                            <div class="col-sm-6">
                                <div class="fs-16 text">{{ __('messages.download_related_documents') }}</div>
                                <div class="product-properties-qty">
                                    <div class="quantity d-flex align-items-center mt-2">
                                        <a class="btn btn-buy-now text-white fs-16 font-hanzel w-100"
                                            href="{{ asset('storage/images/' . $catalogPdf) }}"
                                            target="_blank">{{ __('messages.download') }}</a>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="row">
                            <!-- Nút MUA NGAY -->
                            <div class="col-md-6 col-lg-12 col-xl-6">
                                <div class="product-btn mt-4">
                                    <button type="button"
                                        class="btn btn-buy-now text-white fs-16 font-hanzel text-uppercase w-100"
                                        id="btn-buy-now" data-product-id="{{ $product['id'] ?? '' }}"
                                        data-product-name="{{ $product['product_name'] ?? $product['sku'] }}"
                                        data-add-to-cart-url="{{ route('cart.add') }}"
                                        data-view-cart-url="{{ route('cart') }}">
                                        {{ __('messages.buy_now') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Nút THÊM VÀO GIỎ HÀNG -->
                            <div class="col-md-6 col-lg-12 col-xl-6">
                                <div class="product-btn mt-4">
                                    <button type="button"
                                        class="btn btn-add-to-cart text-white fs-16 font-hanzel text-uppercase w-100 add-to-cart-btn"
                                        data-product-id="{{ $product['id'] ?? '' }}"
                                        data-product-name="{{ $product['product_name'] ?? $product['sku'] }}"
                                        data-add-to-cart-url="{{ route('cart.add') }}">
                                        {{ __('messages.add_to_cart') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="product-detail-infomation">
    <div class="container-fluid">
        <div class="tabs-scroll-wrap">
            <ul class="nav nav-tabs tabs-scroll" id="product-detail-infomation-title" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fs-14 font-hanzel" id="cat-1" data-bs-toggle="tab"
                        data-bs-target="#cat-1-pane" type="button" role="tab" aria-controls="cat-1-pane"
                        aria-selected="true">{{ __('messages.product_description') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel" id="cat-2" data-bs-toggle="tab"
                        data-bs-target="#cat-2-pane" type="button" role="tab" aria-controls="cat-2-pane"
                        aria-selected="false">{{ __('messages.product_features') }}</button>
                </li>
                @if (!empty($product['specifications']) && is_array($product['specifications']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel" id="cat-3" data-bs-toggle="tab"
                        data-bs-target="#cat-3-pane" type="button" role="tab" aria-controls="cat-3-pane"
                        aria-selected="false">{{ __('messages.technical_specifications') }}</button>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel" id="cat-4" data-bs-toggle="tab"
                        data-bs-target="#cat-4-pane" type="button" role="tab" aria-controls="cat-4-pane"
                        aria-selected="false">{{ __('messages.reviews') }}</button>
                </li>
            </ul>
            <span class="tabs-scroll-hint" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <polyline points="9 6 15 12 9 18"></polyline>
                </svg>
            </span>
        </div>
    </div>
    <div class="tab-content" id="product-detail-infomation-content">
        <div class="tab-pane fade show active" id="cat-1-pane" role="tabpanel" aria-labelledby="cat-1"
            tabindex="0">
            <div class="container-fluid">
                <div class="product-detail-infomation-content mx-auto">
                    <!-- <h3 class="fs-16 font-hanzel text-uppercase mt-3 text-center text-red fw-500">
                            {{ __('messages.product_description') }}</h3> -->
                    <div class="fs-18 text-center mt-3 product-detail-infomation-editer">
                        {!! $product['product_description'] ?? ($product['short_description'] ?? __('messages.no_description')) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="cat-2-pane" role="tabpanel" aria-labelledby="cat-2" tabindex="0">
            <div class="container-fluid">
                <div class="product-detail-infomation-content mx-auto">
                    <!-- <h3 class="fs-16 font-hanzel text-uppercase mt-3 text-center text-red fw-500">
                            {{ __('messages.product_features') }}</h3> -->
                    <div class="fs-18  mt-3 product-detail-infomation-editer">
                        @if (!empty($product['features']))
                        {!! $product['features'] !!}
                        @else
                        <p>{{ __('messages.no_features') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($product['specifications']) && is_array($product['specifications']))
        <div class="tab-pane fade" id="cat-3-pane" role="tabpanel" aria-labelledby="cat-3" tabindex="0">
            <div class="container-fluid">
                <div class="product-detail-infomation-content mx-auto">
                    <!-- <h3 class="fs-16 font-hanzel text-uppercase mt-3 text-center text-red fw-500">
                                {{ __('messages.technical_specifications') }}</h3> -->
                    @php
                    $specs = $product['specifications'] ?? [];
                    $locale = app()->getLocale();
                    $productId = $product['id'] ?? null;

                    $order = [];
                    if ($productId) {
                    $attributeIds = \DB::table('product_attribute_product')
                    ->where('product_id', $productId)
                    ->where('show_detail', 'Y')
                    ->join(
                    'product_attribute_values',
                    'product_attribute_product.attribute_value_id',
                    '=',
                    'product_attribute_values.id',
                    )
                    ->pluck('product_attribute_values.attribute_id')
                    ->unique()
                    ->toArray();

                    if (!empty($attributeIds)) {
                    $attributes = \App\Models\ProductAttribute::whereIn('id', $attributeIds)
                    ->with([
                    'translations' => function ($q) use ($locale) {
                    $q->where('language', $locale);
                    },
                    ])
                    ->orderBy('sort_order', 'ASC')
                    ->get();

                    foreach ($attributes as $attribute) {
                    $translation = $attribute->translations
                    ->where('language', $locale)
                    ->first();
                    $attributeName = $translation ? $translation->name : $attribute->code;
                    if (!empty($attributeName)) {
                    $order[] = $attributeName;
                    }
                    }
                    }
                    }

                    $sorted = array_filter(array_replace(array_fill_keys($order, null), $specs));
                    $sorted += array_filter(array_diff_key($specs, array_flip($order)));
                    @endphp

                    <div class="table-responsive mt-4">
                        <table class="table table-bordered">
                            @foreach ($sorted as $label => $value)
                            @if ($value !== null && $value !== '')
                            <tr>
                                <td class="fw-bold">{{ $label }}</td>
                                <td>{{ $value }}</td>
                            </tr>
                            @endif
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="tab-pane fade" id="cat-4-pane" role="tabpanel" aria-labelledby="cat-4" tabindex="0">
            <div class="container-fluid">
                <div class="product-detail-infomation-content mx-auto">
                    <!-- <h3 class="fs-16 font-hanzel text-uppercase mt-3 text-center text-red fw-500"> 
                    {{ __('messages.product_review') }}</h3>-->
                    <div class="fs-18 text-center mt-3">
                        <div class="product-review-star d-flex align-items-center gap-3">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($relatedProducts && count($relatedProducts) > 0)
    <div class="product-detail-related">
        <div class="container-fluid">
            <div class="title-with-line fw-500 fs-20 text-center text-light-red text-uppercase">
                {{ $category['name'] ?? __('messages.similar_category') }}
            </div>
            <h2 class="title-main font-hanzel fs-32 fw-400 text-center mt-2">
                {{ __('messages.similar_products', ['category' => $category['name'] ?? '']) }}
            </h2>
        </div>
        <div class="category-search-list">
            <div class="container-fluid">
                <div class="category-search-list-item">
                    <div class="row">
                        @forelse($relatedProducts as $relatedProduct)
                        <div class="col-md-6 col-xl-4 col-xxl-3">
                            <div class="product-item position-relative">
                                <div class="product-attr">
                                    @if ($relatedProduct['is_new'] ?? false)
                                    <span class="product-new">{{ __('messages.new_product') }}</span>
                                    @endif
                                    @if (($relatedProduct['is_sale'] ?? false) && ($relatedProduct['discount_percentage'] ?? 0) > 0)
                                    <span
                                        class="product-sale">-{{ $relatedProduct['discount_percentage'] }}%</span>
                                    @endif
                                </div>
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
                                <div class="product-item-img bg-img-contain ratio-1-1"
                                    style="background-image: url('{{ $relatedProduct['image'] ?? asset('images/no-image.png') }}');">
                                </div>
                                <div class="product-item-line"></div>
                                <div class="product-item-category d-flex align-items-center gap-2">
                                    <a
                                        href="{{ route('category', ['slug' => $category['slug'] ?? $category['id']]) }}">{{ $category['name'] ?? __('messages.category') }}</a>
                                    <div class="product-item-star text-nowrap">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                </div>
                                <h3
                                    class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                                    {{ $relatedProduct['product_name'] ?? ($relatedProduct['sku'] ?? __('messages.product_name')) }}
                                </h3>
                                <div class="fs-12 text-uppercase">
                                    {{ $relatedProduct['short_description'] ?? '' }}
                                </div>
                                <div class="product-price d-flex gap-3 align-items-center">
                                    <span
                                        class="fs-24 font-hanzel text-red">{{ $relatedProduct['price_display'] ?? __('messages.contact') }}</span>
                                    <span class="fs-14 text-red">{{ __('messages.vat_included') }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <div class="product-item-view">
                                        <a class="fs-16 text-red"
                                            href="{{ route('product.detail', ['slug' => $relatedProduct['slug'] ?? $relatedProduct['id']]) }}">
                                            <span class="me-2">{{ __('messages.view_details') }}</span>
                                            <svg width="11" height="12" viewBox="0 0 11 12"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M2.00109 1.37635L9.28725 0.738899M9.28725 0.738899L9.92471 8.02506M9.28725 0.738899L0.738909 10.9264"
                                                    stroke="#CF171C" stroke-width="1.47765"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </a>
                                    </div>
                                    <div class="cat-link d-flex align-items-center">
                                        <a href="javascript:void(0)" class="w-100 add-to-cart-btn"
                                            data-product-id="{{ $relatedProduct['id'] ?? '' }}"
                                            data-product-name="{{ $relatedProduct['product_name'] ?? '' }}"
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
                                <p class="fs-18">{{ __('messages.no_related_products') }}</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="category-search-hot-link">
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
    @endif
</div>
@endsection