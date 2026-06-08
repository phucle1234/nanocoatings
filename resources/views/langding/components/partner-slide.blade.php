@if(isset($partnerBanners['category']) && $partnerBanners['category'])
<div id="box-partner" class="box-partner" style="background-image: url('{{ $partnerBanners['category_bg_image'] ?? url('langding/imgs/partner-bg.png') }}');">
    <div class="container-fluid">
        <div class="scroll-animate" data-animate="fadeInUp">
            <div class="title-with-line fw-500 fs-20 text-center text-light-red text-uppercase">{!! $partnerBanners['category']->category_name ?? __('messages.partners') !!}</div>

            @if(isset($partnerBanners['category']) && $partnerBanners['category'])
            {{-- Tiêu đề từ danh mục --}}
            <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 text-white">
                {!! $partnerBanners['category']->category_description ?? __('messages.partners_title') !!}
            </h2>
            <div class="text-center fs-16 mt-3 text-white">
                {{ $partnerBanners['category']->meta_description ?? __('messages.partners_description') }}
            </div>
            @else
            {{-- Fallback --}}
            <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 text-white">
                {!! __('messages.partners_title') !!}
            </h2>
            <div class="text-center fs-16 mt-3 text-white">
                {{ __('messages.partners_description') }}
            </div>
            @endif
        </div>

        <div class="box-partner-slider mt-5 scroll-animate" data-animate="fadeInUp">

            @if (isset($partnerBanners['banners']) && $partnerBanners['banners']->count() > 0)
                @php
                    $columnsPerRow = 7;
                    $rowsPerSlide = 2;
                    $itemsPerSlide = $columnsPerRow * $rowsPerSlide; // 14 logo / slide
                    $partnerSlides = $partnerBanners['banners']->values()->chunk($itemsPerSlide);
                @endphp

                @foreach ($partnerSlides as $slidePartners)
                    <div class="partner-slider-items">
                        @foreach ($slidePartners as $partner)
                            <div class="partner-slider-item">
                                <a href="{{ url('/') }}"><img src="{{ $partner->image }}" alt="{{ $partner->title }}" title="{{ $partner->title }}" class="img-fluid w-100"></a>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
        @else
        {{-- Fallback nếu không có đối tác --}}
        <div class="box-partner-slider mt-5 scroll-animate" data-animate="fadeInUp"
            style="background-image: url('{{ url('langding/imgs/partner-bg.png') }}');">
            <div class="partner-slider-items">
                <div class="partner-slider-item">
                    <img src="{{ asset('langding/imgs/partner-1.png') }}" alt="Partner" class="img-fluid w-100">
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
