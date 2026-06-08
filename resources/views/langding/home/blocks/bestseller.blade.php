    <div id="box-products-sales" class="box-products-sales" style="background: url('{{ asset('langding_nano/imgs/Slection5.png') }}') no-repeat center center;background-size: cover;">
        <div class="container-fluid">
            <div class="scroll-animate" data-animate="fadeInUp">
                <div class="title-with-line fw-500 fs-20 text-center text-light-red">{{ __('messages.products') }}</div>
                <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 text-white">
                    {{ __('messages.bestseller_products') }}
                </h2>
            </div>
            <div class="products-sales-slider scroll-animate" data-animate="fadeInUp">
                @forelse($bestsellerProducts as $product)
                    <div class="products-sales-slider-item">
                        <div class="bg-img-cover products-sales-slider-img ratio-1-1"
                            style="background-image: url('{{ $product->image }}');">
                            <a href="{{ route('product.detail', ['slug' => $product->slug ?? $product->id]) }}"
                                class="stretched-link"></a>
                        </div>
                        {{-- <div class="btn btn-outline-secondary btn-detail shadow">
                            <a href="{{ route('product.detail', ['slug' => $product->slug ?? $product->id]) }}"
                                class="d-flex align-items-center gap-1">
                                <span class="fs-14 fw-500 text-white">{{ __('messages.detail_uppercase') }}</span>
                                <svg width="16" height="15" viewBox="0 0 16 15" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M5.98018 4.25661L11.5315 3.77093M11.5315 3.77093L12.0172 9.3223M11.5315 3.77093L5.01852 11.5328"
                                        stroke="white" stroke-width="1.12583" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </a>
                        </div> --}}
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
