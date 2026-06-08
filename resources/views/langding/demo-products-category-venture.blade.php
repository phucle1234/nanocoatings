@extends('langding.index')

@section('title', 'Category Venture')

@section('langding_content')
<div class="page-venture">
    <div class="category-banner bg-img-cover"
        style="background-image: url('{{ asset('langding/imgs/demo-banner-venturer.png') }}');">
        <div class="category-banner-info">
            <div class="container-fluid">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="fs-14 text-white">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="#" class="fs-14 text-white">Sản phẩm</a></li>
                        <li class="breadcrumb-item"><a href="#" class="fs-14 text-white">Lốp Avenza PCR</a></li>
                        <li class="breadcrumb-item fs-14 text-white" aria-current="page">Dòng sản phẩm Venture</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="product-detail-related">
        <div class="container-fluid">
            <div class="title-with-line fw-500 fs-20 text-center text-light-red text-uppercase">Lốp Avenza PCR</div>
            <h2 class="title-main font-hanzel fs-32 fw-400 text-center mt-2">Sản phẩm Venturer mới nhất</h2>
        </div>
        <div class="category-search-list">
            <div class="container-fluid">
                <div class="category-search-list-item">
                    <div class="row">
                        @for ($i = 1; $i <= 12; $i++)
                            <div class="col-md-6 col-xl-4 col-xxl-3">
                            <div class="product-item position-relative">
                                <div class="product-attr">
                                    <span class="product-new">NEW</span>
                                    <span class="product-sale">-50%</span>
                                </div>
                                <div class="product-favourite">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                        fill="black" class="bi bi-heart" viewBox="0 0 16 16">
                                        <path
                                            d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                        fill="currentColor" class="bi bi-heart-fill" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314" />
                                    </svg>
                                </div>
                                <div class="product-item-img bg-img-contain ratio-1-1"
                                    style="background-image: url('{{ asset('langding/imgs/product/product-avata.png') }}');">
                                </div>
                                <div class="product-item-line"></div>
                                <div class="product-item-category d-flex align-items-center gap-2">
                                    <a href="">Category</a>
                                    <div class="product-item-star text-nowrap">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <!-- <i class="bi bi-star"></i> -->
                                    </div>
                                </div>
                                <h3
                                    class="product-item-title text-uppercase text-red fs-16 font-hanzel line-2 mt-2 mb-3">
                                    Lốp 215/45 R17 Venturer AV579 TL 91V RBD (XL, Advenza)
                                </h3>
                                <div class="fs-12 text-uppercase">17-INCH TIRES</div>
                                <div class="product-price d-flex gap-3 align-items-center">
                                    <span class="fs-24 font-hanzel text-red">2.600.000đ</span>
                                    <span class="fs-14 text-red">Đã tính VAT</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <div class="product-item-view">
                                        <a class="fs-16 text-black" href="#">
                                            <span class="me-2">Chi tiết</span>
                                            <svg width="12" height="13" viewBox="0 0 12 13" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M1.96059 1.26893L10.0296 0.562985M10.0296 0.562985L10.7356 8.63204M10.0296 0.562985L0.562794 11.8451"
                                                    stroke="black" stroke-width="1.12583" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                    </div>
                                    <div class="cat-link d-flex align-items-center">
                                        <a href="#" class="w-100">
                                            <img src="{{ asset('langding/imgs/category/cart-icon.svg') }}">
                                        </a>
                                    </div>
                                </div>
                            </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
        <div class="category-search-hot-link">
            <a class="fs-18 text-uppercase font-hanzel" href="#">Săm lốp Xe tải</a>
            <a class="fs-18 text-uppercase font-hanzel" href="#">Săm lốp Xe đạp</a>
            <a class="fs-18 text-uppercase font-hanzel" href="#">Săm lốp xe máy</a>
            <a class="fs-18 text-uppercase font-hanzel" href="#">Lốp Advenza PCR</a>
            <a class="fs-18 text-uppercase font-hanzel" href="#">Săm lốp Chuyên dụng</a>
            <a class="fs-18 text-uppercase font-hanzel" href="#">Săm lốp Xe điện</a>
        </div>
    </div>
