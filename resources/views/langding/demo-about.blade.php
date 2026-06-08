@extends('langding.index')
@section('title', 'Demo About Page')
@section('langding_content')
<div class="page-about">
    <div class="box-media">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="fs-15 text-black">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('demo-about') }}" class="fs-15 text-black">Giới thiệu CaSUMINA</a></li>
                    <li class="breadcrumb-item active fs-15 text-black" aria-current="page">Giới thiệu công ty</li>
                </ol>
            </nav>
            <h2 class="font-hanzel fs-32 mt-4 fw-400 text-center main-title">Giới thiệu Casumina</h2>
            <ul class="nav nav-tabs" id="box-media-title" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fs-14 font-hanzel text-uppercase" id="media-1" data-bs-toggle="tab"
                        data-bs-target="#media-1-pane" type="button" role="tab" aria-controls="media-1-pane"
                        aria-selected="true">
                        Giới thiệu công ty
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel text-uppercase" id="media-2" data-bs-toggle="tab"
                        data-bs-target="#media-2-pane" type="button" role="tab" aria-controls="media-2-pane"
                        aria-selected="false">
                        Lịch sử hình thành
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel text-uppercase" id="media-3" data-bs-toggle="tab"
                        data-bs-target="#media-3-pane" type="button" role="tab" aria-controls="media-3-pane"
                        aria-selected="false">
                        Quan hệ cổ đông
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel text-uppercase" id="media-4" data-bs-toggle="tab"
                        data-bs-target="#media-4-pane" type="button" role="tab" aria-controls="media-4-pane"
                        aria-selected="false">
                        Thư viện
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel text-uppercase" id="media-5" data-bs-toggle="tab"
                        data-bs-target="#media-5-pane" type="button" role="tab" aria-controls="media-5-pane"
                        aria-selected="false">
                        Xí nghiệp thành viên
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel text-uppercase" id="media-6" data-bs-toggle="tab"
                        data-bs-target="#media-6-pane" type="button" role="tab" aria-controls="media-6-pane"
                        aria-selected="false">
                        Cộng đồng
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fs-14 font-hanzel text-uppercase" id="media-7" data-bs-toggle="tab"
                        data-bs-target="#media-7-pane" type="button" role="tab" aria-controls="media-7-pane"
                        aria-selected="false">
                        Quan hệ đầu tư
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="box-media-content">
                <div class="tab-pane fade show active" id="media-1-pane" role="tabpanel" aria-labelledby="media-1"
                    tabindex="0">
                    <div class="about-content">
                        <div class="font-hanzel fs-32 fw-400 text-center sub-title">Về CASUMINA</div>
                        <div class="about-content-wrap row">
                            <div class="col-lg-5">
                                <div class="about-content-img-wrap position-relative">
                                    <div class="about-content-img position-relative">
                                        <img src="{{ asset('langding/imgs/img-about.png') }}" alt="About Company"
                                            class="img-fluid w-100">
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <svg width="24" height="36" viewBox="0 0 24 36" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M11.7751 2.75515C11.3943 2.75239 11.0837 3.05886 11.0809 3.43901C11.0781 3.81917 11.3839 4.12977 11.7648 4.13252C12.1449 4.13528 12.4555 3.8295 12.4583 3.44934C12.461 3.06919 12.1553 2.75859 11.7751 2.75515ZM11.7438 6.88721C9.08467 6.86834 6.90697 9.01354 6.88686 11.6718C6.86682 14.3299 9.01318 16.5087 11.6715 16.5287L11.7084 16.5289C14.3497 16.5289 16.5084 14.3901 16.5284 11.7441C16.5484 9.08612 14.4021 6.90725 11.7438 6.88721ZM11.7082 15.1515L11.6818 15.1514C9.783 15.1371 8.24984 13.5808 8.26417 11.6821C8.27842 9.79203 9.82026 8.26438 11.707 8.26438L11.7334 8.26452C13.6322 8.27884 15.1654 9.83514 15.1511 11.7338C15.1367 13.6239 13.595 15.1515 11.7082 15.1515ZM14.7124 3.27745C14.3542 3.15032 13.9603 3.33798 13.8332 3.69651C13.7061 4.05505 13.8938 4.44863 14.2522 4.57576C17.2857 5.65088 19.3074 8.53999 19.2831 11.7649C19.2803 12.1452 19.5863 12.4559 19.9666 12.4588H19.9719C20.3498 12.4588 20.6576 12.1538 20.6605 11.7752C20.6891 7.96356 18.2988 4.54856 14.7124 3.27745Z"
                                                fill="black" />
                                            <path
                                                d="M15.9336 25.9251C20.5142 20.0289 23.3769 16.9543 23.4155 11.7957C23.464 5.30531 18.1968 0 11.7069 0C5.29269 0 0.0491534 5.19464 0.000394203 11.6201C-0.0389988 16.9185 2.87684 19.989 7.48913 25.9242C2.90074 26.6098 0.000394203 28.3327 0.000394203 30.44C0.000394203 31.8517 1.30518 33.1184 3.67448 34.0068C5.83097 34.8154 8.684 35.2608 11.708 35.2608C14.7319 35.2608 17.585 34.8154 19.7415 34.0068C22.1108 33.1183 23.4155 31.8516 23.4155 30.44C23.4155 28.3338 20.5179 26.6113 15.9336 25.9251ZM1.3777 11.6305C1.42068 5.9606 6.04694 1.37738 11.707 1.37738C17.4341 1.37738 22.0809 6.05964 22.0382 11.7855C22.0016 16.6844 18.9657 19.7034 14.1189 26.0216C13.2544 27.148 12.4605 28.213 11.709 29.2548C10.9597 28.2124 10.1816 27.1665 9.30417 26.0212C4.25704 19.4384 1.34038 16.6476 1.3777 11.6305ZM11.708 33.8835C5.79551 33.8835 1.3777 32.0656 1.3777 30.44C1.3777 29.2346 4.01869 27.7117 8.45922 27.1844C9.4408 28.4723 10.3014 29.6424 11.1454 30.8374C11.209 30.9274 11.2932 31.0008 11.3909 31.0515C11.4887 31.1022 11.5972 31.1287 11.7073 31.1287H11.708C11.818 31.1287 11.9264 31.1024 12.0242 31.0518C12.1219 31.0013 12.2062 30.9281 12.2698 30.8383C13.1059 29.6589 13.99 28.4599 14.9632 27.1852C19.3996 27.713 22.0382 29.2354 22.0382 30.4401C22.0382 32.0656 17.6204 33.8835 11.708 33.8835Z"
                                                fill="black" />
                                        </svg>
                                        <span class="fs-18">Văn phòng giao dịch: 146 Nguyễn Biểu, phường Chợ Quán,
                                            TP.Hồ Chí
                                            Minh</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="about-content-text fs-16 fw-300">
                                    Công ty cổ phần Công nghiệp Cao su Miền Nam – CASUMINA – được thành lập từ những năm
                                    đầu sau giải phóng đất nước (19/04/1976), hiện nay là nhà sản xuất săm lốp xe hàng
                                    đầu Việt Nam và là đơn vị dẫn đầu nghành công nghiệp cao su. Với ban lãnh đạo tâm
                                    huyết và ngày càng trẻ hóa, cùng đội ngũ nhân viên sáng tạo, trình độ chuyên môn
                                    vững vàng, CASUMINA đã đề ra tầm nhìn mới rõ ràng : “ Trở thành nhà sản xuất săm lốp
                                    hàng đầu Đông Nam Á”
                                    <ul class="ul-about mt-5">
                                        <li class="d-flex align-items-start gap-2 gap-lg-3">
                                            <img src="{{ asset('langding/imgs/about-img-1.svg') }}" alt="Check Icon">
                                            <div class="content">
                                                <div class="fs-16 fw-bold text-uppercase mb-2">Ngành nghề kinh doanh
                                                </div>
                                                <p>- Sản xuất, kinh doanh các sản phẩm cao su công nghiệp, cao su tiêu
                                                    dùng.</p>
                                                <p>- Kinh doanh, xuất nhập khẩu nguyện liệu, hóa chất, thiết bị ngành
                                                    công nghiệp cao su.</p>
                                                <p>- Kinh doanh thương mại dịch vụ.</p>
                                                <p>- Kinh doanh bất động sản.</p>
                                                <p>- Kinh doanh các ngành nghề khác hợp với qui định của pháp luật.</p>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start gap-2 gap-lg-3">
                                            <img src="{{ asset('langding/imgs/about-img-2.svg') }}" alt="Check Icon">
                                            <div class="content">
                                                <div class="fs-16 fw-bold text-uppercase mb-2">TẦM NHÌN VÀ SỨ MỆNH
                                                </div>
                                                <p>- Nhà sản xuất săm lốp hàng đầu Đông Nam Á.</p>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start gap-2 gap-lg-3">
                                            <img src="{{ asset('langding/imgs/about-img-3.svg') }}" alt="Check Icon">
                                            <div class="content">
                                                <div class="fs-16 fw-bold text-uppercase mb-2">Sứ mệnh của Casumina
                                                </div>
                                                <p>- Cống hiến cho xã hội sự an toàn, hạnh phúc, hiệu quả và thân thiện.
                                                </p>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-start gap-2 gap-lg-3">
                                            <img src="{{ asset('langding/imgs/about-img-4.svg') }}" alt="Check Icon">
                                            <div class="content">
                                                <div class="fs-16 fw-bold text-uppercase mb-2">Giá trị cốt lõi</div>
                                                <p>- Tin cậy: Sản phẩm, dịch vụ, con người.</p>
                                                <p>- Hiệu quả: Mọi hoạt động luôn hướng đến hiệu quả.</p>
                                                <p>- Hợp tác: Sẵn sàn hợp tác cùng phát triển và có lợi.</p>
                                                <p>- Năng động: Luôn sáng tạo va đổi mới.</p>
                                                <p>- Nhân bản: Vì con người.</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @for ($i = 2; $i <= 2; $i++)
                    <div class="tab-pane fade" id="media-{{ $i }}-pane" role="tabpanel"
                    aria-labelledby="media-{{ $i }}" tabindex="0">
                    <div class="about-content">
                        <div style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
                            <div style="position: relative; padding-left: 60px;">
                                <!-- Timeline line -->
                                <div style="position: absolute; left: 20px; top: 0; bottom: 0; width: 3px; background: linear-gradient(to bottom, #d6232c 0%, #d6232c 50%, rgba(214, 35, 44, 0.3) 100%);"></div>

                                <!-- Timeline items -->
                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1976</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Công Ty Công Nghiệp Cao Su Miền Nam được thành lập theo quyết định 427-HC/QĐ ngày 19/04/1976 của Nhà Nước Việt Nam
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1977</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Trụ sở chính của công ty chính thức được đặt tại số 180 Nguyễn Thị Minh Khai quận 3.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1978</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Công ty tiếp quản 5 xí nghiệp là Hóc Môn, Đại Thắng, Bình Lợi, Bình Triệu, Đồng Nai; trưng mua cơ sở Đồng Tâm; quản lý luôn XN Điện Biên vào năm 1979.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1985</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        <p style="margin-bottom: 12px;">Công ty thành lập "Trung tâm xuất nhập khẩu" với tên giao dịch quốc tế là RUBCHIMEX</p>
                                        <p style="margin-bottom: 0;">Công ty ký hợp đồng gia công với Công ty Taurus – Hungary Thành lập Xưởng Việt – Hung, đặt tại Xí nghiệp Cao su Hốc Môn để sản xuất săm lốp xe đạp xuất khẩu theo nghị định thư sang Hungary và các nước Đông Âu.</p>
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1986</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Biểu tượng con sư tử chính thức được chọn làm Logo Công ty.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1988</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Chuyển Công ty Công nghiệp Cao su Miền Nam thành Xí nghiệp liên hợp Cao su Miền Nam.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1989</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Thương hiệu Casumina chính thức được chứng nhận đăng ký, tên gọi Casumina chính thức ra đời với logo sư tử & dòng chữ Casumina màu đỏ nằm dưới bên trong vòng tròn nền vàng.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1990</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Trung tâm nghiên cứu cao su ra đời Kim nghạch xuất khẩu đạt mức hơn 2 triệu rúp chuyển nhượng/ năm.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1991</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Bắt đầu sản xuất lốp xe máy, xây dựng mạng lưới bán hàng cả nước.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1993</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Công ty chính thức đổi tên từ Xí nghiệp Liên hợp Cao su thành Công ty công nghiệp Cao su Miền Nam.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1995</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Sử dụng thương hiệu Casumina thay cho Rubchimex để làm tên giao dịch quốc tế của công ty.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1996</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Casumina trở thành nhà sản xuất săm lốp xe máy số 1 Việt Nam với Slogan "Bạn đường tin cậy".
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1997</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Thành lập Công ty Liên doanh lốp Yokohama Việt Nam với các đối tác: Yokohama và Mitsuibishi Nhật Bản để sản xuất săm lốp ô tô và xe máy.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 1999</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        <p style="margin-bottom: 12px;">Đầu tư một nhà máy chuyên sản xuất lốp ôtô tải với công nghệ hiện đại.</p>
                                        <p style="margin-bottom: 0;">Công ty nhận chứng chỉ ISO 9002 – 1994.</p>
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2000</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Công ty nhận chứng nhận sản phẩm săm lốp xe máy đạt tiêu chuẩn Nhật Bản JIS K6366/ JIS K6367.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2001</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Công ty nhận chứng chỉ ISO 9001 - 2000.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2002</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Công ty nhận chứng nhận sản phẩm lốp ôtô đạt tiêu chuẩn Nhật Bản JIS K4230.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2003</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Lốp ôtô tải nặng đầu tiên được sản xuất và sau đó sản lượng lốp tải nặng của công ty đã tăng lên nhanh chóng và về năng lực sản xuất lẫn khả năng tiêu thụ.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2004</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Thực hiện dự án cấp quốc gia KC06 DA01, chiếc lốp ôtô radial bán thép đầu tiên của Việt Nam ra đời và vinh dự nhận giải thưởng Khoa học sáng tạo Việt Nam Vifotech.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2005</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Công ty đạt doanh số trên 1000 tỷ đồng và được xếp hạng 59/70 nhà sản xuất lốp trên toàn thế giới.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2006</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Công ty Cổ phần Công nghiệp Cao su Miền Nam chính thức đi vào hoạt động với vốn điều lệ ban đầu là 90 tỷ đồng. Tháng 11/2006 tăng vốn điều lệ lên 120 tỷ đồng.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2007</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        <p style="margin-bottom: 12px;">CASUMINA được xếp hạng thứ 59/75 các nhà sản xuất lốp lớn trên thế giới.</p>
                                        <p style="margin-bottom: 0;">Tháng 03/2007 tăng vốn điều lệ lên 150 tỷ đồng.</p>
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2008</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Tăng vốn điều lệ lên 200 tỷ đồng.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2009</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        <p style="margin-bottom: 12px;">Tháng 08/2009 Công ty chính thức niêm yết 25.000.000 cổ phiếu trên Sở giao dịch Chứng khoán Tp.HCM với mã chứng khoán CSM.</p>
                                        <p style="margin-bottom: 0;">Tăng vốn điều lệ lên 250 tỷ đồng.</p>
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2010</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Vốn điều lệ tăng 425 tỷ đồng, đồng thời doanh thu và lợi nhuận cũng tăng trưởng vượt bậc.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2011</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Kỉ niệm 35 năm (19/04/1976 – 19/04/2011) Công y tiếp tục vinh dự đón nhận huân chương độc lập hạng 3 do nhà nước trao tặng.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2012</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Casumina khởi công xây dựng nhà máy sản xuất lốp ôtô toàn thép công suất 1 triệu lốp tại huyện Tân Uyên, tỉnh Bình Dương.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2013</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Casumina hợp tác với đối tác Camso – công ty đứng đầu thế giới về sản xuất lốp xe nâng.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2014</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Khánh thành nhà máy Casumina Radial – Công suất 1 triệu lốp/năm.
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2015</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        <p style="margin-bottom: 12px;">Trở thành nhà máy sản xuất lốp xe máy tubeless đứng đầu Việt Nam.</p>
                                        <p style="margin-bottom: 12px;">Doanh thu công ty đạt 3.600 tỷ, lợi nhuận 370 tỷ đồng, giữ vững vị trí Top 5 về doanh thu và hiệu quả trong Tập đoàn 2 năm liền 2014 – 2015.</p>
                                        <p style="margin-bottom: 0;">Vốn điều lệ tăng trên 740 tỉ đồng.</p>
                                    </div>
                                </div>

                                <div style="margin-bottom: 40px; position: relative;">
                                    <div style="position: absolute; left: -50px; top: 5px; width: 16px; height: 16px; background-color: #d6232c; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 3px #d6232c;"></div>
                                    <div style="margin-bottom: 8px;">
                                        <span style="font-size: 20px; font-weight: 700; color: #d6232c; font-family: 'Hanzel', sans-serif;">Năm 2016</span>
                                    </div>
                                    <div style="font-size: 16px; line-height: 1.8; color: #333; padding-left: 20px; border-left: 2px solid #e0e0e0; margin-left: 10px;">
                                        Kỷ niệm 40 năm thành lập Công ty (1976-2016), Với nhiều thách thức và triển vọng, công ty đã đặt ra nhiều mục tiêu lớn hơn nhằm chinh phục tầm cao mới: doanh thu 3.800 tỷ, lợi nhuận 380 tỷ, triển khai thành công hệ thống bảo trì năng suất toàn diện – TPM, thực hiện dự án đầu tư 500.000 lốp ôtô bán thép xuất sang thị trường Bắc Mỹ.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            @endfor
        </div>
    </div>
