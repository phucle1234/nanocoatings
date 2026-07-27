    <div class="box-slider-2">
        <div class="box-slider-2-items">
            @if (isset($homeSliderBanners['banners']) && $homeSliderBanners['banners']->count() > 0)
                @foreach ($homeSliderBanners['banners'] as $banner)
                @if ($loop->first && !empty($banner->video_url))
                        <div class="slider-item slide-video" data-slide-type="video">
                            <video class="hero-slide-video" src="{{ $banner->video_url }}" muted playsinline autoplay preload="auto"></video>
                            <div class="hero-video-controls" role="group" aria-label="Điều khiển âm lượng video">
                                <div class="hero-video-volume-wrap">
                                    <input type="range" class="hero-video-volume" min="0" max="100" value="80" step="1" orient="vertical" aria-label="Âm lượng video" aria-orientation="vertical">
                                </div>
                                <button type="button" class="hero-video-mute-btn" aria-label="Tắt âm thanh">
                                    <svg class="icon-muted" viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 5 6 9H3v6h3l5 4V5Z" fill="currentColor"/>
                                        <path d="M16 9l5 6M21 9l-5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <svg class="icon-unmuted" viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 5 6 9H3v6h3l5 4V5Z" fill="currentColor"/>
                                        <path d="M15.5 8.5a5 5 0 0 1 0 7M18.5 6a9 9 0 0 1 0 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
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
