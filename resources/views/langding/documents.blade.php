@extends('langding.index')
@section('title', __('messages.documents'))
@section('langding_content')

<div class="page-posts-category">
    <div class="container-fluid">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mt-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="fs-14 text-black">{{ __('messages.home') }}</a>
                </li>
                @if(isset($breadcrumb) && is_array($breadcrumb))
                @foreach($breadcrumb as $item)
                @if(!empty($item['url']))
                <li class="breadcrumb-item">
                    <a href="{{ $item['url'] }}" class="fs-14 text-black">{{ $item['label'] }}</a>
                </li>
                @else
                <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
                @endif
                @endforeach
                @else
                <li class="breadcrumb-item active" aria-current="page">{{ __('messages.documents') }}</li>
                @endif
            </ol>
        </nav>

        {{-- Tiêu đề trang --}}
        @php
        $currentPage = (isset($breadcrumb) && is_array($breadcrumb) && count($breadcrumb) > 0)
        ? last($breadcrumb)['label']
        : null;
        @endphp
        <h1 class="font-hanzel fs-32 mt-4 fw-400 text-center">
            {{ $currentPage ?? __('messages.document_classification') }}
        </h1>

        {{-- Nội dung chính: folders hoặc danh sách file --}}
        <div class="documents-list mt-5">
            @if(!empty($folders))
            @include('langding.partials.documents-folders', [
            'folders' => $folders,
            'parentSlug' => $parentSlug ?? null,
            ])
            @else
            @include('langding.partials.documents-list', [
            'dangKiemCategories' => $dangKiemCategories ?? [],
            'dangKiemCategory' => $dangKiemCategory ?? ['name' => 'Tài liệu'],
            ])
            @endif
        </div>

    </div>
</div>

<style>
    html,
    body {
        scroll-snap-type: none !important;
    }

    #box-slider,
    #box-viewer,
    #box-products-sales,
    #box-news-info,
    #box-video,
    #box-category,
    #box-partner,
    #box-media,
    #box-location,
    footer {
        scroll-snap-align: none !important;
        scroll-snap-stop: unset !important;
    }

    .document-folder-item {
        transition: background 0.2s;
    }

    .document-tree .document-folder-item a:hover {
        background: #f8f9fa;
    }

    .document-item {
        transition: all 0.3s ease;
    }

    .document-item:hover {
        background: #f8f9fa;
    }

    .document-item .btn-link {
        text-decoration: none;
        white-space: nowrap;
    }
</style>

@endsection
