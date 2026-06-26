@php
    $links = $blockContentLinks[$block->section_type] ?? [];
    $guide = config($guideConfigKey . '.block_admin_guide.' . $block->section_type);
    $hasLinks = !empty($links['category']) || !empty($links['posts']) || !empty($links['manage']);
@endphp

@if ($hasLinks)
    <div class="d-flex flex-wrap gap-2 mt-2 sector-block-action-btns">
        @if (!empty($links['category']))
            <a href="{{ $links['category'] }}"
                class="btn btn-sm btn-outline-success text-nowrap"
                target="_blank" rel="noopener noreferrer"
                title="Ảnh nền section, tiêu đề và mô tả">
                <i class="la la-image"></i> Ảnh nền &amp; tiêu đề
            </a>
        @endif
        @if (!empty($links['posts']))
            <a href="{{ $links['posts'] }}"
                class="btn btn-sm btn-outline-primary text-nowrap"
                target="_blank" rel="noopener noreferrer"
                title="Ảnh slide / bài viết trong block">
                <i class="la la-newspaper"></i> Bài viết / Slider
            </a>
        @endif
        @if (!empty($links['manage']))
            <a href="{{ $links['manage'] }}"
                class="btn btn-sm btn-outline-primary text-nowrap"
                target="_blank" rel="noopener noreferrer">
                <i class="la la-external-link"></i> {{ $links['manage_label'] ?? 'Quản lý' }}
            </a>
        @endif
    </div>
@endif

@if ($guide)
    <p class="small text-muted mb-0 mt-2 sector-block-guide">{{ $guide }}</p>
@endif
