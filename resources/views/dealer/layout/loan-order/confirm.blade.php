@extends('dealer.index')
@section('title', 'Dashboard')
@section('dealer_content')
    <div class="page-order container-xl container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Đại lý</a>
                </li>
                <li class="breadcrumb-item active fs-15 text-black" aria-current="page">Quản lý hàng hóa</li>
            </ol>
        </nav>
        <h1 class="font-hanzel fs-42 fw-400 mt-5">XÁC NHẬN ĐƠN HÀNG</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div class="cart-step d-flex align-items-center gap-5 mb-5">
                    <div class="cart-step-item d-flex align-items-center gap-2 completed">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">1
                        </div>
                        <div class="cart-step-name fw-600 text-red">Tìm kiếm sản phẩm</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 completed">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">2
                        </div>
                        <div class="cart-step-name fw-600 text-red">Lựa chọn nhà phân phối</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 completed">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">3
                        </div>
                        <div class="cart-step-name fw-600 text-red">Quét mã qr</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 completed">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">4
                        </div>
                        <div class="cart-step-name fw-600 text-red">Xác nhận</div>
                    </div>
                </div>
                <div class="cart-confirm">
                    <div class="text-success fs-20 fw-600"><i class="bi bi-check-lg me-2"></i>ĐƠN HÀNG ĐÃ ĐƯỢC TIẾP NHẬN
                        THÀNH CÔNG</div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-4 pt-3 border-top">
                        Thông tin nhà phân phối nhận hàng mượn
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="customer-info d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('langding/imgs/product/product-avata.png') }}" alt="" class="border">
                        <div class="customer-info-name">Công ty National (Chi nhánh 1)</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Liên hệ:</span>
                        <span class="fw-500">0323232323</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-secondary">Địa chỉ:</span>
                        <span class="fw-500">123 Hồ Anh, Bình Trị, Bình Tân, HCM</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Mã số đơn hàng:</span>
                        <span class="fw-500">ADFZ-03266222</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Ngày tiếp nhận:</span>
                        <span class="fw-500">5/14/2024 3:31:30 PM</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-secondary">Ngày hoàn tất:</span>
                        <span class="fw-500 text-warning">Đang xử lý</span>
                    </div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Thông tin đặt hàng
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Tên sản phẩm</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Danh mục</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Đơn giá</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap text-center">Số lượng</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i <= 6; $i++)
                                    <tr>
                                        <td>
                                            <a href="#">
                                                <div class="product-item d-flex align-items-center gap-2">
                                                    <img src="{{ asset('langding/imgs/product/product-avata.png') }}"
                                                        alt="">
                                                    <div class="order-detail-customer-name fs-14 fw-600">Product Name</div>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <a href=""
                                                class="product-cat text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">VENTURE</a>
                                        </td>
                                        <td>
                                            <span class="product-price fs-14 fw-600">1.000.000đ</span>
                                        </td>
                                        <td class="text-center"><span class="fs-14 fw-600">10</span></td>
                                        <td class="text-end">
                                            <span class="product-price fs-14 fw-600">1.000.000đ</span>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-3 pt-3 border-top">
                        Giá trị đơn hàng
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Tạm tính</span>
                        <span class="fw-500">12.000.000đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">VAT</span>
                        <span class="fw-500">1.200.000đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Tổng thanh toán</span>
                        <span class="fw-500">13.200.000đ</span>
                    </div>
                </div>
                <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
                    <div class="d-block">
                        <div class="fw-500 d-flex align-items-center gap-2">
                            <i class="bi bi-clipboard2-check"></i>
                            <span class="fs-12 text-secondary">Đơn hàng số</span>
                            <span class="fs-12 fw-600">AZZD0323232</span>
                        </div>
                        <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                            <i class="bi bi-check2"></i>
                            <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                            <span class="fs-12 fw-600">03/03/2022 lúc 3:54 PM</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <a href="" class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500">QUẢN LÝ ĐƠN
                            HÀNG</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
