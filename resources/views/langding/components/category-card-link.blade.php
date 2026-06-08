@php
    $isYoutube = ($category->link_type ?? 'detail') === 'youtube' && !empty($category->youtube_url);
    $detailHref = route('category', ['slug' => $category->category_translation_slug ?? $category->id]);
    $cardHref = $isYoutube ? $category->youtube_url : $detailHref;
    $linkLabel = $isYoutube ? __('messages.watch_video') : __('messages.detail');
@endphp

<div class="product-item-img position-relative mx-auto">
    <a href="{{ $cardHref }}" @if($isYoutube) data-fancybox @endif
        class="{{ $isYoutube ? 'category-video-thumb d-inline-block position-relative' : '' }}">
        <img src="{{ $category->category_image ?? asset('langding/imgs/product.png') }}"
            alt="{{ $category->category_name ?? 'Category' }}"
            class="img-fluid mx-auto" width="383">
        @if ($isYoutube)
            <div class="video-play-btn">
                <img src="{{ asset('langding_nano/imgs/Play.png') }}" alt="Play" width="64" height="64">
            </div>
        @endif
    </a>
</div>
<div class="product-item-view mt-3">
    <a class="text-white fs-16 d-flex align-items-center justify-content-center gap-2"
        href="{{ $cardHref }}" @if($isYoutube) data-fancybox @endif>
        <span>{{ $linkLabel }}</span>
        <svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2.00109 1.37635L9.28725 0.738899M9.28725 0.738899L9.92471 8.02506M9.28725 0.738899L0.738909 10.9264"
                stroke="#ffffff" stroke-width="1.47765" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </a>
</div>
