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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">ĐƠN HÀNG MƯỢN - LỰA CHỌN NHÀ PHÂN PHỐI</h1>
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
                    <div class="cart-step-item d-flex align-items-center gap-2 active">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">2
                        </div>
                        <div class="cart-step-name fw-600 text-red">Lựa chọn nhà phân phối</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 opacity-25">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">3
                        </div>
                        <div class="cart-step-name fw-600">Quét mã qr</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 opacity-25">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">4
                        </div>
                        <div class="cart-step-name fw-600">Xác nhận</div>
                    </div>
                </div>
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                    Chi tiết đơn hàng mượn
                    <i class="bi bi-info-circle fs-18 text-secondary"></i>
                </h4>
                <div id="loan-order-container" data-url="{{ route('dealer.loan-order.load-cart') }}"
                    data-url-update="{{ route('dealer.loan-order.update-to-cart') }}"
                    data-url-delete="{{ route('dealer.loan-order.delete-to-cart') }}"></div>
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-5 pt-3 border-top">
                    Thông tin nhà phân phối nhận hàng mượn
                    <i class="bi bi-info-circle fs-18 text-secondary"></i>
                </h4>
                <form id="loan-partner-form" action="{{ route('dealer.loan-order-partner.submit') }}" method="POST">
                    <div class="partner-list">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="city_code" class="form-label fw-600 fs-14">Tỉnh / Thành phố<span
                                            class="text-danger">*</span></label>
                                    <select class="form-select bg-body-secondary fs-14 py-2" id="city_code" name="city_code"
                                        data-url="{{ route('dealer.loan-order.dealers-by-city-code') }}" data-dealer-partner-code-selected="{{ $dealerPartnerCodeSelected }}">
                                        <option value="">Chọn tỉnh</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->code }}" {{ $provinceSelected == $province->code ? 'selected' : '' }}>{{ $province->name_vi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="dealer_partner_code" class="form-label fw-600 fs-14">Danh sách nhà phân
                                        phối<span class="text-danger">*</span></label>
                                    <select class="form-select bg-body-secondary fs-14 py-2" id="dealer_partner_code"
                                        name="dealer_partner_code">
                                        <option value="">Chọn nhà phân phối</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="partner-item-wrapper"></div>
                    </div>
                    <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
                        <div class="d-block">
                            <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                                <i class="bi bi-check2"></i>
                                <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                                <span
                                    class="fs-12 fw-600">{{ $cart->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('dealer.loan-order') }}"
                                class="btn btn-dark text-white rounded-1 px-4 py-3 fw-500">QUAY LẠI</a>
                            <button type="submit"
                                class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500 btn-partner-success">BƯỚC TIẾP
                                THEO<i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dealer/js/loan-order/partner.js') }}"></script>
@endpush
