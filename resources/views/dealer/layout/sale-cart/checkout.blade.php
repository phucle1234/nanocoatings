@extends('dealer.index')
@section('title', 'Dashboard')
@section('dealer_content')
    <div class="page-casumina container-xl container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Đại lý</a>
                </li>
                <li class="breadcrumb-item active fs-15 text-black" aria-current="page">Quản lý hàng hóa</li>
            </ol>
        </nav>
        <h1 class="font-hanzel fs-42 fw-400 mt-5">ĐƠN HÀNG BÁN - THÔNG TIN KHÁCH HÀNG</h1>
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
                        <div class="cart-step-name fw-600 text-red">Thông tin khách hàng</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 opacity-25">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">3
                        </div>
                        <div class="cart-step-name fw-600">Quét mã qr</div>
                    </div>
                </div>
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                    Chi tiết đơn hàng bán
                    <i class="bi bi-info-circle fs-18 text-secondary"></i>
                </h4>
                <div id="sale-cart-container" data-url="{{ route('dealer.sale-cart.load-cart') }}"
                    data-url-update="{{ route('dealer.sale-cart.update-to-cart') }}"
                    data-url-delete="{{ route('dealer.sale-cart.delete-to-cart') }}"></div>
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-5 pt-3 border-top">
                    Tìm kiếm khách hàng sẵn có
                    <i class="bi bi-info-circle fs-18 text-secondary"></i>
                </h4>
                <div class="input-search mb-3">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" id="search-keyword"
                        class="form-control" placeholder="Tìm kiếm khách hàng" autocomplete="off">
                    <span id="clear-search" role="button" class="{{ request('keyword') ? '' : 'd-none' }}"><i
                            class="bi bi-x-lg"></i></span>
                </div>
                <form id="sale-checkout-form" action="{{ route('dealer.sale-checkout-info') }}" method="POST">
                    <div id="product-table-container" data-url="{{ route('dealer.sale-checkout') }}">
                        @include('dealer.layout.sale-cart._table-customer')
                    </div>
                    <div class="insert-new-customer mt-5">
                        <p>Hoặc</p>
                        <h4 class="fs-15 mb-2 d-flex gap-2 align-items-center">
                            <div class="form-check mb-0 d-flex align-items-center gap-2 ps-4">
                                <input class="form-check-input fs-18" type="checkbox" value="create-new"
                                    id="check-create-new" name="has_create_new">
                                <label class="form-check-label text-red fw-600 fs-16" for="check-create-new" role="button">
                                    Nhấp vào đây
                                </label>
                            </div>
                            <span>để tạo khách hàng mới. </span>
                            <i class="bi bi-info-circle fs-18 text-secondary"></i>
                        </h4>
                        <div class="new-customer-info d-none">
                            <p class="mb-0 fs-14">Vui lòng điền đầy đủ thông tin để quá trình kích hoạt bảo hành được thực
                                hiện
                                thuận tiện và nhanh chóng nhất có thể.</p>
                            <p class="fs-14 mb-0">Lưu ý: Những mục đánh dấu * là những mục thông tin bắt buộc.</p>
                            <h2 class="font-hanzel text-red fs-24 my-4">THÔNG TIN THIẾT YẾU *</h2>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-600 fs-14">Tên đầy đủ <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-body-secondary fs-14 py-2"
                                            id="name" name="name" placeholder="Họ và tên">
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="mb-3">
                                        <label for="gender" class="form-label fw-600 fs-14">Giới tính <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select bg-body-secondary fs-14 py-2" id="gender"
                                            name="gender">
                                            <option value="">Giới tính</option>
                                            <option value="Nam">Nam</option>
                                            <option value="Nữ">Nữ</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label fw-600 fs-14">Liên hệ <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-body-secondary fs-14 py-2"
                                            id="phone" name="phone" placeholder="SĐT">
                                    </div>
                                </div>
                            </div>
                            <h2 class="font-hanzel text-red fs-24 my-4">THÔNG TIN TÙY CHỌN</h2>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-600 fs-14">Địa chỉ email</label>
                                        <input type="text" class="form-control bg-body-secondary fs-14 py-2"
                                            id="email" name="email" placeholder="Địa chỉ email">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label for="zalo" class="form-label fw-600 fs-14">Zalo liên hệ</label>
                                        <input type="text" class="form-control bg-body-secondary fs-14 py-2"
                                            id="zalo" name="zalo" placeholder="SĐT đăng ký Zalo">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="facebook" class="form-label fw-600 fs-14">Địa chỉ facebook</label>
                                <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="facebook"
                                    name="facebook" placeholder="Đường dẫn đến hồ sơ facebook cá nhân">
                            </div>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="mb-3">
                                        <label for="vehicle" class="form-label fw-600 fs-14">Dòng xe đang sử dụng</label>
                                        <input type="text" class="form-control bg-body-secondary fs-14 py-2"
                                            id="vehicle" name="vehicle" placeholder="Loại xe đang chạy">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label for="license-plate" class="form-label fw-600 fs-14">Biển số xe</label>
                                        <input type="text" class="form-control bg-body-secondary fs-14 py-2"
                                            id="license-plate" name="license-plate" placeholder="Biển số xe">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label for="city" class="form-label fw-600 fs-14">Tỉnh / Thành phố<span
                                                class="text-danger">*</span></label>
                                        <select class="form-select bg-body-secondary fs-14 py-2" id="city"
                                            name="city">
                                            <option value="">Chọn tỉnh/thành phố</option>
                                            @foreach ($provainces as $province)
                                                <option value="{{ $province->code }}">{{ $province->name_vi }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="mb-3">
                                        <label for="address" class="form-label fw-600 fs-14">Địa chỉ chi
                                            tiết<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control bg-body-secondary fs-14 py-2"
                                            id="address" name="address"
                                            placeholder="Nhập địa chỉ chi tiết, nơi khách hàng đang sinh sống">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
                            <div class="d-block">
                                <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                                    <i class="bi bi-check2"></i>
                                    <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                                    <span class="fs-12 fw-600">{{ $cart->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <a href="{{ route('dealer.sale-cart') }}"
                                    class="btn btn-dark text-white rounded-1 px-4 py-3 fw-500">QUAY LẠI</a>
                                <button
                                    class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500 btn-checkout-success">BƯỚC
                                    TIẾP
                                    THEO<i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dealer/js/sale-cart/checkout.js') }}"></script>
@endpush
