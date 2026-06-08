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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">THÔNG TIN VẬN CHUYỂN</h1>
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
                        <div class="cart-step-name fw-600 text-red">Thông tin giỏ hàng</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 active">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">2
                        </div>
                        <div class="cart-step-name fw-600 text-red">Thông tin thanh toán</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 opacity-25">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">3
                        </div>
                        <div class="cart-step-name fw-600">Xác nhận đơn hàng</div>
                    </div>
                </div>
                <form id="cart-checkout-form" action="{{ route('dealer.cart.checkout-info') }}" method="POST">
                    <div class="cart-info">
                        <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 pb-3 mb-4 border-bottom">
                            Thông tin người đặt hàng
                            <i class="bi bi-info-circle fs-18 text-secondary"></i>
                        </h4>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-600 fs-14">Tên người nhận <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="name"
                                        name="name" placeholder="Ex: Nguyễn Văn A" value="{{ $user->name }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-600 fs-14">Số điện thoại <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="phone"
                                        name="phone" placeholder="Ex: 0901234567" value="{{ $user->phone }}">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-600 fs-14">Email đặt hàng <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="email"
                                name="email" placeholder="Ex: example@email.com" value="{{ $user->email }}">
                        </div>
                        <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-5 pb-3 mb-4 border-bottom">
                            Địa chỉ nhận hàng
                            <i class="bi bi-info-circle fs-18 text-secondary"></i>
                        </h4>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label fw-600 fs-14">Tỉnh / Thành phố <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select bg-body-secondary fs-14 py-2" id="city" name="city">
                                        <option value="">Chọn tỉnh/thành phố</option>
                                        @foreach ($provainces as $province)
                                            <option value="{{ $province->code }}"
                                                {{ $user->city_code == $province->code ? 'selected' : '' }}>
                                                {{ $province->name_vi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <label for="address" class="form-label fw-600 fs-14">Địa chỉ chi tiết <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="address"
                                        name="address" placeholder="Ex: 123 Đường ABC, Phường XYZ, Quận 1, TP. HCM"
                                        value="{{ $user->address }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label fw-600 fs-14">Ghi chú đơn hàng</label>
                            <textarea class="form-control bg-body-secondary fs-14 py-2" id="note" rows="5" name="note"
                                placeholder="Ex: Giao hàng trong giờ hành chính, không giao vào thứ 7 và chủ nhật"></textarea>
                        </div>
                    </div>
                    <div class="order-btn-link d-flex align-items-center justify-content-end gap-2 mt-5">
                        {{-- <div class="d-block">
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
                        </div> --}}
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('dealer.cart') }}"
                                class="btn btn-dark text-white rounded-1 px-4 py-3 fw-500">QUAY LẠI</a>
                            <button type="submit"
                                class="btn-checkout-success btn btn-danger text-white rounded-1 px-4 py-3 fw-500">BƯỚC TIẾP
                                THEO<i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dealer/js/cart/checkout.js') }}"></script>
@endpush
