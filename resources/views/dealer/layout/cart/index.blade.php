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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">THÔNG TIN GIỎ HÀNG</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div class="input-search mb-3">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" id="search-keyword"
                        class="form-control" placeholder="Tìm kiếm sản phẩm" autocomplete="off">
                    <span id="clear-search" role="button" class="{{ request('keyword') ? '' : 'd-none' }}"><i
                            class="bi bi-x-lg"></i></span>
                </div>
                <div id="product-table-container" data-url="{{ route('dealer.cart') }}">
                    @include('dealer.layout.cart._table-product')
                </div>
                <div class="cart-preview border rounded p-3 mt-4">
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Xem trước đơn hàng sẽ mua
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div id="cart-container" data-url="{{ route('dealer.cart.load-cart') }}"
                        data-url-update="{{ route('dealer.cart.update-to-cart') }}"
                        data-url-delete="{{ route('dealer.cart.delete-to-cart') }}"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dealer/js/cart/cart.js') }}"></script>
@endpush
