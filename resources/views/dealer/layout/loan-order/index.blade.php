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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">ĐƠN HÀNG MƯỢN - TÌM KIẾM SẢN PHẨM</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div class="cart-step d-flex align-items-center gap-5 mb-5">
                    <div class="cart-step-item d-flex align-items-center gap-2 active">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">1
                        </div>
                        <div class="cart-step-name fw-600 text-red">Tìm kiếm sản phẩm</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 opacity-25">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">2
                        </div>
                        <div class="cart-step-name fw-600">Lựa chọn nhà phân phối</div>
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
                <div class="input-search mb-3">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" id="search-keyword"
                        class="form-control" placeholder="Tìm kiếm sản phẩm" autocomplete="off">
                    <span id="clear-search" role="button" class="{{ request('keyword') ? '' : 'd-none' }}"><i
                            class="bi bi-x-lg"></i></span>
                </div>
                <div id="product-table-container" data-url="{{ route('dealer.loan-order') }}">
                    @include('dealer.layout.loan-order._table-product')
                </div>
                <div class="casumina-sell-preview border rounded p-3 mt-5">
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Xem trước đơn hàng sẽ cho mượn
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div id="loan-order-container" data-url="{{ route('dealer.loan-order.load-cart') }}"
                        data-url-update="{{ route('dealer.loan-order.update-to-cart') }}"
                        data-url-delete="{{ route('dealer.loan-order.delete-to-cart') }}"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dealer/js/loan-order/index.js') }}"></script>
@endpush
