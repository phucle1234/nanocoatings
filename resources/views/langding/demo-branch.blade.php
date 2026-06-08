@extends('langding.index')
@section('title', 'Demo Branch Page')
@section('langding_content')
<div class="page-branch">
    <div class="box-location">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="fs-15 text-black">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('demo-branch') }}" class="fs-15 text-black">Tin tức</a></li>
                    <li class="breadcrumb-item active fs-15 text-black" aria-current="page">Phân phối</li>
                </ol>
            </nav>
            <h2 class="font-hanzel fs-32 fw-400 text-center mt-4 mt-0 title">Hệ thống phân phối</h2>
            <div class="box-location-intro text-center fs-14 mt-4 mx-auto">
                Công ty CASUMINA hiện có 05 nhà máy ở Việt nam, 01 trung tâm nghiên cứu kỹ thuật –<br>R&D . Các sản
                phẩm được bán tại 64 tỉnh thành Việt Nam và trên 60 nước trên Thế giới.
            </div>
            <div class="box-location-search mx-auto">
                <div class="global-header d-flex align-items-center">
                    <div class="search-box d-flex align-items-center w-50">
                        <img src="{{ asset('langding/imgs/world.png') }}" alt="Location" class="img-fluid">
                        <input type="text" class="form-control" placeholder="{{ __('messages.worldwide') }}">
                    </div>
                    <select class="form-select select-country" aria-label="Small select example">
                        <option selected>{{ __('messages.country') }}</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                    <select class="form-select select-product" aria-label="Small select example">
                        <option selected>{{ __('messages.products') }}</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                    <button class="btn btn-go fw-700 ms-auto" type="button">{{ __('messages.go') }}</button>
                </div>
            </div>
            <div class="bg-branch">
                <img src="{{ asset('langding/imgs/bg-branch-page.png') }}" alt="Background Branch"
                    class="img-fluid w-100">
            </div>
        </div>
    </div>
    <div class="branch-content-item">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="branch-content-location">
                        <div
                            class="branch-content-location-title d-flex align-items-center justify-content-between border-bottom border-dark-subtle">
                            <div class="fs-28 fw-700 text-uppercase title">Cửa hàng & đại lý gần bạn</div>
                            <div class="fs-16 d-flex align-items-center gap-2">
                                <svg width="14" height="18" viewBox="0 0 14 18" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.75 0C4.96052 0.00237379 3.24501 0.714295 1.97965 1.97965C0.714295 3.24501 0.00237379 4.96052 0 6.75C0 11.5959 6.28875 17.6653 6.55594 17.9212C6.60791 17.9718 6.67753 18 6.75 18C6.82247 18 6.89209 17.9718 6.94406 17.9212C7.21125 17.6653 13.5 11.5959 13.5 6.75C13.4976 4.96052 12.7857 3.24501 11.5203 1.97965C10.255 0.714295 8.53948 0.00237379 6.75 0V0ZM6.75 9.84375C6.13811 9.84375 5.53997 9.6623 5.0312 9.32236C4.52244 8.98241 4.12591 8.49924 3.89175 7.93393C3.65759 7.36862 3.59632 6.74657 3.7157 6.14644C3.83507 5.54631 4.12972 4.99506 4.56239 4.56239C4.99506 4.12972 5.54631 3.83507 6.14644 3.7157C6.74657 3.59632 7.36862 3.65759 7.93393 3.89175C8.49924 4.12591 8.98241 4.52244 9.32236 5.0312C9.6623 5.53997 9.84375 6.13811 9.84375 6.75C9.84326 7.57036 9.51715 8.35698 8.93707 8.93707C8.35699 9.51715 7.57036 9.84326 6.75 9.84375Z"
                                        fill="#EC2526" />
                                </svg>
                                <span class="text-red" role="button">Sử dụng vị trí hiện tại của bạn</span>
                            </div>
                        </div>
                        <ul class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
                            @for ($i = 1; $i <= 10; $i++)
                                <div class="nav-link-item">
                                <div class="d-flex justify-content-between">
                                    <div class="nav-link-item-info">
                                        <h3 class="fs-20 font-hanzel">CÔNG TY TNHH MTV KHÔI ANH PHÁT</h3>
                                        <ul class="list-unstyled mb-0">
                                            <li class="d-flex align-items-center gap-2">
                                                <img src="{{ asset('langding/imgs/icon-location.svg') }}"
                                                    alt="Icon" width="13">
                                                <a href="#" class="text-muted fs-16 opacity-75">180
                                                    Nguyễn
                                                    Thị Minh Khai, Phường
                                                    Võ Thị
                                                    Sáu, Quận 3, TP.HCM</a>
                                            </li>
                                            <li class="d-flex align-items-center gap-2 mt-2">
                                                <img src="{{ asset('langding/imgs/telephone-call.svg') }}"
                                                    alt="Icon" width="16">
                                                <a href="#" class="text-muted fs-16 opacity-75">(084)2838
                                                    362 369</a>
                                            </li>
                                            <li class="d-flex align-items-center gap-2 mt-2">
                                                <img src="{{ asset('langding/imgs/icon-mail.svg') }}" alt="Icon"
                                                    width="16">
                                                <a href="#"
                                                    class="text-muted fs-16 opacity-75">casumina@casumina.com.vn</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="btn-choose d-flex gap-4">
                                        <button class="nav-link {{ $i === 1 ? 'active' : '' }}"
                                            id="branch-tab-{{ $i }}" data-bs-toggle="pill"
                                            data-bs-target="#branch-content-{{ $i }}" type="button"
                                            role="tab" aria-controls="branch-content-{{ $i }}"
                                            aria-selected="true">
                                            <svg width="26" height="27" viewBox="0 0 26 27" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M15.2666 13.0349L4.57098 11.105L0.169381 2.30095C-0.137565 1.68612 -0.0156524 0.943198 0.472802 0.460047C0.961292 -0.023069 1.70512 -0.138771 2.31636 0.175327L24.6339 11.6202C25.1642 11.8924 25.4988 12.4387 25.4988 13.0349C25.4987 13.631 25.1642 14.1773 24.6339 14.4496L2.31636 25.8944C1.70512 26.2085 0.961289 26.0928 0.4728 25.6097C-0.0156548 25.1266 -0.137566 24.3836 0.16938 23.7688L4.5923 14.922L15.2666 13.0349Z"
                                                    fill="#6D6D6D" />
                                            </svg>
                                        </button>
                                        <a class="hot-phone" href="tel:(084)2838362369">
                                            <svg width="32" height="32" viewBox="0 0 32 32"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M29.393 23.36C28.519 22.627 23.393 19.381 22.541 19.53C22.141 19.601 21.835 19.942 21.016 20.919C20.6372 21.3999 20.2213 21.8503 19.772 22.266C18.9488 22.0671 18.1519 21.7718 17.398 21.386C14.4413 19.9465 12.0526 17.5571 10.614 14.6C10.2282 13.8461 9.93286 13.0492 9.734 12.226C10.1497 11.7767 10.6001 11.3608 11.081 10.982C12.057 10.163 12.399 9.859 12.47 9.457C12.619 8.603 9.37 3.479 8.64 2.605C8.334 2.243 8.056 2 7.7 2C6.668 2 2 7.772 2 8.52C2 8.581 2.1 14.59 9.689 22.311C17.41 29.9 23.419 30 23.48 30C24.228 30 30 25.332 30 24.3C30 23.944 29.757 23.666 29.393 23.36Z"
                                                    fill="white" />
                                                <path
                                                    d="M23 15H25C24.9976 12.879 24.154 10.8456 22.6542 9.34578C21.1544 7.846 19.121 7.00238 17 7V9C18.5908 9.00159 20.116 9.63424 21.2409 10.7591C22.3658 11.884 22.9984 13.4092 23 15Z"
                                                    fill="white" />
                                                <path
                                                    d="M28 15H30C29.996 11.5534 28.6251 8.24911 26.188 5.812C23.7509 3.37488 20.4466 2.00397 17 2V4C19.9163 4.00344 22.7122 5.16347 24.7744 7.22563C26.8365 9.28778 27.9966 12.0837 28 15Z"
                                                    fill="white" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                    </div>
                    @endfor
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="branch-content-sidebar border border-dark-subtle rounded-2 ms-xl-4 mt-4 mt-lg-0">
                    <div class="tab-content">
                        @for ($i = 1; $i <= 10; $i++)
                            <div class="tab-pane fade {{ $i === 1 ? 'show active' : '' }}"
                            id="branch-content-{{ $i }}" role="tabpanel"
                            aria-labelledby="branch-tab-{{ $i }}" tabindex="0">
                            <h3 class="fs-20 fw-bold">GẦN BẠN NHẤT</h3>
                            <div class="display-map mt-3">
                                <div class="map-container">
                                    <iframe
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.785959448705!2d106.7190448758388!3d10.827685558244587!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175291a71519df7%3A0xcbeec0265c71c79f!2zVHJ1bmcgVMOibSBUaMawxqFuZyBN4bqhaSBHaWdhbWFsbA!5e0!3m2!1svi!2s!4v1761809164129!5m2!1svi!2s"
                                        style="border:0;" allowfullscreen="" class="w-100 h-100"></iframe>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h3 class="fs-20 fw-bold">CÔNG TY TNHH MTV KHÔI ANH PHÁT</h3>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex align-items-start gap-2">
                                        <img src="{{ asset('langding/imgs/icon-location.svg') }}"
                                            alt="Icon" width="13">
                                        <a href="#" class="text-muted fs-16 opacity-75 lh-1">180
                                            Nguyễn
                                            Thị Minh Khai, Phường
                                            Võ Thị
                                            Sáu, Quận 3, TP.HCM</a>
                                    </li>
                                    <li class="d-flex align-items-center gap-2 mt-2">
                                        <img src="{{ asset('langding/imgs/telephone-call.svg') }}"
                                            alt="Icon" width="16">
                                        <a href="#" class="text-muted fs-16 opacity-75">(084)2838 362
                                            369</a>
                                    </li>
                                    <li class="d-flex align-items-center gap-2 mt-2">
                                        <img src="{{ asset('langding/imgs/icon-mail.svg') }}" alt="Icon"
                                            width="16">
                                        <a href="#"
                                            class="text-muted fs-16 opacity-75">casumina@casumina.com.vn</a>
                                    </li>
                                </ul>
                            </div>
                    </div>
                    @endfor
                    <div class="btn-action d-flex gap-3 justify-content-between mt-4">
                        <a href="#"
                            class="btn btn-outline-dark text-uppercase p-0 fw-500 text-center d-flex align-items-center justify-content-center">Chỉ
                            đường</a>
                        <a href="#"
                            class="btn btn-primary text-uppercase p-0 fw-500 text-center d-flex align-items-center justify-content-center">Liên
                            hệ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="category-search-list">
    <div class="category-search-hot-link">
        <a class="fs-16 text-uppercase font-hanzel" href="#">Săm lốp Xe tải</a>
        <a class="fs-16 text-uppercase font-hanzel" href="#">Săm lốp Xe đạp</a>
        <a class="fs-16 text-uppercase font-hanzel" href="#">Săm lốp xe máy</a>
        <a class="fs-16 text-uppercase font-hanzel" href="#">Lốp Advenza PCR</a>
        <a class="fs-16 text-uppercase font-hanzel" href="#">Săm lốp Chuyên dụng</a>
        <a class="fs-16 text-uppercase font-hanzel" href="#">Săm lốp Xe điện</a>
    </div>
</div>
<div class="box-news-info style-other">
    <div class="container-fluid">
        <div class="title-group">
            <div class="title-with-line fw-500 fs-20 text-center text-light-red">KHUYẾN MÃI</div>
            <h2 class="font-hanzel fs-48 fw-400 text-center mt-2 text-white">Thông tin ưu đãi</h2>
            <div class="fw-300 fs-18 text-center text-white mt-3">Lorem ipsum dolor sit amet, nihil audiam
                nam<br>no, ei
                eos
                exerci nostro.</div>
        </div>
        <div class="news-slider">
            <div class="news-slider-item">
                <div class="news-slider-child">
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="news-slider-item">
                <div class="news-slider-child">
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="news-slider-item">
                <div class="news-slider-child">
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ url('langding/imgs/new.png') }}');"></div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
