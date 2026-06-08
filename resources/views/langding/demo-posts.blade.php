@extends('langding.index')
@section('title', 'Demo About Page')
@section('langding_content')
    <div class="page-posts-category style-other">
        <div class="box-media">
            <div class="container-fluid">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#" class="fs-15 text-black">Trang chủ</a></li>
                        <li class="breadcrumb-item"><a href="#" class="fs-15 text-black">Tin tức</a></li>
                        <li class="breadcrumb-item"><a href="#" class="fs-15 text-black">Chi tiết</a></li>
                    </ol>
                </nav>
                <h2 class="font-hanzel fs-32 mt-4 fw-400 text-center main-title">Tin tức và Sự kiện</h2>
                <div class="box-posts-content">
                    <div class="bg-img-cover posts-img position-relative"
                        style="background-image: url('{{ asset('langding/imgs/media-1.png') }}');">
                        <div class="calendar">
                            <div class="day fs-20 fw-700 text-white d-flex align-items-center justify-content-center">17/05
                            </div>
                            <div class="year fs-32 fw-700 d-flex align-items-center justify-content-center bg-white">2025
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4 mt-3 content-meta">
                        <a href="#" class="d-flex align-items-center fs-20 gap-2">
                            <svg width="23" height="25" viewBox="0 0 23 25" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M11.1901 12.9819C14.7616 12.9819 17.681 10.0624 17.681 6.49094C17.681 2.91949 14.7616 0 11.1901 0C7.61865 0 4.69922 2.91949 4.69922 6.49094C4.69922 10.0624 7.61871 12.9819 11.1901 12.9819Z"
                                    fill="#CF171C" />
                                <path
                                    d="M22.3302 18.169C22.1601 17.7438 21.9334 17.347 21.6783 16.9785C20.3744 15.051 18.362 13.7755 16.0944 13.4637C15.811 13.4354 15.4992 13.492 15.2724 13.6621C14.0819 14.5408 12.6647 14.9943 11.1908 14.9943C9.71681 14.9943 8.2996 14.5408 7.10912 13.6621C6.88234 13.492 6.57054 13.407 6.28712 13.4637C4.01955 13.7755 1.97875 15.051 0.703248 16.9785C0.448149 17.347 0.221369 17.7722 0.0513387 18.169C-0.0336765 18.339 -0.0053562 18.5375 0.079659 18.7075C0.306439 19.1043 0.589859 19.5012 0.844958 19.8413C1.24177 20.3799 1.66695 20.8617 2.14883 21.3152C2.54564 21.712 2.99915 22.0805 3.45271 22.449C5.69191 24.1214 8.38467 25 11.1624 25C13.9402 25 16.633 24.1213 18.8722 22.449C19.3257 22.1089 19.7792 21.712 20.1761 21.3152C20.6296 20.8617 21.0831 20.3798 21.4799 19.8413C21.7634 19.4728 22.0185 19.1043 22.2452 18.7075C22.3869 18.5375 22.4152 18.339 22.3302 18.169Z"
                                    fill="#CF171C" />
                            </svg>
                            <span class="fs-20 text-black-50 fw-700">by Admin</span>
                        </a>
                        <div class="d-flex align-items-center gap-2">
                            <svg width="25" height="24" viewBox="0 0 25 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12.5 0C5.60753 0 3.35943e-05 5.04667 3.35943e-05 11.25C3.35943e-05 13.4183 0.685867 15.5187 1.9867 17.335C1.74045 20.0587 1.08045 22.0808 0.122117 23.0387C-0.00454973 23.1654 -0.0362164 23.3592 0.0437836 23.5192C0.114617 23.6621 0.26045 23.75 0.4167 23.75C0.435867 23.75 0.455034 23.7487 0.474617 23.7458C0.643367 23.7221 4.56337 23.1579 7.39878 21.5212C9.00878 22.1708 10.7238 22.5 12.5 22.5C19.3925 22.5 25 17.4533 25 11.25C25 5.04667 19.3925 0 12.5 0ZM6.6667 12.9167C5.74753 12.9167 5.00003 12.1692 5.00003 11.25C5.00003 10.3308 5.74753 9.58333 6.6667 9.58333C7.58587 9.58333 8.33337 10.3308 8.33337 11.25C8.33337 12.1692 7.58587 12.9167 6.6667 12.9167ZM12.5 12.9167C11.5809 12.9167 10.8334 12.1692 10.8334 11.25C10.8334 10.3308 11.5809 9.58333 12.5 9.58333C13.4192 9.58333 14.1667 10.3308 14.1667 11.25C14.1667 12.1692 13.4192 12.9167 12.5 12.9167ZM18.3334 12.9167C17.4142 12.9167 16.6667 12.1692 16.6667 11.25C16.6667 10.3308 17.4142 9.58333 18.3334 9.58333C19.2525 9.58333 20 10.3308 20 11.25C20 12.1692 19.2525 12.9167 18.3334 12.9167Z"
                                    fill="#CF171C" />
                            </svg>
                            <span class="fs-20 text-black-50 fw-700">2 Đánh giá</span>
                        </div>
                    </div>
                    <h1 class="mb-32 fw-700">Vinh danh CASUMINA cùng 30 doanh nghiệp đoạt giải Thương hiệu Vàng TPHCM
                        2020.</h1>
                    <div class="box-posts-editor">
                        Ngày 18/01/2021, CASUMINA tự hào khai trương Advenza Tire Spa – trung tâm chăm sóc lốp xe đầu tiên
                        của thương hiệu Advenza tại thành phố Hải Phòng. Tọa lạc tại 528 đường Lê Thánh Tôn, P. Đông Hải 1,
                        Q. Hải An, trung tâm hứa hẹn mang đến trải nghiệm vượt trội trong việc chăm sóc và bảo dưỡng lốp xe
                        du lịch.
                        <div class="row">
                            <div class="col-md-6">
                                <img src="{{ asset('langding/imgs/media-2.png') }}" alt="Media Image"
                                    class="rounded img-fluid my-4 w-100">
                            </div>
                            <div class="col-md-6">
                                <img src="{{ asset('langding/imgs/media-2.png') }}" alt="Media Image"
                                    class="rounded img-fluid my-4 w-100">
                            </div>
                        </div>
                        <h2>Hành Trình Phát Triển Của Advenza Tire Spa</h2>
                        <p>Ngày 18/01/2021, CASUMINA tự hào khai trương Advenza Tire Spa – trung tâm chăm sóc lốp xe đầu
                            tiên của thương hiệu Advenza tại thành phố Hải Phòng. Tọa lạc tại 528 đường Lê Thánh Tôn, P.
                            Đông Hải 1, Q. Hải An, trung tâm hứa hẹn mang đến trải nghiệm vượt trội trong việc chăm sóc và
                            bảo dưỡng lốp xe du lịch.Ngày 18/01/2021, CASUMINA tự hào khai trương Advenza Tire Spa – trung
                            tâm chăm sóc lốp xe đầu tiên của thương hiệu Advenza tại thành phố Hải Phòng. Tọa lạc tại 528
                            đường Lê Thánh Tôn, P. Đông Hải 1, Q. Hải An, trung tâm hứa hẹn mang đến trải nghiệm vượt trội
                            trong việc chăm sóc và bảo dưỡng lốp xe du lịch.</p>
                        <h2>Các Dòng Sản Phẩm Lốp Xe Advenza</h2>
                        <p>
                            Advenza tự hào mang đến sự đa dạng cho người dùng với 5 dòng sản phẩm nổi bật, đáp ứng nhu cầu
                            khác nhau:</p>
                        <ul>
                            <li>Venturer: Dành cho xe Sedan, Hatchback – vận hành êm ái, tốc độ vượt trội.</li>
                            <li>Coverer: Phù hợp với SUV, MPV – bền bỉ trên mọi địa hình.</li>
                            <li>Discoverer: Thiết kế cho xe bán tải – mạnh mẽ, an toàn, chinh phục mọi hành trình.</li>
                            <li>Traveller: Lựa chọn lý tưởng cho xe khách, mini bus – an toàn, tiết kiệm nhiên liệu.</li>
                            <li>Milega: Dòng lốp thương mại, xe tải nhẹ – chịu tải cao, bền bỉ theo thời gian.</li>
                        </ul>
                        <h2>Tại Sao Nên Chọn Advenza Tire Spa?</h2>
                        <p>Advenza Tire Spa không chỉ cung cấp các sản phẩm lốp chính hãng mà còn cam kết mang lại trải
                            nghiệm dịch vụ tận tâm và chu đáo:</p>
                        <ul>
                            <li>Chất lượng đỉnh cao: Sản phẩm được sản xuất từ công nghệ tiên tiến, đạt chuẩn quốc tế.</li>
                            <li>Dịch vụ tận tình: Đội ngũ kỹ thuật viên chuyên nghiệp, luôn sẵn sàng hỗ trợ khách hàng.</li>
                            <li>Giá cả hợp lý: Sự lựa chọn hoàn hảo cho những ai muốn tiết kiệm chi phí nhưng vẫn đảm bảo
                                chất lượng</li>
                        </ul>
                    </div>
                    <div class="categories d-lg-flex align-items-center justify-content-between">
                        <div class="categories-items d-lg-flex align-items-center">
                            <span class="fs-24 fw-700">CATEGORIES:</span>
                            <div class="d-flex align-items-center">
                                <a href="#" class="cat-item fs-16">Tin Khuyến Mãi</a>
                                <a href="#" class="cat-item fs-16">Tin CASUMINA</a>
                            </div>
                        </div>
                        <div class="share d-flex align-items-center gap-3">
                            <span class="fs-16 fw-500 text-black-50 me-3">SHARE:</span>
                            <svg width="23" height="24" viewBox="0 0 23 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M18.2812 15.5625C16.8918 15.5625 15.6675 16.246 14.8984 17.2854L8.24953 13.8808C8.35992 13.5045 8.4375 13.1146 8.4375 12.7031C8.4375 12.145 8.32308 11.6145 8.12541 11.1264L15.0838 6.93919C15.8581 7.848 16.9964 8.4375 18.2812 8.4375C20.6076 8.4375 22.5 6.54511 22.5 4.21875C22.5 1.89239 20.6076 0 18.2812 0C15.9549 0 14.0625 1.89239 14.0625 4.21875C14.0625 4.75486 14.1729 5.26341 14.356 5.73553L7.37695 9.93506C6.60323 9.05325 5.48142 8.48438 4.21875 8.48438C1.89239 8.48438 0 10.3768 0 12.7031C0 15.0295 1.89239 16.9219 4.21875 16.9219C5.63109 16.9219 6.87614 16.2183 7.64227 15.1497L14.2693 18.5432C14.1472 18.9373 14.0625 19.3477 14.0625 19.7812C14.0625 22.1076 15.9549 24 18.2812 24C20.6076 24 22.5 22.1076 22.5 19.7812C22.5 17.4549 20.6076 15.5625 18.2812 15.5625Z"
                                    fill="black" />
                            </svg>
                        </div>
                    </div>
                    <div class="reviews">
                        <div class="reviews-title fs-28 fw-700">ĐÁNH GIÁ</div>
                        <div class="reviews-list">
                            <div class="reviews-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="reviews-avatar-wrap d-flex align-items-center gap-5">
                                        <div class="reviews-avatar-img bg-img-cover"
                                            style="background-image: url('{{ asset('langding/imgs/media-1.png') }}');">
                                        </div>
                                        <div class="reviews-avatar-info">
                                            <div class="fs-28 fw-700">Nguyễn Văn A</div>
                                            <div class="fs-22 text-black-50 mt-2">March 20, 2023 at 2:37 pm</div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary fs-14 fw-400">Trả lời</button>
                                </div>
                                <div class="reviews-item-content text-center">Neque porro est qui dolorem ipsum quia quaed
                                    inventor
                                    veritatis et quasi architecto beatae vitae dicta sunt explicabo. Aelltes port lacus quis
                                    enim var</div>
                            </div>
                            <div class="reviews-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="reviews-avatar-wrap d-flex align-items-center gap-5">
                                        <div class="reviews-avatar-img bg-img-cover"
                                            style="background-image: url('{{ asset('langding/imgs/media-1.png') }}');">
                                        </div>
                                        <div class="reviews-avatar-info">
                                            <div class="fs-28 fw-700">Nguyễn Văn A</div>
                                            <div class="fs-22 text-black-50 mt-2">March 20, 2023 at 2:37 pm</div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary fs-14 fw-400">Trả lời</button>
                                </div>
                                <div class="reviews-item-content text-center">Neque porro est qui dolorem ipsum quia quaed
                                    inventor
                                    veritatis et quasi architecto beatae vitae dicta sunt explicabo. Aelltes port lacus quis
                                    enim var</div>
                            </div>
                        </div>
                        <div class="reviews-comment-wrap">
                            <div class="reviews-comment-title fs-28 fw-700">VIẾT ĐÁNH GIÁ</div>
                            <div class="reviews-comment-form">
                                <div class="comment-form-item">
                                    <label for="comment-form-name text-black-50" class="form-label">Họ Và Tên</label>
                                    <input type="text" class="form-control" id="comment-form-name">
                                </div>
                                <div class="comment-form-item mt-5">
                                    <label for="comment-form-name text-black-50" class="form-label">Your
                                        Email</label>
                                    <input type="text" class="form-control" id="comment-form-name">
                                </div>
                                <div class="comment-form-item mt-5">
                                    <textarea class="form-control" id="comment-form-message" rows="5" placeholder="Your Comment"></textarea>
                                </div>
                                <button class="btn btn-primary btn-send-comment mt-5 fs-24 fw-600">GỬI ĐÁNH GIÁ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="category-search-list">
            <div class="container-fluid">
                <h2 class="font-hanzel fs-32 mt-4 fw-400 text-center">Các bài viết liên quan</h2>
                <div class="posts-list">
                    <div class="row">
                        @for ($j = 1; $j <= 4; $j++)
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
