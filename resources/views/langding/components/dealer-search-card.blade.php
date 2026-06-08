{{--
    Component: dealer-search-card
    Params:
      $kicker  — text hiển thị ở kicker (default: messages.distribution_system)
      $countries — collection các country để render select
--}}
<div class="box-location-search dealer-search-card mx-auto mb-2">
    <div class="dealer-search-head">
        <p class="dealer-search-kicker">{{ $kicker ?? __('messages.distribution_system') }}</p>
        <h3 class="dealer-search-title">{{ __('messages.search_method_title') }}</h3>
    </div>

    <div class="dealer-search-tabs" id="dealerSearchTabs">
        <button type="button" class="dealer-tab is-active"
            data-target="searchByArea">{{ __('messages.search_by_area') }}</button>
        <button type="button" class="dealer-tab"
            data-target="searchByLocation">{{ __('messages.search_by_location') }}</button>
    </div>

    <div class="dealer-search-panels">
        <div class="dealer-panel is-active" id="searchByArea">
            <div class="dealer-form-grid area-grid">
                <div class="field">
                    <label class="field-label" for="countrySelect">{{ __('messages.location') }}</label>
                    <div class="field-control has-leading-icon">
                        <span class="field-leading-icon position-absolute">
                            <img src="{{ asset('langding/imgs/world.png') }}" alt="{{ __('messages.location') }}">
                        </span>
                        <select class="form-select field-select select-country" style="width: 100%;"
                            aria-label="Country" id="countrySelect"
                            data-provinces-url="{{ url('/branch/provinces') }}">
                            @foreach ($countries as $country)
                            <option value="{{ $country->code ?? '' }}"
                                data-id="{{ $country->id ?? '' }}"
                                data-code="{{ $country->code ?? '' }}">
                                {{ app()->getLocale() === 'vi' ? $country->name_vi ?? '' : $country->name_en ?? '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="provinceSelect">{{ __('messages.province_city') }}</label>
                    <div class="field-control">
                        <select class="form-select field-select select-country" style="width: 100%;"
                            aria-label="Province" id="provinceSelect"
                            data-categories-url="{{ url('/branch/categories') }}" disabled>
                            <option selected value="">{{ __('messages.province_city') }}</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="productSelect">{{ __('messages.products') }}</label>
                    <div class="field-control">
                        <select class="form-select field-select select-product" style="width: 100%;"
                            aria-label="Product" id="productSelect" disabled>
                            <option selected value="" data-id="" data-code="">
                                {{ __('messages.products') }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="field action-field">
                    <button class="btn btn-search btn-go fw-700" type="button"
                        id="btnSearchDealer">{{ __('messages.search') }}</button>
                </div>
            </div>
        </div>

        <div class="dealer-panel" id="searchByLocation">
            <div class="dealer-form-grid location-grid">
                <div class="field">
                    <label class="field-label"
                        for="locationProductSelect">{{ __('messages.products') }}</label>
                    <div class="field-control">
                        <select class="form-select field-select select-product" style="width: 100%;"
                            aria-label="Chọn sản phẩm theo vị trí hiện tại" id="locationProductSelect">
                            <option value="" data-id="" data-code="">
                                {{ __('messages.products') }}
                            </option>
                            <option value="01" data-id="1" data-code="01">Săm lốp xe tải</option>
                            <option value="02" data-id="11" data-code="02">Săm lốp xe đạp</option>
                            <option value="03" data-id="15" data-code="03">Săm lốp xe máy</option>
                            <option value="04" data-id="23" data-code="04">Lốp PCR Advenza</option>
                        </select>
                    </div>
                </div>

                <div class="field action-field">
                    <button class="btn btn-search btn-go fw-700" type="button" id="btnGetLocation">
                        <span class="svg-icon">
                            <svg width="22" height="28" viewBox="0 0 22 28" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M11 0C8.08369 0.00344047 5.28779 1.16347 3.22564 3.22563C1.16348 5.28778 0.00345217 8.08367 1.17029e-05 11C-0.00348119 13.3832 0.774992 15.7018 2.21601 17.6C2.21601 17.6 2.51601 17.995 2.56501 18.052L11 28L19.439 18.047C19.483 17.994 19.784 17.6 19.784 17.6L19.785 17.597C21.2253 15.6996 22.0034 13.3821 22 11C21.9966 8.08367 20.8365 5.28778 18.7744 3.22563C16.7122 1.16347 13.9163 0.00344047 11 0ZM11 15C10.2089 15 9.43553 14.7654 8.77773 14.3259C8.11993 13.8864 7.60724 13.2616 7.30449 12.5307C7.00174 11.7998 6.92253 10.9956 7.07687 10.2196C7.23121 9.44372 7.61217 8.73098 8.17158 8.17157C8.73099 7.61216 9.44373 7.2312 10.2197 7.07686C10.9956 6.92252 11.7998 7.00173 12.5307 7.30448C13.2616 7.60723 13.8864 8.11992 14.3259 8.77772C14.7654 9.43552 15 10.2089 15 11C14.9987 12.0605 14.5768 13.0771 13.827 13.827C13.0771 14.5768 12.0605 14.9987 11 15Z"
                                    fill="currentColor"></path>
                            </svg>
                        </span>
                        {{ __('messages.get_location') }}
                    </button>
                </div>
            </div>
            <p class="dealer-search-note">{{ __('messages.location_permission_note') }}</p>
        </div>
    </div>

    <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="white" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="modal-body p-0">
                    <img src="{{ asset('langding/imgs/' . (app()->getLocale() === 'vi' ? 'BannerContact.png' : 'BannerContact_EN.png')) }}"
                        alt="{{ __('messages.contact_information') }}" class="img-fluid w-100">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="noDealerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-body p-4 p-md-5 text-center">
                    <img src="{{ asset('langding/imgs/logo3.svg') }}" width="100"
                        alt="{{ __('messages.contact_information') }}" class="img-fluid">
                    <div class="mb-3">
                        <h4 class="mb-2 fw-700 text-uppercase title-with-line fw-500 fs-18 text-center text-light-red">CASUMINA</h4>
                    </div>
                    <p class="mb-3 fs-16 lh-lg text-secondary text-left fs-16 mt-3">
                        {{ __('messages.no_dealer_found1') }}
                    </p>
                    <p class="text-left fs-16 mt-3">
                        {{ __('messages.no_dealer_found2') }}
                        {{ __('messages.no_dealer_found3') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
