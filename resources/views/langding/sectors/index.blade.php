@extends('langding.index')
@section('title', __('messages.application_sectors'))
@section('langding_content')
    <div class="page-sector-hub">
        <div class="page-sector-hub-hero">
            <div class="container-fluid">
                <nav aria-label="breadcrumb" class="sector-hub-breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}" class="text-white-50">{{ __('messages.breadcrumb_home') }}</a>
                        </li>
                        <li class="breadcrumb-item active text-white" aria-current="page">
                            {{ __('messages.application_sectors') }}
                        </li>
                    </ol>
                </nav>

                <div class="sector-hub-heading scroll-animate" data-animate="fadeInUp">
                    <div class="title-with-line fw-500 fs-18 text-center text-light-red">
                        {{ __('messages.application_sectors') }}
                    </div>
                    <h1 class="font-hanzel fs-32 fw-400 text-center mt-2 text-white sector-hub-title">
                        {{ __('messages.application_sectors_heading') }}
                    </h1>
                </div>
            </div>
        </div>

        <div class="page-sector-hub-body">
            <div class="container-fluid">
                @if ($sectors->isNotEmpty())
                    <div class="sector-hub-grid">
                        @foreach ($sectors as $sector)
                            <a href="{{ route('sectors.show', $sector->display_slug) }}"
                                class="sector-card scroll-animate" data-animate="fadeInUp">
                                <div class="sector-card-media bg-img-cover"
                                    style="background-image: url('{{ $sector->cover_image ?: asset('langding_nano/imgs/Slection3.png') }}');">
                                    <div class="sector-card-overlay" aria-hidden="true"></div>
                                    <div class="sector-card-body">
                                        <h2 class="sector-card-title font-hanzel">{{ $sector->display_name }}</h2>
                                        @if (!empty($sector->display_description))
                                            <p class="sector-card-desc">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($sector->display_description), 140) }}
                                            </p>
                                        @endif
                                        <span class="sector-card-cta">
                                            {{ __('messages.sector_explore') }}
                                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="sector-hub-empty text-center py-5">
                        <p class="text-white fs-16 mb-0">{{ __('messages.no_categories') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
