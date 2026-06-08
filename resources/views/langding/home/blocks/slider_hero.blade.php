    <div class="box-slider-2">
        <div class="box-slider-2-items">
            @if (isset($homeSliderBanners['banners']) && $homeSliderBanners['banners']->count() > 0)
                @foreach ($homeSliderBanners['banners'] as $banner)
                    @if (isset($banner->all_images) && count($banner->all_images) > 0)
                        @foreach ($banner->all_images as $image)
                            @if ($banner->url)
                                <div class="slider-item" style="--banner-image: url('{{ $image }}'); cursor: pointer;"
                                    onclick="window.open('{{ $banner->url }}', '_blank')" data-url="{{ $banner->url }}">
                                </div>
                            @else
                                <div class="slider-item"
                                    style="background: url('{{ $image }}') no-repeat center center;background-size: cover;">
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endif
        </div>
    </div>
