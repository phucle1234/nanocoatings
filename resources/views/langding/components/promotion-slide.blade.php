@if(isset($promotionBanners['category']) && $promotionBanners['category']->is_active !== 'n')
<div id="box-news-info" class="box-news-info" style="background: url('{{ $promotionBanners['category_bg_image'] ?? url('langding/imgs/section-info-bg111.png') }}') no-repeat center center;background-size: cover;">
    <div class="container-fluid">
        <div class="scroll-animate title-group" data-animate="fadeInUp">
            <div class="title-with-line fw-500 fs-18 text-center text-light-red text-uppercase">
                {!! $promotionBanners['category']->category_name ?? __('messages.promotion_info') !!}
            </div>
        </div>

        @if(isset($promotionBanners['banners']) && $promotionBanners['banners']->count() > 0)
        <div class="news-slider scroll-animate" data-animate="fadeInUp">
            @foreach($promotionBanners['banners']->chunk(3) as $chunk)
            <div class="news-slider-item">
                <div class="news-slider-child">
                    @foreach($chunk as $promo)
                    <div class="news-slider-item-child">
                        {{-- Ảnh nền từ bài viết --}}

                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ $promo->image ?? asset('langding/imgs/new.png') }}');">
                        </div>

                        <div class="news-slider-item-info">
                            <a href="{{ $promo->url ?? ($promo->slug ? url('/post/' . $promo->slug) : '#') }}" class="stretched-link" target="_blank">
                                <div class="news-slider-item-info-title fw-300 text-white">
                                    {{ $promo->meta_title }}
                                </div>

                                <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">
                                    {!! $promo->content !!}
                                </div>

                                {{-- Link chi tiết --}}
                                <!-- <div class="news-slider-item-info-view mt-5">
                                    <a class="btn-khuyenmai d-inline-flex align-items-center gap-1 text-white fs-14 shadow"
                                        href="{{ $promo->slug ? url('/post/' . $promo->slug) : '#' }}">
                                        {{ __('messages.detail') }}
                                        <img src="{{ asset('langding/imgs/detail.svg') }}" alt="">
                                    </a>
                                </div> -->
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center text-white py-5">
            <p>{{ __('messages.no_promotions') }}</p>
        </div>
        @endif
    </div>
</div>
@endif