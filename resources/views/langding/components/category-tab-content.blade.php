@php
    $isYoutube = ($category->link_type ?? 'detail') === 'youtube' && !empty($category->youtube_url);
    $hasMedia = $isYoutube || !empty($category->category_image);
    $hasText = !empty($category->category_meta_title)
        || !empty($category->category_meta_description)
        || !empty($category->category_description_html);
    $hasContent = $hasMedia || $hasText;
    $mediaHref = $isYoutube ? $category->youtube_url : null;
    $mediaImage = $category->category_image ?? asset('langding/imgs/product.png');
@endphp

<div class="category-tab-content scroll-animate" data-animate="fadeInUp">
    <div class="container-fluid">
        @if ($hasContent)
            @if (!empty($category->category_meta_title))
                <h3 class="category-tab-content-title font-hanzel fs-32 fw-400 text-center text-white text-uppercase mb-0">
                    {{ $category->category_meta_title }}
                </h3>
            @endif

            @if (!empty($category->category_meta_description))
                <p class="category-tab-content-subtitle font-hanzel fs-20 fw-400 text-center text-white mt-3 mb-0">
                    {{ $category->category_meta_description }}
                </p>
            @endif

            @if ($hasMedia)
                <div class="category-tab-content-media mx-auto mt-4">
                    @if ($isYoutube)
                        <a href="{{ $mediaHref }}" data-fancybox
                            class="category-video-thumb d-block position-relative">
                            <img src="{{ $mediaImage }}"
                                alt="{{ $category->category_name ?? 'Category' }}"
                                class="img-fluid w-100">
                            <div class="video-play-btn">
                                <img src="{{ asset('langding_nano/imgs/Play.png') }}" alt="Play" width="64"
                                    height="64">
                            </div>
                        </a>
                    @else
                        <img src="{{ $mediaImage }}"
                            alt="{{ $category->category_name ?? 'Category' }}"
                            class="img-fluid w-100">
                    @endif
                </div>
            @endif

            @if (!empty($category->category_description_html))
                <div class="category-tab-content-body fs-16 fw-300 text-white mx-auto mt-4">
                    {!! $category->category_description_html !!}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <p class="fs-18 text-white">{{ __('messages.no_content') }}</p>
            </div>
        @endif
    </div>
</div>
