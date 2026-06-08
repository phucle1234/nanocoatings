@extends('langding.index')
@section('title', __('messages.distribution_system'))

@section('langding_content')
<div class="page-branch page-distribution-system">
    <div class="box-location pb-0">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}" class="fs-15 text-black">{{ __('messages.home') }}</a>
                    </li>
                    <li class="breadcrumb-item active fs-15 text-black" aria-current="page">
                        {{ __('messages.distribution_system') }}
                    </li>
                </ol>
            </nav>

            <h2 class="font-hanzel fs-32 fw-400 text-center mt-4 title">
                {{ __('messages.distribution_system') }}
            </h2>

            <div class="box-location-intro text-center fs-14 mt-4 mx-auto">
                {{ __('messages.distribution_intro') }}<br>
                {{ __('messages.distribution_intro_2') }}
            </div>

            @include('langding.components.dealer-search-card', ['kicker' => __('messages.distribution_system')])
        </div>
    </div>

    <div class="branch-content-item distribution-content-item">
        <div class="container-fluid">
            <div class="row map-fullscreen" id="distributionLayoutRow">
                <div class="col-xl-6 distribution-list-column d-none" id="distributionListColumn">
                    <div class="branch-content-location">
                        <div class="branch-content-location-title d-flex align-items-center justify-content-between border-bottom border-dark-subtle">
                            <div class="fs-28 fw-700 text-uppercase title">
                                {{ __('messages.distribution_system_real') }}
                                <span class="dealer-count">(<span id="distributor-count">0</span>)</span>
                            </div>
                        </div>

                        <div class="nav nav-pills d-block" role="tablist" aria-orientation="vertical" id="distributorList"></div>

                        <div id="noResults" class="text-center py-5" style="display: none;">
                            <h4 class="text-muted fs-18">{{ __('messages.no_dealers_found') }}</h4>
                            <p class="text-muted fs-14">{{ __('messages.try_another_keyword') }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 distribution-map-column position-relative" id="distributionMapColumn">
                    <div id="mapbox"></div>
                    <div class="left-map position-absolute">Casumina.com.vn</div>
                    <div class="right-map position-absolute">Casumina.com.vn</div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('langding.components.category-search-links')

@include('langding.components.promotion-slide')
@endsection

@include('langding.components.mapbox-assets')

@push('styles')
<style>
    .page-distribution-system .distribution-content-item {
        margin-top: 24px;
    }

    .page-distribution-system #mapbox {
        width: 100%;
        height: 800px;
        min-height: 600px;
    }

    .page-distribution-system .map-fullscreen #mapbox {
        height: calc(100vh - 120px);
        min-height: 700px;
    }

    .page-distribution-system .distribution-list-column .branch-content-location .nav-pills,
    .page-distribution-system #distributorList {
        max-height: 737px;
        overflow-y: auto;
    }

    .page-distribution-system .distributor-item {
        cursor: pointer;
    }

    .page-distribution-system .distributor-item.is-active {
        background: #f8f8f8;
    }

    .page-distribution-system .distributor-toggle,
    .page-distribution-system .map-link {
        height: 54px;
        width: 54px;
        border: 1px solid #6D6D6D;
        color: var(--bs-black);
        background-color: var(--bs-white);
        text-align: center;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .page-distribution-system .hot-phone {
        height: 54px;
        width: 54px;
        border-radius: 50%;
        background: #c9161d;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 20px;
        font-weight: 700;
    }

    .page-distribution-system .showroom-list {
        padding-left: 18px;
        border-left: 2px solid #c9161d;
    }

    .page-distribution-system .showroom-child-item:last-child {
        padding-bottom: 0 !important;
    }

    @media (max-width: 1199.98px) {

        .page-distribution-system #mapbox,
        .page-distribution-system .map-fullscreen #mapbox {
            height: 600px;
            min-height: 600px;
        }
    }

    @media (max-width: 575.98px) {

        .page-distribution-system #mapbox,
        .page-distribution-system .map-fullscreen #mapbox {
            height: 380px;
            min-height: 380px;
        }

        .page-distribution-system .distributor-toggle,
        .page-distribution-system .hot-phone {
            width: 38px;
            height: 38px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    window.distributionPageConfig = {
        routes: {
            searchDistributors: @json(route('api.distribution.search-distributors')),
            searchNearestDistributors: @json(route('api.distribution.search-nearest-distributors')),
            distributorShowrooms: @json(route('api.distribution.distributor-showrooms', ['code' => '__CODE__'])),
        },
        locale: @json(app()->getLocale()),
        countries: @json($countries ?? []),
        csrfToken: @json(csrf_token()),
        translations: {
            pleaseSelectProvince: @json(__('messages.please_select_province')),
            searching: @json(__('messages.searching')),
            directions: @json(__('messages.directions')),
            contact: @json(__('messages.contact')),
            products: @json(__('messages.products')),
            provinceCity: @json(__('messages.province_city')),
            nearestYou: @json(__('messages.nearest_you')),
            casuminaDealer: @json(__('messages.casumina_dealer')),
            noDealersFound: @json(__('messages.no_dealers_found')),
            tryAnotherKeyword: @json(__('messages.try_another_keyword')),
            showroomList: @json(__('messages.store_system')),
            noShowroomsFound: @json(__('messages.no_showrooms_found')),
            locationNotSupported: @json(__('messages.location_not_supported')),
            locationError: @json(__('messages.location_error')),
            pleaseSelectLocationCategory: @json(__('messages.please_select_location_category')),
        }
    };
</script>
<script src="{{ asset('langding/js/distribution-mapbox.js') }}"></script>
@endpush