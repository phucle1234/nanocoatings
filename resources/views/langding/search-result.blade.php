@extends('langding.index')
@section('title', __('messages.search_results') . ' - Casumina')
@section('langding_content')

<div class="page-shop">
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-5">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="fs-14 text-muted">{{ __('messages.home') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#" class="fs-14 text-muted">{{ __('messages.search_results') }}</a>
                </li>
            </ol>
        </nav>

        <div class="category-search">
            <div class="category-search-list">
                <h1 class="fs-18 font-hanzel text-uppercase text-red mb-0 text-center">
                    {{ __('messages.search_results') }}
                    @if(!empty($keyword))
                    {{ __('messages.search_keyword') }}: <strong>{{ $keyword }}</strong>
                    @endif
                </h1>

                <div class="container-fluid">
                    @if(empty($keyword))
                    <div class="alert alert-secondary text-center">
                        {{ __('messages.enter_search_keyword') }}
                    </div>
                    @elseif($total === 0)
                    <div class="alert alert-secondary text-center">
                        {{ __('messages.no_search_results') }}
                    </div>
                    @else
                    <p class="mb-4 text-center">
                        {{ __('messages.found_search_results', ['count' => $total]) }}
                    </p>

                    <div class="tab-content" id="category-search-list-content">
                        <div class="tab-pane fade show active" id="cat-all-pane" role="tabpanel" tabindex="0">
                            <div class="category-search-list-item">
                                <div class="row" id="search-grid">
                                    @foreach($results as $item)
                                    @php
                                    $detailUrl = $item->detail_url
                                    ?? (
                                    $item->type === 'product'
                                    ? route('product.detail', ['slug' => $item->slug ?? '#'])
                                    : route('post.detail', ['slug' => $item->slug ?? '#'])
                                    );

                                    if ($item->type === 'product') {
                                    $images = $item->image_urls;

                                    if (is_string($images)) {
                                    $decoded = json_decode($images, true);
                                    $images = is_array($decoded) ? $decoded : [];
                                    }

                                    $image = is_array($images) ? ($images[0] ?? null) : null;
                                    $displayImage = $image ?: asset('images/no-image.png');
                                    } else {
                                    $postImages = $item->post_image_urls;

                                    if (is_string($postImages)) {
                                    $decoded = json_decode($postImages, true);
                                    $postImages = is_array($decoded) ? $decoded : [];
                                    }

                                    $postImage = is_array($postImages) ? ($postImages[0] ?? null) : null;
                                    $displayImage = $postImage ?: asset('images/no-image.png');
                                    }
                                    @endphp

                                    <div class="col-md-6 col-xl-4 col-xxl-3 product-grid-item mb-4">
                                        <div class="product-item position-relative h-100">


                                            <a href="{{ $detailUrl }}" class="link-overlay">
                                                <div class="product-item-img bg-img-contain ratio-1-1"
                                                    style="background-image: url('{{ $displayImage }}');">
                                                </div>
                                            </a>

                                            <div class="product-item-line"></div>

                                            <h3 class="product-item-title text-red fs-16 font-hanzel line-2 mt-3 mb-3">
                                                <a class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2"
                                                    href="{{ $detailUrl }}">
                                                    {{ $item->title ?? '' }}
                                                </a>
                                            </h3>


                                            <div class="d-flex align-items-center justify-content-between mt-2 product-item-actions">
                                                <div class="product-item-view">
                                                    <a class="fs-16 text-black" href="{{ $detailUrl }}">
                                                        {{ __('messages.detail') }}
                                                    </a>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @include('langding.components.pagination', ['pagination' => $pagination ?? null])
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('langding.components.promotion-slide')

<style>
    .breadcrumb-item+.breadcrumb-item::before {
        content: "/";
        color: #999;
    }

    @media (max-width: 767px) {
        .category-search-filter {
            justify-content: center;
        }
    }
</style>

@endsection