</div>
<div class="category-banner-design position-relative bg-img-cover"
    style="background-image: url('{{ asset('langding/imgs/demo-bg-venturer.png') }}');">
    <div class="category-banner-design-slider">
        @for ($i = 1; $i <= 3; $i++)
            <div class="category-banner-design-slider-item text-center">
            <div class="category-banner-design-slider-item-info">
                <div class="title-with-line fs-18 text-light-red">THIẾT KẾ</div>
                <div class="font-hanzel fs-32 mt-2">Phanh tốt chống trượt dài</div>
                <div class="fs-14 mt-3">Hoa lốp được thiết kế theo dạng hướng dọc cùng với ba rãnh
                    chính<br>giúp cân bằng khi lái, chống trượt dài khi phanh</div>
                <div class="category-banner-design-img">
                    <img src="{{ asset('langding/imgs/demo-banner-design-venturer.png') }}" alt="Design"
                        class="mt-3 img-fluid mx-auto">
                </div>
            </div>
    </div>
    @endfor
</div>
</div>
<div class="category-banner-design position-relative has-overlay-top bg-img-cover"
    style="background-image: url('{{ asset('langding/imgs/demo-bg-venturer-1.png') }}');">
    <div class="category-banner-design-slider">
        @for ($i = 1; $i <= 3; $i++)
            <div class="category-banner-design-slider-item text-center">
            <div class="category-banner-design-slider-item-info">
                <div class="title-with-line fs-18 text-light-red">CÔNG NGHỆ</div>
                <div class="font-hanzel fs-32 mt-2 text-white">Thách thức khí hậu và thời tiết</div>
                <div class="fs-14 text-white mt-3">Công thức cao su mặt lốp được nghiên cứu tính năng kháng
                    cắt
                    xé tốt ở dải nhiệt độ từ thấp đến cao</div>
                <div class="category-banner-design-img">
                    <img src="{{ asset('langding/imgs/demo-banner-design-venturer.png') }}" alt="Design"
                        class="mt-3 img-fluid mx-auto">
                </div>
            </div>
    </div>
    @endfor
</div>
</div>
<div class="category-banner-design position-relative has-overlay-bot bg-img-cover"
    style="background-image: url('{{ asset('langding/imgs/demo-bg-venturer-2.png') }}');">
    <div class="category-banner-design-slider">
        @for ($i = 1; $i <= 3; $i++)
            <div class="category-banner-design-slider-item text-center">
            <div class="bg-img-cover ratio-16-9">
                <div class="category-banner-design-slider-item-info">
                    <div class="title-with-line fs-18 text-light-red">TRẢI NGHIỆM</div>
                    <div class="font-hanzel fs-32 mt-2 text-white">Tiêu tiếng ồn, tản nhiệt tốt</div>
                    <div class="fs-14 text-white mt-3">Rãnh gai có nhiều rãnh nhỏ xen kẻ nhau giúp triệt tiêu
                        tiếng ồn, dễ điều khiển và<br>tản nhiệt tối dưới mọi điều kiện</div>
                </div>
            </div>
    </div>
    @endfor
