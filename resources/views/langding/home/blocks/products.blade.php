@isset($latestProducts)
@once
    @push('styles')
        <style>
            #latest-products { padding: 80px 0 100px; }
            #latest-products .product-item-img { width: 100%; max-width: 240px; aspect-ratio: 1; }
            #latest-products .product-item-title { min-height: 3em; overflow-wrap: anywhere; }
            #latest-products .product-price,
            #latest-products .product-item-category { flex-wrap: wrap; }
            #latest-products .product-item-category a { max-width: 100%; overflow-wrap: anywhere; }
            #latest-products .slick-dots { flex-wrap: wrap; justify-content: center; width: 100%; bottom: -40px; }
            #latest-products .block-heading::before { display: none; }
            #latest-products .block-heading-title { color: var(--bs-black) !important; text-shadow: none; }
            @media (max-width: 575px) {
                #latest-products { padding: 48px 0 80px; }
            }
        </style>
    @endpush
@endonce
<section id="latest-products" class="category-avenza-product-highlight bg-white text-black" aria-labelledby="latest-products-title">
    <div class="container-fluid">
        <div id="latest-products-title" class="mb-4">
            @if (!empty($isSectorPage))
                @include('langding.components.block-banner-heading', [
                    'banner' => [],
                    'fallbackTitle' => __('messages.newest_products')
                ])
            @else
                <h2 class="font-hanzel fs-32 fw-400 text-center mt-3 mb-0">{{ __('messages.newest_products') }}</h2>
            @endif
        </div>
        @if ($latestProducts->isNotEmpty())
            <div class="highlight-items" data-slick='{"slidesToScroll":4,"responsive":[{"breakpoint":1200,"settings":{"slidesToShow":3,"slidesToScroll":3}},{"breakpoint":992,"settings":{"slidesToShow":2,"slidesToScroll":2}},{"breakpoint":575,"settings":{"slidesToShow":1,"slidesToScroll":1}}]}'>
                @foreach ($latestProducts as $product)
                    <div class="highlight-item">
                        @php($productUrl = route('product.detail', ['slug' => $product['slug'] ?? $product['id']]))
                        <div class="product-item position-relative">
                            @if ($product['is_new'] ?? false)
                                <div class="product-attr"><span class="product-new">{{ __('messages.new') }}</span></div>
                            @endif
                            <a href="{{ $productUrl }}" class="link-overlay" aria-label="{{ $product['name'] }}">
                                <div class="product-item-img bg-img-contain" role="img" aria-label="{{ $product['name'] }}"
                                    style="background-image: url('{{ $product['image'] ?? asset('images/no-image.png') }}');"></div>
                            </a>
                            <div class="product-item-line"></div>
                            <div class="product-item-category d-flex align-items-center gap-2">
                                @if (!empty($product['category_slug']))
                                    <a href="{{ route('category', ['slug' => $product['category_slug']]) }}">{{ $product['category_name'] ?? __('messages.category') }}</a>
                                @endif
                                <div class="product-item-star text-nowrap" aria-hidden="true">
                                    @for ($star = 0; $star < 5; $star++)
                                        <i class="bi bi-star-fill"></i>
                                    @endfor
                                </div>
                            </div>
                            <h3 class="product-item-title text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                                <a class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2" href="{{ $productUrl }}">{{ $product['name'] }}</a>
                            </h3>
                            <div class="product-price d-flex gap-3 align-items-center">
                                <span class="fs-24 font-hanzel text-red lh-1">{{ $product['price_display'] ?? __('messages.contact') }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <a class="fs-16 text-black" href="{{ $productUrl }}">
                                    <span class="me-2">{{ __('messages.detail') }}</span><i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                                </a>
                                <div class="cat-link d-flex align-items-center">
                                    <a href="{{ $productUrl }}" class="w-100 add-to-cart-btn"
                                        data-product-id="{{ $product['id'] }}" data-product-name="{{ $product['name'] }}"
                                        data-add-to-cart-url="{{ route('cart.add') }}"
                                        title="{{ __('messages.add_to_cart') }}" aria-label="{{ __('messages.add_to_cart') }}">
                                        <img src="{{ asset('langding/imgs/category/cart-icon.svg') }}" alt="" width="30" height="30">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center py-5 mb-0">{{ __('messages.no_products') }}</p>
        @endif
    </div>
</section>
@endisset
