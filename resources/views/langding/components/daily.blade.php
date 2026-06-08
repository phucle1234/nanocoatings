<div class="box-location">
    <div class="container-fluid">

        <div class="title-with-line fw-500 fs-18 text-center text-light-red">
            {{ __('messages.authorized_dealer') }}
        </div>
        <h2 class="font-hanzel fs-32 fw-400 text-center mt-4 mt-0 title">
            {{ __('messages.distribution_system') }}
        </h2>
        <div class="box-location-intro text-center fs-14 mt-4 mx-auto">
            {{ __('messages.distribution_intro') }}<br>
            {{ __('messages.distribution_intro_2') }}
        </div>

        <div class="page-branch p-0 mt-5">
            @include('langding.components.dealer-search-card', ['kicker' => __('messages.distribution_system')])

            <div class="row map-fullscreen" id="dealerLayoutRow">
                <div class="col-xl-6 dealer-list-column d-none" id="dealerListColumn">
                    <div class="branch-content-location">
                        <div
                            class="branch-content-location-title d-flex align-items-center justify-content-between border-bottom border-dark-subtle">
                            <div class="fs-28 fw-700 text-uppercase title">
                                {{ __('messages.stores_dealers') }}
                                <span class="dealer-count">(<span id="dealer-count">0</span>)</span>
                            </div>
                        </div>
                        <ul class="nav nav-pills d-block" role="tablist" aria-orientation="vertical"
                            id="dealerListShow"></ul>
                        <div id="noResults" class="text-center py-5">
                            <h4 class="text-muted fs-18">{{ __('messages.no_dealers_found') }}</h4>
                            <p class="text-muted fs-14">{{ __('messages.try_another_keyword') }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 dealer-map-column position-relative" id="dealerMapColumn">
                    <div id="mapbox"></div>
                    <div class="left-map position-absolute">Casumina.com.vn</div>
                    <div class="right-map position-absolute">Casumina.com.vn</div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('langding.components.mapbox-assets')

@push('styles')
<style>
    .page-branch .branch-content-location .nav-pills .nav-link-item .map-link {
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

    .page-branch .branch-content-location .nav-pills .nav-link-item .map-link:hover {
        background-color: #f0f0f0;
        border-color: #6D6D6D;
        color: var(--bs-black);
    }

    .box-location .branch-content-location .nav-pills {
        max-height: 737px;
        overflow-y: auto;
    }

    #mapbox {
        height: 800px;
        width: 100%;
    }

    .map-fullscreen #mapbox {
        height: calc(100vh - 120px);
        min-height: 700px;
    }

    @media (max-width: 1199.98px) {
        .box-location .branch-content-location .nav-pills {
            max-height: 600px;
        }

        #mapbox {
            height: 600px;
        }

        .map-fullscreen #mapbox {
            height: 600px;
            min-height: 600px;
        }

        .page-branch .branch-content-location .nav-link-item-info h3 {
            font-size: var(--fs-16);
        }

        .page-branch .branch-content-location .nav-link-item-info ul li a {
            font-size: var(--fs-14);
        }

        .page-branch .branch-content-location .nav-pills .nav-link-item .map-link svg {
            width: 18px;
            height: 18px;
        }

        .page-branch .branch-content-location .nav-pills .nav-link-item .map-link {
            width: 34px;
            height: 34px;
        }
    }

    @media (max-width: 575.98px) {
        .box-location .branch-content-location .nav-pills {
            max-height: 400px;
        }

        #mapbox {
            height: 400px;
        }

        .map-fullscreen #mapbox {
            height: 300px;
            min-height: 300px;
        }
    }
</style>
@endpush
@push('scripts')
<script>
    window.branchPageConfig = {
        dealers: @json($dealers ?? []),
        routes: {
            searchDealers: @json(route('api.search-dealers')),
            searchNearestDealers: @json(route('api.search-nearest-dealers')),
            searchAllDealers: @json(route('api.search-all-dealers')),
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
            locationNotSupported: 'Trình duyệt của bạn không hỗ trợ Geolocation.',
            locationError: 'Không lấy được vị trí hiện tại của bạn.',
            pleaseSelectLocationCategory: 'Vui lòng chọn danh mục sản phẩm.',
        }
    };
</script>

<script src="{{ asset('langding/js/mapbox.js') }}"></script>


@endpush