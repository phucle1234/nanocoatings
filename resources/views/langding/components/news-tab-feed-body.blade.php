@php
$postsList = collect($posts ?? []);
@endphp
@if($postsList->count() > 0)
<div class="box-media-slider-item">
    <div class="row flex-wrap">
        <div class="col-12 col-lg-6">
            @foreach($postsList->slice(0, 2) as $post)
                @include('langding.components.news-tab-feed-item', ['post' => $post, 'iteration' => $loop->iteration])
            @endforeach
        </div>
        <div class="col-12 col-lg-6 mt-4 mt-lg-0">
            @foreach($postsList->slice(2, 2) as $post)
                @include('langding.components.news-tab-feed-item', ['post' => $post, 'iteration' => $loop->iteration])
            @endforeach
        </div>
    </div>
</div>
@else
<div class="text-center py-5">
    <div class="mb-4">
        <i class="fas fa-newspaper fa-3x text-muted"></i>
    </div>
    <h3 class="font-hanzel fs-32">{{ __('messages.no_news') }}</h3>
    <p class="fs-18">{{ __('messages.no_news_in_category') }}</p>
</div>
@endif
