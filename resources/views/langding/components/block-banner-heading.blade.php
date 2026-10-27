@php
    $bannerData = is_array($banner ?? null) ? $banner : [];
    $category = $bannerData['category'] ?? null;
    $title = $category?->meta_title ?? ($fallbackTitle ?? '');
    $description = $category?->meta_description ?? ($fallbackDescription ?? null);
    $titleMargin = $titleMargin ?? 'mt-2';
    $ctaUrl = ($ctaUrl ?? null) ?: ($category?->url ?? null);
    // Tiêu đề ngắn: chữ to hơn để cụm tiêu đề cân đối
    $titleLength = mb_strlen(trim(strip_tags((string) $title)));
    $isShortTitle = $titleLength > 0 && $titleLength <= 18;
@endphp
<div class="block-heading {{ $isShortTitle ? 'block-heading--short' : '' }} {{ $titleMargin }}">
    @if ($titleLength > 0)
        <h2 class="block-heading-title font-hanzel fw-400 text-center mb-0 text-white">
            <span class="block-heading-title-text">{{ $title }}</span>
        </h2>
    @endif
    @if (!empty($description))
        <div class="block-heading-desc video-intro fw-400 text-center mx-auto text-white">
            {!! $description !!}
        </div>
    @endif
    @if (!empty($ctaUrl))
        <div class="block-heading-cta">
            <a class="block-heading-cta-link" href="{{ $ctaUrl }}">
                <span>{{ $ctaLabel ?? __('messages.detail') }}</span>
                <svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path d="M2.00109 1.37635L9.28725 0.738899M9.28725 0.738899L9.92471 8.02506M9.28725 0.738899L0.738909 10.9264"
                        stroke="currentColor" stroke-width="1.47765" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </div>
    @endif
</div>