</div>
</div>
<div class="category-why bg-img-cover"
    style="background-image: url('{{ asset('langding/imgs/category/bg-why.png') }}');">
    <div class="container-fluid">
        <div class="category-why-slider">
            <div class="category-why-slider-item">
                <div class="category-why-slider-item-info">
                    <div class="title-with-line fs-20 text-light-red text-center">TÍNH NĂNG CHÍNH</div>
                    <div class="font-hanzel fs-32 text-center lh-1 mt-2">Tại sao Avenza PCR lại là lựa chọn
                        tối ưu</div>
                    <div class="fs-14 text-center mt-3 mb-3 category-why-slider-item-info-intro">Bất kể bạn chọn di
                        chuyển bằng phương tiện gì và
                        dù bạn đi
                        đâu, với những cải tiến công nghệ dành riêng cho từng dòng sản phẩm.<br>Advenza luôn đồng
                        hành cùng bạn</div>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="category-why-slider-item-object">
                                <img src="{{ asset('langding/imgs/category/icon-ket-cau.svg') }}"
                                    alt="Why Avenza PCR">
                                <div class="category-why-slider-item-object-title fs-24 font-hanzel mt-2">KẾT CẤU
                                </div>
                                <div class="category-why-slider-item-object-intro fs-14 mt-2">Lốp Venturer là loại
                                    lốp
                                    không
                                    săm tubeless với
                                    kết cấu thép đặc biệt chịu tải cao, chịu va đập
                                    trên nhiều loại đường</div>
                            </div>
                            <div class="category-why-slider-item-object mt-5">
                                <img src="{{ asset('langding/imgs/category/icon-cong-nghe.svg') }}"
                                    alt="Why Avenza PCR">
                                <div class="category-why-slider-item-object-title fs-24 font-hanzel mt-2">CÔNG NGHỆ
                                </div>
                                <div class="category-why-slider-item-object-intro fs-14 mt-3">Công thức cao su tân
                                    tiến giúp việc chịu được đa
                                    dạng dải nhiệt ở mọi điều kiện, tiêu tiếng ồn và
                                    dễ kiểm soát khi lái</div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <img src="{{ asset('langding/imgs/category/why-lop.png') }}" alt="Why Avenza PCR"
                                class="img-fluid">
                        </div>
                        <div class="col-lg-3">
                            <div class="category-why-slider-item-object text-end">
                                <img class="ms-auto" src="{{ asset('langding/imgs/category/icon-an-toan.svg') }}"
                                    alt="Why Avenza PCR">
                                <div class="category-why-slider-item-object-title fs-24 font-hanzel mt-2">AN TOÀN
                                </div>
                                <div class="category-why-slider-item-object-intro fs-14 mt-2">Hông lốp mềm dẻo với
                                    khung sườn chịu lực cao
                                    tạo nên tính tăng uốn gấp linh hoạt, đảm bảo độ
                                    cứng vững và độ an toàn cao</div>
                            </div>
                            <div class="category-why-slider-item-object mt-5 text-end">
                                <img class="ms-auto" src="{{ asset('langding/imgs/category/icon-dam-bao.svg') }}"
                                    alt="Why Avenza PCR">
                                <div class="category-why-slider-item-object-title fs-24 font-hanzel mt-2">ĐẢM BẢO
                                </div>
                                <div class="category-why-slider-item-object-intro fs-14 mt-3">Mọi sản phẩm của
                                    Advenza đều đạt các tiêu
                                    chuẩn khắt khe của các thị trường có ngành
                                    công nghiệp ô tô phát triển trên thế giới</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="category-why-slider-item">
                <div class="category-why-slider-item-info">
                    <div class="title-with-line fs-20 text-light-red text-center">TÍNH NĂNG CHÍNH</div>
                    <div class="font-hanzel fs-32 text-center lh-1 mt-2">Tại sao Avenza PCR lại là lựa chọn
                        tối ưu</div>
                    <div class="fs-14 text-center mt-3 mb-3 category-why-slider-item-info-intro">Bất kể bạn chọn di
                        chuyển bằng phương tiện gì và
                        dù bạn đi
                        đâu, với những cải tiến công nghệ dành riêng cho từng dòng sản phẩm.<br>Advenza luôn đồng
                        hành cùng bạn</div>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="category-why-slider-item-object">
                                <img src="{{ asset('langding/imgs/category/icon-ket-cau.svg') }}"
                                    alt="Why Avenza PCR">
                                <div class="category-why-slider-item-object-title fs-24 font-hanzel mt-2">KẾT CẤU
                                </div>
                                <div class="category-why-slider-item-object-intro fs-14 mt-2">Lốp Venturer là loại
                                    lốp
                                    không
                                    săm tubeless với
                                    kết cấu thép đặc biệt chịu tải cao, chịu va đập
                                    trên nhiều loại đường</div>
                            </div>
                            <div class="category-why-slider-item-object mt-5">
                                <img src="{{ asset('langding/imgs/category/icon-cong-nghe.svg') }}"
                                    alt="Why Avenza PCR">
                                <div class="category-why-slider-item-object-title fs-24 font-hanzel mt-2">CÔNG NGHỆ
                                </div>
                                <div class="category-why-slider-item-object-intro fs-14 mt-3">Công thức cao su tân
                                    tiến giúp việc chịu được đa
                                    dạng dải nhiệt ở mọi điều kiện, tiêu tiếng ồn và
                                    dễ kiểm soát khi lái</div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <img src="{{ asset('langding/imgs/category/why-lop.png') }}" alt="Why Avenza PCR"
                                class="img-fluid">
                        </div>
                        <div class="col-lg-3">
                            <div class="category-why-slider-item-object text-end">
                                <img class="ms-auto" src="{{ asset('langding/imgs/category/icon-an-toan.svg') }}"
                                    alt="Why Avenza PCR">
                                <div class="category-why-slider-item-object-title fs-24 font-hanzel mt-2">AN TOÀN
                                </div>
                                <div class="category-why-slider-item-object-intro fs-14 mt-2">Hông lốp mềm dẻo với
                                    khung sườn chịu lực cao
                                    tạo nên tính tăng uốn gấp linh hoạt, đảm bảo độ
                                    cứng vững và độ an toàn cao</div>
                            </div>
                            <div class="category-why-slider-item-object mt-5 text-end">
                                <img class="ms-auto" src="{{ asset('langding/imgs/category/icon-dam-bao.svg') }}"
                                    alt="Why Avenza PCR">
                                <div class="category-why-slider-item-object-title fs-24 font-hanzel mt-2">ĐẢM BẢO
                                </div>
                                <div class="category-why-slider-item-object-intro fs-14 mt-3">Mọi sản phẩm của
                                    Advenza đều đạt các tiêu
                                    chuẩn khắt khe của các thị trường có ngành
                                    công nghiệp ô tô phát triển trên thế giới</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="category-avenza-video bg-img-cover"
    style="background-image: url('{{ asset('langding/imgs/bg-avenza-video.png') }}');">
    <div class="container-fluid">
        <div class="title-with-line fs-18 text-center text-light-red lh-1">VIDEO</div>
        <h2 class="font-hanzel fs-32 fw-400 text-center mb-0 mt-3 text-white lh-1">Thoải mái lướt nhanh</h2>
        <div class="video-slider mx-auto">
            <div class="video-slider-item">
                <a href="https://www.youtube.com/watch?v=PUdyuKaGQd4" data-fancybox>
                    <div class="bg-img-cover"
                        style="background-image: url('{{ asset('langding/imgs/video.jpg') }}');">
                        <div class="video-play-btn">
                            <img src="{{ asset('langding/imgs/video.svg') }}" alt="Play">
                        </div>
                    </div>
                </a>
            </div>
            <div class="video-slider-item">
                <a href="https://www.youtube.com/watch?v=PUdyuKaGQd4" data-fancybox>
                    <div class="bg-img-cover"
                        style="background-image: url('{{ asset('langding/imgs/video.jpg') }}');">
                        <div class="video-play-btn">
                            <img src="{{ asset('langding/imgs/video.svg') }}" alt="Play">
                        </div>
                    </div>
                </a>
            </div>
            <div class="video-slider-item">
                <a href="https://www.youtube.com/watch?v=PUdyuKaGQd4" data-fancybox>
                    <div class="bg-img-cover"
                        style="background-image: url('{{ asset('langding/imgs/video.jpg') }}');">
                        <div class="video-play-btn">
                            <img src="{{ asset('langding/imgs/video.svg') }}" alt="Play">
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<div class="category-avenza-news box-news-info">
    <div class="container-fluid">
        <div class="title-group">
            <div class="title-with-line fs-18 text-center text-light-red">
                KHUYẾN MÃI</div>
            <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 text-white">
                Thông tin ưu đãi</h2>
            <div class="fs-16 text-center text-white mt-2 intro">
                Lorem ipsum dolor sit amet, nihil audiam
                nam<br>no, ei
                eos
                exerci nostro.</div>
        </div>
        <div class="news-slider">
            <div class="news-slider-item">
                <div class="news-slider-child">
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new-2.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="news-slider-item">
                <div class="news-slider-child">
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new-2.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="news-slider-item">
                <div class="news-slider-child">
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                    <div class="news-slider-item-child">
                        <div class="bg-img-cover ratio-16-9"
                            style="background-image: url('{{ asset('langding/imgs/new.png') }}');">
                        </div>
                        <div class="news-slider-item-info">
                            <div class="news-slider-item-info-title fw-300 text-white">15% Off</div>
                            <div class="news-slider-item-info-sub fw-500 fs-36 text-white mt-2">ON AIRPORT
                                PICKUPS
                                <br>ALL
                                OVER USA
                            </div>
                            <div class="news-slider-item-info-view mt-5"><a
                                    class="d-flex align-items-center gap-1 text-white fs-24" href="#">Chi
                                    tiết
                                    <img src="{{ asset('langding/imgs/detail.svg') }}" alt=""></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="box-location">
    <div class="container-fluid">
        <div class="title-with-line fw-500 fs-18 text-center text-light-red">ĐẠI ĐIỆN ỦY QUYỀN</div>
        <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 mt-0 title">Hệ thống phân phối</h2>
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
</div>
@endsection
