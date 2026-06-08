@extends('langding.index')
@section('title', __('messages.shop') . ' - Casumina')
@section('langding_content')

<div class="page-shop">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="fs-14 text-muted">{{ __('messages.home') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#" class="fs-14 text-muted">{{ __('messages.shop') }}</a>
                </li>
            </ol>
        </nav>

        @php
        $productPrices = collect($allProducts ?? [])->map(function ($product) {
        $candidateKeys = ['price', 'sale_price', 'final_price', 'selling_price', 'price_value', 'price_number'];

        foreach ($candidateKeys as $key) {
        if (isset($product[$key]) && is_numeric($product[$key]) && (float) $product[$key] > 0) {
        return (int) round($product[$key]);
        }
        }

        if (!empty($product['price_display'])) {
        $priceDigits = preg_replace('/[^\d]/', '', (string) $product['price_display']);

        if (is_numeric($priceDigits) && (int) $priceDigits > 0) {
        return (int) $priceDigits;
        }
        }

        return null;
        })->filter()->values();



        $selectedMinPrice = max($defaultMinPrice, min($selectedMinPrice, $defaultMaxPrice));
        $selectedMaxPrice = max($selectedMinPrice, min($selectedMaxPrice, $defaultMaxPrice));
        @endphp

        <div class="row g-4">
            <!-- <div class="col-lg-3">
                <div class="shop-sidebar">
                    <div class="shop-sidebar-section">
                        <div class="shop-sidebar-header bg-red text-white fw-600 py-3 px-4">
                            <span class="fs-16">{{ __('messages.product_category') }}</span>
                        </div>

                        <div class="shop-sidebar-content">
                            @if (isset($categoriesWithChildrenShop) && count($categoriesWithChildrenShop) > 0)
                            @foreach ($categoriesWithChildrenShop as $index => $category)
                            @php
                            $categoryUrl = isset($category['slug'])
                            ? '/category/' . $category['slug']
                            : '#';
                            $hasChildren =
                            isset($category['children']) && count($category['children']) > 0;
                            @endphp
                            <div class="category-item">
                                {{-- Parent Category --}}
                                <div class="category-parent">
                                    <div class="d-flex align-items-center gap-2 py-3 px-4">
                                        @if (isset($category['icon']) && !empty($category['icon']))
                                        <img src="{{ asset('imgs/' . $category['icon']) }}"
                                            alt="{{ $category['name'] ?? '' }}" width="24"
                                            height="24" onerror="this.style.display='none'">
                                        @endif
                                        <a href="{{ $categoryUrl }}"
                                            class="text-decoration-none text-dark fs-15 fw-500 flex-grow-1">
                                            {{ $category['name'] ?? '' }}
                                        </a>
                                        @if ($hasChildren)
                                        <button
                                            class="btn btn-sm p-0 border-0 bg-transparent category-toggle"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#category-{{ $index }}"
                                            aria-expanded="false"
                                            aria-controls="category-{{ $index }}">
                                            <i class="bi bi-chevron-down fs-12 text-muted"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Children Categories --}}
                                @if ($hasChildren)
                                <div class="collapse category-children" id="category-{{ $index }}">
                                    @foreach ($category['children'] as $childIndex => $child)
                                    @php
                                    $childUrl = isset($child['slug'])
                                    ? '/category/' . $child['slug']
                                    : '#';
                                    $hasGrandchildren =
                                    isset($child['children']) &&
                                    is_array($child['children']) &&
                                    count($child['children']) > 0;
                                    @endphp
                                    <div class="category-child">
                                        <div class="d-flex align-items-center py-2 px-4 ps-5">
                                            <a href="{{ $childUrl }}"
                                                class="text-decoration-none text-dark fs-14 flex-grow-1">
                                                {{ $child['name'] ?? '' }}
                                            </a>
                                            @if ($hasGrandchildren)
                                            <button
                                                class="btn btn-sm p-0 border-0 bg-transparent category-toggle"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#category-{{ $index }}-{{ $childIndex }}"
                                                aria-expanded="false"
                                                aria-controls="category-{{ $index }}-{{ $childIndex }}">
                                                <i class="bi bi-chevron-down fs-11 text-muted"></i>
                                            </button>
                                            @endif
                                        </div>

                                        {{-- Grandchildren (Level 3) --}}
                                        @if ($hasGrandchildren)
                                        <div class="collapse category-grandchildren"
                                            id="category-{{ $index }}-{{ $childIndex }}">
                                            @foreach ($child['children'] as $grandchild)
                                            @php
                                            $grandchildUrl = isset($grandchild['slug'])
                                            ? '/category/' . $grandchild['slug']
                                            : '#';
                                            @endphp
                                            <a href="{{ $grandchildUrl }}"
                                                class="d-block text-decoration-none text-muted py-1 px-4 ps-6 fs-13"
                                                style="padding-left: 4.0rem !important;">
                                                {{ $grandchild['name'] ?? '' }}
                                            </a>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                            @else
                            <div class="p-4 text-center text-muted">
                                {{ __('messages.no_categories') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div> -->

            <div class="col-lg-12" style="margin-top: -20px;">
                <div class="category-search category-search--inline">
                    <div class="category-search-list">
                        <h1 class="font-hanzel fs-46 text-center lh-1 mt-3">{{ __('messages.products') }}</h1>

                        <div class="category-search-body">
                            <div class="category-search-filter d-lg-flex align-items-center justify-content-between mt-3">
                                <div class="category-search-filter-one d-flex align-items-center gap-3 gap-lg-4 mb-3 mb-lg-0">
                                    <div class="category-search-filter-display d-flex">
                                        <div class="category-search-filter-display-wrap d-flex align-items-center gap-1">
                                            <div class="category-search-filter-display-icon active" data-view="grid">
                                                <i class="bi bi-grid"></i>
                                            </div>
                                            <div class="category-search-filter-display-icon" data-view="list">
                                                <i class="bi bi-list-ul"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="category-search-filter-field">
                                        <button type="button"
                                            class="btn category-search-filter-field-wrap dropdown-toggle-split d-flex align-items-center gap-2"
                                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                            <i class="bi bi-funnel"></i><span
                                                class="fs-16 fw-500">{{ __('messages.product_filter') }}</span>
                                        </button>
                                        <div class="dropdown-menu category-search-dropdown p-3">
                                            <div class="category-search-filter-field-cat">
                                                <select class="select2-filter w-100" id="select2-filter-cat" name="category[]"
                                                    multiple="multiple"
                                                    data-placeholder="{{ __('messages.product_category') }}"
                                                    style="width: 100%;">

                                                    @if(isset($categoriesWithChildrenShop) && count($categoriesWithChildrenShop) > 0)
                                                    @foreach($categoriesWithChildrenShop as $category)
                                                    <option value="{{ $category['id'] }}"
                                                        {{ in_array((string) $category['id'], array_map('strval', $filterSearch['category_ids'] ?? [])) ? 'selected' : '' }}>
                                                        {{ $category['name'] ?? '' }}
                                                    </option>
                                                    @endforeach
                                                    @else
                                                    <option value="">{{ __('messages.no_categories') }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="category-search-filter-field-range mt-3">
                                                <div class="filter-range-panel">
                                                    <div class="filter-range-title fs-16 fw-600 mb-3">{{ __('messages.price_range') }}</div>

                                                    <div class="price-range-slider">
                                                        <div class="price-range-slider-track"></div>
                                                        <div class="price-range-slider-progress" id="price-range-progress"></div>
                                                        <input type="range" class="price-range-input" id="price-range-min"
                                                            min="{{ $defaultMinPrice }}"
                                                            max="{{ $defaultMaxPrice }}"
                                                            step="10000"
                                                            value="{{ $selectedMinPrice }}">

                                                        <input type="range" class="price-range-input" id="price-range-max"
                                                            min="{{ $defaultMinPrice }}"
                                                            max="{{ $defaultMaxPrice }}"
                                                            step="10000"
                                                            value="{{ $selectedMaxPrice }}">

                                                        <input type="hidden" id="price-min-value" value="{{ $selectedMinPrice }}">
                                                        <input type="hidden" id="price-max-value" value="{{ $selectedMaxPrice }}">
                                                    </div>

                                                    <div class="price-range-values mt-3">
                                                        <div class="price-range-label fs-14 text-muted mb-1">Từ</div>
                                                        <div class="price-range-text fs-16 fw-500">
                                                            <span id="price-range-display-min">{{ number_format($selectedMinPrice, 0, ',', '.') }}đ</span>
                                                            <span class="mx-1">-</span>
                                                            <span id="price-range-display-max">{{ number_format($selectedMaxPrice, 0, ',', '.') }}đ</span>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" id="price-min-value" value="{{ $selectedMinPrice }}">
                                                    <input type="hidden" id="price-max-value" value="{{ $selectedMaxPrice }}">
                                                </div>
                                            </div>
                                            @if(isset($filterableAttributes) && $filterableAttributes->count() > 0)
                                            <div class="category-search-filter-field-characteristics mt-3">
                                                <div class="fs-16 fw-600 mb-2">{{ __('messages.product_features') }}</div>

                                                <div class="characteristics-list" style="max-height: 260px; overflow: auto;">
                                                    @foreach($filterableAttributes as $attr)
                                                    @php
                                                    $attrName = $attr->translation->name ?? $attr->code;
                                                    $values = $attr->activeValues ?? collect();
                                                    @endphp

                                                    @if($values->count() > 0)
                                                    <div class="characteristics-group mb-3">
                                                        <div class="fs-14 fw-600 mb-2">{{ $attrName }}</div>
                                                        <div class="d-flex flex-column gap-2">
                                                            @foreach($values as $val)
                                                            @php
                                                            $valLabel = $val->translation->value ?? $val->value;
                                                            $checked = in_array((string) $val->id, array_map('strval', $filterSearch['attribute_value_ids'] ?? []));
                                                            @endphp
                                                            <label class="d-flex align-items-center gap-2">
                                                                <input
                                                                    type="checkbox"
                                                                    class="form-check-input m-0 filter-attr-value"
                                                                    value="{{ $val->id }}"
                                                                    {{ $checked ? 'checked' : '' }}>
                                                                <span class="fs-14">{{ $valLabel }}</span>
                                                            </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                            <button class="btn btn-primary mt-3 btn-search-product fs-16 text-white px-4">
                                                <span>{{ __('messages.filter_product') }}</span><i
                                                    class="fs-20 bi bi-search ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="category-search-filter-two d-flex align-items-center gap-3">
                                    <div class="category-search-filter-sort d-flex align-items-center gap-2">

                                        <select class="select2-filter" id="select2-filter-type"
                                            data-placeholder="{{ __('messages.select_product_type') }}"
                                            data-minimum-results-for-search="Infinity"
                                            style="min-width: 220px;">
                                            <option value="" {{ empty($filterSearch['filter_type']) ? 'selected' : '' }}>{{ __('messages.all_products') }}</option>
                                            <option value="is_new" {{ ($filterSearch['filter_type'] ?? '') === 'is_new' ? 'selected' : '' }}>
                                                {{ __('messages.newest_products') }}
                                            </option>
                                            <option value="is_featured" {{ ($filterSearch['filter_type'] ?? '') === 'is_featured' ? 'selected' : '' }}>
                                                {{ __('messages.featured_products') }}
                                            </option>
                                            <option value="is_bestseller" {{ ($filterSearch['filter_type'] ?? '') === 'is_bestseller' ? 'selected' : '' }}>
                                                {{ __('messages.bestseller_products') }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="category-search-filter-sort">
                                        <div class="category-search-filter-sort-order d-xxl-flex align-items-center gap-2">
                                            <div class="fs-16 text-nowrap mb-1 mb-xxl-0">{{ __('messages.sort_by') }}</div>
                                            <select class="select2-filter w-100" id="select2-filter-sort"
                                                data-placeholder="{{ __('messages.sort_by') }}" style="width: 100%;">
                                                <option value="price"
                                                    {{ $filterSearch['sort_by'] === 'price' ? 'selected' : '' }}>
                                                    {{ __('messages.default') }}
                                                </option>
                                                <option value="price_desc"
                                                    {{ $filterSearch['sort_by'] === 'price_desc' ? 'selected' : '' }}>
                                                    {{ __('messages.price_high_low') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="category-search-filter-pagination">
                                        <div class="category-search-filter-pagination-item d-xxl-flex align-items-center gap-2">
                                            <div class="fs-16 text-nowrap mb-1 mb-xxl-0"><span>{{ $paginated->perPage() }} / {{ $paginated->total() }}</span>
                                                {{ __('messages.products') }}
                                            </div>
                                            <select class="select2-filter w-100" id="select2-filter-pagination"
                                                data-placeholder="{{ __('messages.products_display') }}"
                                                style="width: 100%;">
                                                <option value="16"
                                                    {{ $filterSearch['per_page'] == 16 ? 'selected' : '' }}>
                                                    {{ __('messages.products_count', ['count' => 16]) }}
                                                </option>
                                                <option value="24"
                                                    {{ $filterSearch['per_page'] == 24 ? 'selected' : '' }}>
                                                    {{ __('messages.products_count', ['count' => 24]) }}
                                                </option>
                                                <option value="32"
                                                    {{ $filterSearch['per_page'] == 32 ? 'selected' : '' }}>
                                                    {{ __('messages.products_count', ['count' => 32]) }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-content" id="category-search-list-content">
                                <div class="tab-pane fade show active" id="cat-all-pane" role="tabpanel"
                                    aria-labelledby="cat-all" tabindex="0">
                                    <div class="category-search-list-item">
                                        <div class="row" id="products-grid">
                                            @forelse($allProducts as $rowIndex => $productRow)
                                            <div class="col-md-6 col-xl-4 col-xxl-3 product-grid-item">
                                                {{-- Thêm class product-grid-item --}}
                                                <div class="product-item position-relative">
                                                    @if ($productRow['is_new'] ?? false)
                                                    <div class="product-attr">
                                                        <span class="product-new">{{ __('messages.new') }}</span>
                                                    </div>
                                                    @endif

                                                    <div class="product-favourite">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="33"
                                                            height="33" fill="black" class="bi bi-heart"
                                                            viewBox="0 0 16 16">
                                                            <path
                                                                d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                                                        </svg>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="33"
                                                            height="33" fill="currentColor" class="bi bi-heart-fill"
                                                            viewBox="0 0 16 16">
                                                            <path fill-rule="evenodd"
                                                                d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314" />
                                                        </svg>
                                                    </div>

                                                    <a href="{{ route('product.detail', ['slug' => $productRow['slug']]) }}"
                                                        class="link-overlay">
                                                        <div class="product-item-img bg-img-contain ratio-1-1"
                                                            data-bg="{{ $productRow['image'] ?? asset('images/no-image.png') }}">
                                                        </div>
                                                    </a>

                                                    <div class="product-item-line"></div>

                                                    <div class="product-item-category d-flex align-items-center gap-2">
                                                        <a
                                                            href="{{ route('category', ['slug' => $productRow['category_slug'] ?? '']) }}">{{ $productRow['category_name'] ?? '' }}</a>
                                                        <div class="product-item-star text-nowrap">
                                                            <i class="bi bi-star-fill"></i>
                                                            <i class="bi bi-star-fill"></i>
                                                            <i class="bi bi-star-fill"></i>
                                                            <i class="bi bi-star-fill"></i>
                                                            <i class="bi bi-star-fill"></i>
                                                        </div>
                                                    </div>

                                                    <h3
                                                        class="product-item-title text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                                                        <a class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2"
                                                            href="{{ route('product.detail', ['slug' => $productRow['slug']]) }}">
                                                            {{ $productRow['product_name'] ?? '' }}
                                                        </a>
                                                    </h3>

                                                    <div class="product-item-excerpt d-none text-muted fs-14 mb-3 line-3">
                                                        {{ Str::limit(strip_tags($productRow['excerpt'] ?? ''), 150) }}
                                                    </div>

                                                    <div class="product-price d-flex gap-3 align-items-center">
                                                        <span
                                                            class="fs-24 font-hanzel text-red">{{ $productRow['price_display'] ?? __('messages.contact') }}</span>
                                                        <span
                                                            class="fs-14 text-red">{{ __('messages.vat_included') }}</span>
                                                    </div>

                                                    <div
                                                        class="d-flex align-items-center justify-content-between mt-2 product-item-actions">
                                                        <div class="product-item-view">
                                                            <a class="fs-16 text-black"
                                                                href="{{ route('product.detail', ['slug' => $productRow['slug']]) }}">
                                                                {{ __('messages.detail') }}
                                                            </a>
                                                        </div>
                                                        <div class="cat-link d-flex align-items-center">
                                                            <a href="javascript:void(0)" class="w-100 add-to-cart-btn"
                                                                data-product-id="{{ $productRow['id'] ?? '' }}"
                                                                data-product-name="{{ $productRow['product_name'] ?? '' }}"
                                                                data-add-to-cart-url="{{ route('cart.add') }}"
                                                                title="{{ __('messages.add_to_cart') }}">
                                                                <img
                                                                    src="{{ asset('langding/imgs/category/cart-icon.svg') }}">
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
                                        @if (is_object($paginated) && method_exists($paginated, 'hasPages') && $paginated->hasPages())
                                        @php
                                        $shopPagination = [
                                        'current_page' => $paginated->currentPage(),
                                        'last_page' => $paginated->lastPage(),
                                        ];
                                        @endphp
                                        <div class="pt-4 border-top">
                                            @include('langding.components.pagination', [
                                            'pagination' => $shopPagination,
                                            'page_param' => 'page',
                                            ])
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('langding.components.promotion-slide')

@push('styles')
<style>
    @media (max-width: 767.98px) {
        .page-shop #products-grid>.product-grid-item {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .category-search-list .category-search-list-item .product-item .cat-link {
            margin-left: 20px;
        }

        .page-shop #products-grid.list-view .product-item-actions {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100%;
            gap: 0px;
        }

        .page-shop #products-grid .product-item-category {
            min-width: 0;
        }

        .page-shop #products-grid .product-item-category a {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .page-shop #products-grid .product-item-category .product-item-star {
            flex: 0 0 auto;
        }

        #products-grid.list-view .product-price span.fs-24 {
            font-size: 14px !important;
        }
        .category-search-filter-display {
            margin-bottom: 0px !important;
        }
        .category-search-list .category-search-filter-field-wrap {
            width: 260px;
        }
    }
</style>
@endpush


@push('scripts')
<script>
    $(function() {
        const SHOP_VIEW_MODE_KEY = 'shop_view_mode';
        const VIEW_MODE = {
            GRID: 'grid',
            LIST: 'list',
        };
        const PRICE_GAP = 10000;

        const $productsGrid = $('#products-grid');
        const $viewButtons = $('.category-search-filter-display-icon');

        const $priceMinRange = $('#price-range-min');
        const $priceMaxRange = $('#price-range-max');
        const $priceProgress = $('#price-range-progress');
        const $priceMinValue = $('#price-min-value');
        const $priceMaxValue = $('#price-max-value');
        const $priceMinDisplay = $('#price-range-display-min');
        const $priceMaxDisplay = $('#price-range-display-max');

        const $filterSort = $('#select2-filter-sort');
        const $filterType = $('#select2-filter-type');
        const $filterPagination = $('#select2-filter-pagination');
        const $filterCategory = $('#select2-filter-cat');
        const $filterButton = $('.btn-search-product');

        init();

        function init() {
            hydrateBackgroundImages();
            bindEvents();
            syncPriceRange();
            restoreViewMode();
        }

        function bindEvents() {
            $viewButtons.on('click', handleViewModeChange);

            $priceMinRange.on('input change', syncPriceRange);
            $priceMaxRange.on('input change', syncPriceRange);

            $filterSort.on('change', applyFiltersToUrl);
            $filterType.on('change', applyFiltersToUrl);
            $filterPagination.on('change', applyFiltersToUrl);

            $filterButton.on('click', function(e) {
                e.preventDefault();
                applyFiltersToUrl();
            });

            bindCategoryCollapseEvents();
        }

        function hydrateBackgroundImages() {
            $('.product-item-img[data-bg]').each(function() {
                const url = $(this).attr('data-bg');
                if (!url) return;

                $(this).css('background-image', `url("${url}")`);
            });
        }

        function handleViewModeChange() {
            const viewMode = $(this).data('view');
            if (!viewMode) return;

            applyViewMode(viewMode);
            localStorage.setItem(SHOP_VIEW_MODE_KEY, viewMode);
        }

        function restoreViewMode() {
            const savedViewMode = localStorage.getItem(SHOP_VIEW_MODE_KEY) || VIEW_MODE.GRID;
            applyViewMode(savedViewMode);
        }

        function applyViewMode(viewMode) {
            updateViewButtonState(viewMode);

            if (viewMode === VIEW_MODE.LIST) {
                enableListView();
            } else {
                enableGridView();
            }

            animateProductsGrid();
        }

        function updateViewButtonState(viewMode) {
            $viewButtons.removeClass('active');
            $viewButtons.filter(`[data-view="${viewMode}"]`).addClass('active');
        }

        function enableListView() {
            $productsGrid.addClass('list-view').removeClass('grid-view');

            $('.product-item').each(function() {
                const $item = $(this);

                if ($item.find('.product-item-content').length) return;

                const $category = $item.find('.product-item-category');
                const $title = $item.find('.product-item-title');
                const $excerpt = $item.find('.product-item-excerpt');
                const $price = $item.find('.product-price');
                const $actions = $item.find('.product-item-actions');
                const $line = $item.find('.product-item-line');

                const $contentWrapper = $('<div class="product-item-content"></div>');
                $contentWrapper.append($category, $title, $excerpt, $price, $actions);

                $line.remove();
                $item.find('a.link-overlay').after($contentWrapper);
            });

            $('.product-item-excerpt').removeClass('d-none');
        }

        function enableGridView() {
            $productsGrid.removeClass('list-view').addClass('grid-view');

            $('.product-item').each(function() {
                const $item = $(this);
                const $contentWrapper = $item.find('.product-item-content');

                if (!$contentWrapper.length) return;

                $contentWrapper.children().each(function() {
                    $item.append($(this));
                });

                $contentWrapper.remove();

                if (!$item.find('.product-item-line').length) {
                    $item.find('a.link-overlay').after('<div class="product-item-line"></div>');
                }
            });

            $('.product-item-excerpt').addClass('d-none');
        }

        function animateProductsGrid() {
            $productsGrid.css('opacity', '0');

            setTimeout(function() {
                $productsGrid.css('opacity', '1');
            }, 150);
        }

        function bindCategoryCollapseEvents() {
            $(document).on('shown.bs.collapse', '.category-children, .category-grandchildren', function() {
                const targetId = $(this).attr('id');
                $(`[data-bs-target="#${targetId}"]`).find('i').css('transform', 'rotate(180deg)');
            });

            $(document).on('hidden.bs.collapse', '.category-children, .category-grandchildren', function() {
                const targetId = $(this).attr('id');
                $(`[data-bs-target="#${targetId}"]`).find('i').css('transform', 'rotate(0deg)');
            });

            $(document).on('click', '.category-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        }

        function syncPriceRange() {
            if (!$priceMinRange.length || !$priceMaxRange.length) return;

            let minValue = parseInt($priceMinRange.val() || 0, 10);
            let maxValue = parseInt($priceMaxRange.val() || 0, 10);

            const minLimit = parseInt($priceMinRange.attr('min') || 0, 10);
            const maxLimit = parseInt($priceMaxRange.attr('max') || 0, 10);
            const activeElement = document.activeElement;

            if (maxValue - minValue < PRICE_GAP) {
                if (activeElement === $priceMinRange[0]) {
                    minValue = maxValue - PRICE_GAP;
                    $priceMinRange.val(minValue);
                } else {
                    maxValue = minValue + PRICE_GAP;
                    $priceMaxRange.val(maxValue);
                }
            }

            minValue = Math.max(minLimit, minValue);
            maxValue = Math.min(maxLimit, maxValue);

            updatePriceProgress(minValue, maxValue, minLimit, maxLimit);
            updatePriceDisplay(minValue, maxValue);
        }

        function updatePriceProgress(minValue, maxValue, minLimit, maxLimit) {
            const totalRange = Math.max(1, maxLimit - minLimit);
            const leftPercent = ((minValue - minLimit) / totalRange) * 100;
            const rightPercent = ((maxValue - minLimit) / totalRange) * 100;

            $priceProgress.css({
                left: `${leftPercent}%`,
                right: `${100 - rightPercent}%`,
            });
        }

        function updatePriceDisplay(minValue, maxValue) {
            $priceMinValue.val(minValue);
            $priceMaxValue.val(maxValue);
            $priceMinDisplay.text(formatPriceVND(minValue));
            $priceMaxDisplay.text(formatPriceVND(maxValue));
        }

        function formatPriceVND(value) {
            return Number(value || 0).toLocaleString('vi-VN') + 'đ';
        }

        function getSelectedAttributeValueIds() {
            return $('.filter-attr-value:checked')
                .map(function() {
                    return $(this).val();
                })
                .get();
        }

        function buildSearchParams() {
            const url = new URL(window.location.href);
            const params = url.searchParams;

            const sort = $filterSort.val() || '';
            const perPage = $filterPagination.val() || '';
            const type = $filterType.val() || '';
            const categoryIds = $filterCategory.val() || [];
            const attributeValueIds = getSelectedAttributeValueIds();

            const minPrice = $priceMinValue.val() || '';
            const maxPrice = $priceMaxValue.val() || '';
            const defaultMinPrice = $priceMinRange.attr('min') || '';
            const defaultMaxPrice = $priceMaxRange.attr('max') || '';

            params.delete('page');

            setOrDeleteParam(params, 'sort_by', sort);
            setOrDeleteParam(params, 'per_page', perPage);
            setOrDeleteParam(params, 'filter_type', type);

            params.delete('is_new');
            params.delete('is_featured');
            params.delete('is_bestseller');

            if (type === 'is_new') params.set('is_new', '1');
            if (type === 'is_featured') params.set('is_featured', '1');
            if (type === 'is_bestseller') params.set('is_bestseller', '1');

            replaceArrayParams(params, 'category_ids[]', 'category_ids', categoryIds);
            replaceArrayParams(params, 'attribute_value_ids[]', 'attribute_value_ids', attributeValueIds);

            params.delete('min_price');
            params.delete('max_price');

            const hasCustomPriceRange =
                minPrice &&
                maxPrice &&
                (minPrice !== defaultMinPrice || maxPrice !== defaultMaxPrice);

            if (hasCustomPriceRange) {
                params.set('min_price', minPrice);
                params.set('max_price', maxPrice);
            }

            return {
                pathname: url.pathname,
                query: params.toString(),
            };
        }

        function applyFiltersToUrl() {
            const {
                pathname,
                query
            } = buildSearchParams();
            window.location.href = query ? `${pathname}?${query}` : pathname;
        }

        function setOrDeleteParam(params, key, value) {
            if (value) {
                params.set(key, value);
            } else {
                params.delete(key);
            }
        }

        function replaceArrayParams(params, arrayKey, fallbackKey, values) {
            params.delete(arrayKey);
            params.delete(fallbackKey);

            values.forEach(function(value) {
                params.append(arrayKey, value);
            });
        }
    });
</script>
@endpush
@endsection