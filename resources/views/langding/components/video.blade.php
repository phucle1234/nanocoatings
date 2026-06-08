<div id="box-video" class="box-video" style="background: url('{{ asset('langding_nano/imgs/Slection6.png') }}') no-repeat center center;background-size: cover;">
    <div class="container-fluid">
        <div class="scroll-animate" data-animate="fadeInUp">
            @if(isset($introductionBanners['category']) && $introductionBanners['category'])
            <div class="title-with-line fw-500 fs-18 text-center text-light-red text-uppercase">
                {!! $introductionBanners['category']->category_name ?? __('messages.introduction') !!}
            </div>
            <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 mb-0 text-white">
                {{ $introductionBanners['category']->meta_title ?? __('messages.' . 'casu' . 'mina') }}
            </h2>
            <div class="video-intro fw-400 fs-14 text-center mt-3 mx-auto text-white">
                {!! $introductionBanners['category']->meta_description ?? __('messages.introduction_description') !!} <a href="{{ route('about') }}" class="text-white fw-500">({{ __('messages.detail') }})</a>
            </div>

            @else
            <div class="title-with-line fw-500 fs-18 text-center text-light-red">{{ __('messages.introduction') }}</div>
            <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 mb-0 text-white">{{ __('messages.' . 'casu' . 'mina') }}</h2>
            <div class="video-intro fw-400 fs-14 text-center mt-3 mx-auto text-white line-3">
                {{ __('messages.introduction_description') }}
            </div>
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
