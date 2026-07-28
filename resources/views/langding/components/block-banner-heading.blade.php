@php
    $bannerData = is_array($banner ?? null) ? $banner : [];
    $category = $bannerData['category'] ?? null;
    $title = $category?->meta_title ?? ($fallbackTitle ?? '');
    $description = $category?->meta_description ?? ($fallbackDescription ?? null);
    $titleMargin = $titleMargin ?? 'mt-2';
    $ctaUrl = $category?->url ?? null;
@endphp
<h2 class="font-hanzel fs-32 fw-400 text-center {{ $titleMargin }} mb-0 text-white">
    {{ $title }}
</h2>
@if (!empty($description))
    <div class="video-intro fw-400 fs-14 text-center mt-3 mx-auto text-white">
        {!! $description !!}
    </div>
@endif
@if (!empty($ctaUrl))
    <div class="mt-3 text-center">
        <a class="text-white fw-500" href="{{ $ctaUrl }}">
            <span>{{ $ctaLabel ?? __('messages.detail') }}</span>
        </a>
    </div>
@endif