</div>
<div class="box-video">
    <div class="container-fluid">
        <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 mb-0 title-main">Video</h2>
        <div class="video-slider mx-auto">
            <div class="video-slider-item">
                <a href="https://www.youtube.com/watch?v=lyaOlS_IVx0" data-fancybox>
                    <div class="bg-img-cover ratio-2-1"
                        style="background-image: url('{{ asset('langding/imgs/video.jpg') }}');">
                        <div class="video-play-btn">
                            <img src="{{ asset('langding/imgs/video.svg') }}" alt="Play">
                        </div>
                    </div>
                </a>
            </div>
            <div class="video-slider-item">
                <a href="https://www.youtube.com/watch?v=lyaOlS_IVx0" data-fancybox>
                    <div class="bg-img-cover ratio-2-1"
                        style="background-image: url('{{ asset('langding/imgs/video.jpg') }}');">
                        <div class="video-play-btn">
                            <img src="{{ asset('langding/imgs/video.svg') }}" alt="Play">
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="bg-video"></div>
    <div class="box-statistics">
        <div class="container-fluid">
            <div class="statistics-overlay">
                <div class="row g-0">
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">45M+</div>
                            <div class="stat-label">LỐP XE ĐƯỢC BÁN</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">5M+</div>
                            <div class="stat-label">ĐẠI LÝ</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">2K+</div>
                            <div class="stat-label">CỬA HÀNG</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <div class="stat-number">30M+</div>
                            <div class="stat-label">KHÁCH HÀNG</div>
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
