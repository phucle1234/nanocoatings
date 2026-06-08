@extends('langding.index')
@section('title', 'Demo About Page')
@section('langding_content')
    <div class="page-posts-category">
        <div class="box-media">
            <div class="container-fluid">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="fs-15 text-black">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="#" class="fs-15 text-black">Tin tức</a></li>
                    </ol>
                </nav>
                <h2 class="font-hanzel fs-32 mt-4 fw-400 text-center main-title">Tin tức và Sự kiện</h2>
                <ul class="nav nav-tabs" id="box-media-title" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fs-14 font-hanzel" id="media-1" data-bs-toggle="tab"
                            data-bs-target="#media-1-pane" type="button" role="tab" aria-controls="media-1-pane"
                            aria-selected="true">
                            TIN NỔI BẬT
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fs-14 font-hanzel" id="media-2" data-bs-toggle="tab"
                            data-bs-target="#media-2-pane" type="button" role="tab" aria-controls="media-2-pane"
                            aria-selected="false">
                            TIN KHUYẾN MÃI
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fs-14 font-hanzel" id="media-3" data-bs-toggle="tab"
                            data-bs-target="#media-3-pane" type="button" role="tab" aria-controls="media-3-pane"
                            aria-selected="false">
                            TIN CASUMINA
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fs-14 font-hanzel" id="media-4" data-bs-toggle="tab"
                            data-bs-target="#media-4-pane" type="button" role="tab" aria-controls="media-4-pane"
                            aria-selected="false">
                            TIN CỔ ĐÔNG
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fs-14 font-hanzel" id="media-5" data-bs-toggle="tab"
                            data-bs-target="#media-5-pane" type="button" role="tab" aria-controls="media-5-pane"
                            aria-selected="false">
                            TIN TỨC
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fs-14 font-hanzel" id="media-6" data-bs-toggle="tab"
                            data-bs-target="#media-6-pane" type="button" role="tab" aria-controls="media--pane"
                            aria-selected="false">
                            TIN KHÁC
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="box-media-content">
                    @for ($i = 1; $i <= 6; $i++)
                        <div class="tab-pane fade {{ $i == 1 ? 'show active' : '' }}" id="media-{{ $i }}-pane"
                            role="tabpanel" aria-labelledby="media-{{ $i }}" tabindex="0">
                            <div class="box-media-slider">
                                <div class="box-media-slider-item">
                                    <div class="row flex-nowrap"> <!-- ✅ Thêm flex-nowrap -->
                                        <div class="col-xxl-7 col-xl-7 col-lg-7"> <!-- ✅ Thêm fallback -->
                                            <div class="media-item-big">
                                                <div class="bg-img-cover ratio-17-9"
                                                    style="background-image: url('{{ asset('langding/imgs/media-1.png') }}');">
                                                </div>
                                                <div class="media-item-big-content px-4">
                                                    <a href="#">
                                                        <div class="calendar d-flex align-items-center gap-2 text-white">
                                                            <img src="{{ asset('langding/imgs/calendar.svg') }}"
                                                                alt="Calendar" class="img-fluid">
                                                            <span class="fs-20">17/05/2024</span>
                                                        </div>
                                                        <div class="media-item-big-title d-flex align-items-center mt-3">
                                                            <h3 class="fs-24 text-white mb-0">
                                                                Casumina: Thắp sáng ngọn lửa đổi mới sáng tạo từ lối sống
                                                                bền vững
                                                            </h3>
                                                            <div class="view-more d-flex align-items-center ms-auto">
                                                                <span></span>
                                                                <img src="{{ asset('langding/imgs/view-more.png') }}"
                                                                    alt="Icon" class="img-fluid">
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-5 col-xl-5 col-lg-5"> <!-- ✅ Thêm fallback -->
                                            @for ($y = 1; $y <= 2; $y++)
                                                <div class="media-item-small media-item-small-{{ $y }} d-flex">
                                                    <div class="bg-img-cover"
                                                        style="background-image: url('{{ asset('langding/imgs/media-2.png') }}');">
                                                    </div>
                                                    <div class="media-item-small-content p-3">
                                                        <a href="#">
                                                            <div class="calendar d-flex align-items-center gap-1 text-red">
                                                                <img src="{{ asset('langding/imgs/calendar-white.svg') }}"
                                                                    alt="Calendar" class="img-fluid" width="21">
                                                                <span class="fs-16">17/05/2024</span>
                                                            </div>
                                                            <div class="media-item-small-title line-5 mt-3">
                                                                <h3 class="fs-18 text-black mb-0">
                                                                    Casumina 50 năm vươn mình trở thành thương hiệu săm lốp
                                                                    hàng đầu Việt Nam
                                                                </h3>
                                                                <div class="view-more d-flex align-items-center">
                                                                    <span></span>
                                                                    <img src="{{ asset('langding/imgs/view-more.png') }}"
                                                                        alt="Icon" class="img-fluid" width="18">
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
        <div class="category-search-list">
            <div class="container-fluid">
                <div class="category-search-filter d-lg-flex align-items-center justify-content-between mt-3">
                    <div class="category-search-filter-one d-flex align-items-center gap-3 gap-lg-4 mb-3 mb-lg-0">
                        <div class="category-search-filter-display d-flex">
                            <div class="category-search-filter-display-wrap d-flex align-items-center gap-1">
                                <div class="category-search-filter-display-icon active"><i class="bi bi-grid"></i></div>
                                <div class="category-search-filter-display-icon"><i class="bi bi-list-ul"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="category-search-filter-two d-flex align-items-center gap-3">
                        <div class="category-search-filter-sort">
                            <div class="category-search-filter-sort-order d-xxl-flex align-items-center gap-2">
                                <div class="fs-16 text-nowrap mb-1 mb-xxl-0 text-uppercase">Sắp xếp theo</div>
                                <select class="select2-filter w-100" id="select2-filter-sort"
                                    data-placeholder="Sắp xếp theo" style="width: 100%;">
                                    <option value="aa">Mặc định</option>
                                    <option value="AL">Giá thấp - Cao</option>
                                </select>
                            </div>
                        </div>
                        <div class="category-search-filter-pagination">
                            <div class="category-search-filter-pagination-item d-xxl-flex align-items-center gap-2">
                                <div class="fs-16 text-nowrap mb-1 mb-xxl-0 text-uppercase"><span>15/ 89</span> Sản phẩm
                                    hiển thị</div>
                                <select class="select2-filter w-100" id="select2-filter-pagination"
                                    data-placeholder="Sản phẩm hiển thị" style="width: 100%;">
                                    <option value="">Đặc điểm sản phẩm</option>
                                    <option value="AL">15 sản phẩm</option>
                                    <option value="WY">30 sản phẩm</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="posts-list">
                    <div class="row">
                        @for ($j = 1; $j <= 12; $j++)
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="posts-item">
                                    <div class="bg-img-cover"
                                        style="background-image: url('{{ asset('langding/imgs/media-1.png') }}');">
                                    </div>
                                    <div class="posts-item-content">
                                        <div class="d-flex align-items-center gap-2 fw-500">
                                            <a href="#" class="link-to-category fs-12 text-red">Tin CASUMINA</a>
                                            <div class="calendar d-flex align-items-center gap-1 text-red">
                                                <img src="{{ asset('langding/imgs/calendar-white.svg') }}" alt="Calendar"
                                                    class="img-fluid" width="18">
                                                <span class="fs-12">17/05/2024</span>
                                            </div>
                                        </div>
                                        <h3 class="posts-title line-3 fs-20 fw-700">
                                            <a class="text-black" href="#">Vinh danh CASUMINA cùng 30 doanh nghiệp
                                                đoạt
                                                giải Thương hiệu Vàng TPHCM
                                                2020.</a>
                                        </h3>
                                        <div class="posts-footer d-flex align-items-center justify-content-between">
                                            <div class="posts-author d-flex align-items-center gap-2">
                                                <div class="bg-img-cover posts-author-img"
                                                    style="background-image: url('{{ asset('langding/imgs/media-1.png') }}');">
                                                </div>
                                                <div class="posts-author-info">
                                                    <div class="fs-16 fw-600">Admin</div>
                                                    <div class="fs-14 fw-300">Casumina</div>
                                                </div>
                                            </div>
                                            <a href="#">
                                                <svg width="80" height="32" viewBox="0 0 80 32" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <rect y="15" width="60.1622" height="2" fill="#F52618" />
                                                    <circle cx="59.1348" cy="16" r="16" fill="#F52618" />
                                                    <path
                                                        d="M63.6783 16.6283L55.7637 24.5403C55.4164 24.8867 54.8537 24.8867 54.5056 24.5403C54.1583 24.1939 54.1583 23.6312 54.5056 23.2848L61.7924 16.0006L54.5064 8.71637C54.1591 8.36996 54.1591 7.80729 54.5064 7.46C54.8537 7.11359 55.4173 7.11359 55.7646 7.46L63.6792 15.3719C64.0211 15.7148 64.0211 16.2863 63.6783 16.6283Z"
                                                        fill="white" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item">
                            <a class="page-link"><i class="bi bi-chevron-double-left"></i></a>
                        </li>
                        <li class="page-item">
                            <a class="page-link"><i class="bi bi-chevron-left"></i></a>
                        </li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item active"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                        <li class="page-item"><a class="page-link" href="#">25</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#"><i class="bi bi-chevron-double-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
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
