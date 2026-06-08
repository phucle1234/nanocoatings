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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">ĐƠN HÀNG BÁN - TÌM KIẾM SẢN PHẨM</h1>
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
                        <div class="cart-step-name fw-600">Thông tin khách hàng</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 opacity-25">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">3
                        </div>
                        <div class="cart-step-name fw-600">Quét mã qr</div>
                    </div>
                </div>
                <div id="get-product-by-qrcode" class="mb-3 pb-3 border-bottom" data-url="{{ route('dealer.sale-cart.product-by-qrcode') }}" data-add-cart="{{ route('dealer.sale-cart.add-to-cart') }}">
                    <div class="product-scan mt-5">
                        <div class="product-scan-name text-center fw-600 text-red">Mua sản phẩm bằng cách quét mã QR</div>
                        <div class="text-center mt-4">
                            <div id="qr-reader" class="d-none w-100 mx-auto" style="max-width: 500px;"></div>
                            <img src="{{ asset('dealer/imgs/qr-scan.jpg') }}" class="img-fluid" width="150">
                        </div>
                        <div class="row">
                            <div class="d-flex justify-content-center">
                                <div class="col-md-6">
                                    <div class="mt-3 fs-14 text-center">Vui lòng sử dụng thiết bị để quét mã trên sản phẩm</div>
                                    <button id="btn-open-camera" class="btn w-100 btn-outline-danger text-red rounded-pill px-2 py-1 fw-500 mt-3">
                                        <i class="bi bi-camera me-2 fs-20"></i>Mở quyền truy cập thiết bị
                                    </button>
                                    <button id="btn-stop-camera" class="btn w-100 btn-danger text-white rounded-pill px-2 py-1 fw-500 mt-3 d-none">
                                        <i class="bi bi-x-circle me-2 fs-20"></i>Đóng camera
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="input-search mb-3">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" id="search-keyword" class="form-control" placeholder="Tìm kiếm sản phẩm" autocomplete="off">
                    <span id="clear-search" role="button" class="{{ request('keyword') ? '' : 'd-none' }}"><i class="bi bi-x-lg"></i></span>
                </div>
                <div id="product-table-container" data-url="{{ route('dealer.sale-cart') }}">
                    @include('dealer.layout.sale-cart._table-product')
                </div>
                <div class="casumina-sell-preview border rounded p-3 mt-4">
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Xem trước đơn hàng sẽ bán
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div id="sale-cart-container" data-url="{{ route('dealer.sale-cart.load-cart') }}" data-url-update="{{ route('dealer.sale-cart.update-to-cart') }}" data-url-delete="{{ route('dealer.sale-cart.delete-to-cart') }}"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('dealer/js/sale-cart/cart.js') }}"></script>
@endpush
