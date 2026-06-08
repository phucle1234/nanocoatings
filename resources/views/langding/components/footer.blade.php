<footer style="background-image: url('{{ $footerMain?->image ?? asset('langding_nano/imgs/Slection8.png') }}'); background-size: cover; background-position: center;">
    <div class="container-fluid">
        <div class="footer-top position-relative text-center">
            <div class="title-main text-white fs-24 fw-700 text-uppercase">
                {{ $footerMain->category_description ?? __('messages.slogan') }}</div>
            <div class="title-sub text-white fs-18 fw-700 mt-1">
                {{ $footerMain->meta_title ?? __('messages.company_name') }}</div>
            <div class="intro text-white fs-14 fw-300 mt-1">
                {!! $footerMain->meta_description ?? __('messages.company_address') !!}
            </div>
        </div>
        <div class="footer-center position-relative">
            <div class="row">
                <div class="col-lg-6">
                    <div class="footer-center-item mt-5">
                        <h3 class="fs-18 text-white fw-700">{{ __('messages.contact') }}</h3>
                        <ul class="list-unstyled mb-0">
                            @foreach ($lienHe['banners'] as $lienHe)
                                <li class="d-flex align-items-center gap-2">
                                    <img src="{{ $lienHe->image ?? asset('langding/imgs/icon-location.svg') }}"
                                        alt="{{ __('messages.icon') }}">
                                    <a href="#" class="text-white fs-14">{{ $lienHe->title ?? '' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="footer-center-item mt-5">
                        <h3 class="fs-18 text-white fw-700">{{ __('messages.about_casumina') }}</h3>
                        <ul class="list-unstyled mb-0">
                            @foreach ($veCasumina['banners'] ?? [] as $aboutLink)
                                @php
                                    $aboutHref = $aboutLink->canonical_url
                                        ?: ($aboutLink->url ?: ($aboutLink->slug ? url($aboutLink->slug) : '#'));
                                @endphp
                                <li><a href="{{ $aboutHref }}"
                                        class="text-white fs-14">{{ $aboutLink->title ?? '' }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="footer-center-item mt-5">
                        <h3 class="fs-18 text-white fw-700">{{ __('messages.product_categories') }}</h3>
                        <ul class="list-unstyled mb-0">
                            @foreach ($footerCategories as $category)
                                @php
                                    $categoryUrl = isset($category['slug']) ? '/category/' . $category['slug'] : '#';
                                @endphp
                                <li><a href="{{ $categoryUrl }}"
                                        class="text-white fs-14">{{ $category['name'] ?? '' }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="row">
                <div class="col-lg-6">
                    <h3 class="text-white fw-700 fs-18 text-uppercase mb-0">{{ __('messages.register_info') }}</h3>
                    <div class="mt-3">
                        <form method="POST" action="{{ route('contact.subscribe') }}" class="form-control-email">
                            @csrf
                            <div class="input-group input-group-lg">
                                <input type="email" name="email" class="form-control"
                                    placeholder="{{ __('messages.email_placeholder') }}" required>
                                <button type="submit" class="btn fw-600 text-white fs-16" style="background-color: #2CCC81;">
                                    {{ __('messages.subscribe_btn') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="capacity-profile text-end">
                        <a target="_blank" href="{{ asset('/storage/uploads/documents/posts/HOSONANGLUCCSM.pdf') }}"
                            class="btn btn-outline-secondary px-5 py-3" style="background-color: #2CCC81;">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('langding/imgs/user.svg') }}" alt="{{ __('messages.logo') }}"
                                    class="img-fluid">
                                <span
                                    class="fs-16 fw-700 text-uppercase text-white">{{ __('messages.company_profile') }}</span>
                            </div>
                        </a>
                    </div>
                    <div class="social-links d-flex justify-content-end mt-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-white fs-14">{{ __('messages.connect_with_casumina') }}</span>
                            @foreach ($ketNoiVoiCasumina['banners'] as $ketNoiVoiCasumina)
                                <a target="_blank" href="{{ $ketNoiVoiCasumina->canonical_url ?? '' }}"><img
                                        src="{{ $ketNoiVoiCasumina->image ?? asset('langding/imgs/facebook.png') }}"
                                        alt="{{ $ketNoiVoiCasumina->title ?? '' }}" class="img-fluid"></a>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4 copyright text-white fs-14 text-end">{{ __('messages.copyright') }}</div>
                </div>
            </div>
        </div>
    </div>
</footer>
