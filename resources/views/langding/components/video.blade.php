@php
    $videoBanner = $introductionBanners ?? [];
    $videoBg = ($videoBanner['category_bg_image'] ?? null) ?: asset('langding_nano/imgs/Slection6.png');
@endphp
<div id="box-video" class="box-video" style="background: url('{{ $videoBg }}') no-repeat center center;background-size: cover;">
    <div class="container-fluid">
        <div class="scroll-animate" data-animate="fadeInUp">
            @if (!empty($introductionBanners['category']))
            @include('langding.components.block-banner-heading', [
                'banner' => $introductionBanners,
                'fallbackTitle' => __('messages.' . 'casu' . 'mina'),
                // Đang ở trang /about thì không hiện nút "Chi tiết" trỏ về chính nó
                'ctaUrl' => ($introductionBanners['category']->url ?? null) ?: (request()->routeIs('about') ? null : route('about')),
            ])
            @else
            @include('langding.components.block-banner-heading', [
                'banner' => [],
                'fallbackTitle' => __('messages.' . 'casu' . 'mina'),
                'fallbackDescription' => e(__('messages.introduction_description')),
            ])
            @endif
        </div>

        @if(isset($introductionBanners['banners']) && $introductionBanners['banners']->count() > 0)
        <div class="video-slider mx-auto scroll-animate" data-animate="fadeInUp">
            @foreach($introductionBanners['banners'] as $video)
            <div class="video-slider-item">
                <a href="{{ $video->canonical_url ?? '#' }}" data-fancybox>
                    <div class="bg-img-cover ratio-2-1"
                        style="background-image: url('{{ $video->image ?? asset('langding/imgs/video.jpg') }}');">
                        <div class="video-play-btn">
                            <img src="{{ asset('langding_nano/imgs/Play.png') }}" alt="Play">
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @else
        {{-- Fallback nếu không có video --}}
        <div class="video-slider mx-auto mt-5 scroll-animate" data-animate="fadeInUp">
            <div class="video-slider-item">
                <a href="https://www.youtube.com/watch?v=lyaOlS_IVx0" data-fancybox>
                    <div class="bg-img-cover ratio-2-1"
                        style="background-image: url('{{ url('langding/imgs/video.jpg') }}');">
                        <div class="video-play-btn">
                            <img src="{{ asset('langding/imgs/video.svg') }}" alt="Play">
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endif
        {{-- Statistics Section --}}

    </div>
    <div class="bg-video"></div>
    <!-- <div class="box-statistics">
        <div class="container-fluid">
            <div class="statistics-overlay">

                @if(isset($introductionBanners['category']->category_description) && $introductionBanners['category']->category_description)
                {!! $introductionBanners['category']->category_description !!}

                @else
                <div class="row g-0">
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">45M+</div>
                            <div class="stat-label">{{ __('messages.stat_tires_label') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">5M+</div>
                            <div class="stat-label">{{ __('messages.stat_dealers_label') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">2K+</div>
                            <div class="stat-label">{{ __('messages.stat_stores_label') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">30M+</div>
                            <div class="stat-label">{{ __('messages.stat_customers_label') }}</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div> -->
</div>
