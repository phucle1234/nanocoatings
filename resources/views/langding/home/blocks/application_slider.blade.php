    <div class="box-slider">
        <div class="bg-slider no-slider"
            style="background: url('{{ $homeSliderBanners2['category_bg_image'] ?? '' }}') no-repeat center center;background-size: cover;">
        </div>
        <div class="box-slider-head scroll-animate animated" data-animate="fadeInUp">
            <div class="title-with-line fw-500 fs-18 text-center text-light-red">
            {{ $homeSliderBanners2['meta_title'] ?? 'Products' }}
            </div>
            <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 text-white">
                {{ $homeSliderBanners2['meta_description'] ?? 'Application Potential' }}
            </h2>
        </div>

        <div class="main-slider">
            <div class="main-slider-items">
                @if (isset($homeSliderBanners2['banners']) && $homeSliderBanners2['banners']->count() > 0)
                    @foreach ($homeSliderBanners2['banners'] as $banner)
                        @if (isset($banner->all_images) && count($banner->all_images) > 0)
                            @foreach ($banner->all_images as $image)
                                <div class="slider-item text-center">
                                    <div class="slider-content">
                                        <div class="slider-content-child">
                                            <a href="{{ $banner->url ?? '#' }}" target="_blank"><img
                                                    src="{{ $image }}" alt="{{ $banner->title ?? 'Banner' }}"></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
