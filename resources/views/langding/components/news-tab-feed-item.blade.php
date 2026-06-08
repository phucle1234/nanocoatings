@php
$post = is_array($post) ? $post : (array) $post;
$slug = $post['slug'] ?? null;
$image = $post['image'] ?? asset('langding/imgs/media-2.png');
$title = $post['title'] ?? __('messages.news_title');
$dateRaw = $post['published_at'] ?? $post['created_at'] ?? null;
$dateStr = $dateRaw ? \Carbon\Carbon::parse($dateRaw)->format('d/m/Y') : '';
$detailUrl = $slug ? route('post.detail', ['slug' => $slug]) : '#';
@endphp
<div class="media-item-small media-item-small-{{ $iteration }} d-flex">
    <div class="bg-img-cover position-relative"
        style="background-image: url('{{ $image }}');">
        <a href="{{ $detailUrl }}" class="stretched-link"></a>
    </div>
    <div class="media-item-small-content p-3">
        <a href="{{ $detailUrl }}">
            <div class="calendar d-flex align-items-center gap-1 text-red">
                <img src="{{ asset('langding/imgs/calendar-white.svg') }}" alt="Calendar" class="img-fluid" width="21">
                <span class="fs-16">{{ $dateStr }}</span>
            </div>
            <div class="media-item-small-title mt-3">
                <h3 class="fs-16 text-black mb-0">{{ $title }}</h3>
                <div class="view-more d-flex align-items-center">
                    <span></span>
                    <img src="{{ asset('langding/imgs/view-more.png') }}" alt="Icon" class="img-fluid" width="18">
                </div>
            </div>
        </a>
    </div>
</div>
