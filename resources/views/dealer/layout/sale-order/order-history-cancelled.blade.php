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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">LỊCH SỬ BÁN HÀNG CỦA NPP</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row casumina-history">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div class="btn-group mb-3">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        Hủy
                    </button>
                    <ul class="dropdown-menu">
                        @foreach (\App\Helpers\DealerHelper::saleStatusWithRoute('sale') as $route => $label)
                            <li><a class="dropdown-item fs-14" href="{{ $route }}">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="input-search mb-3">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" id="search-keyword"
                        class="form-control" placeholder="Tìm kiếm đơn hàng" autocomplete="off">
                    <span id="clear-search" role="button" class="{{ request('keyword') ? '' : 'd-none' }}"><i
                            class="bi bi-x-lg"></i></span>
                </div>
                <div id="sale-order-history-table-container" data-url="{{ route('dealer.sale-order-history-cancelled') }}">
                    @include('dealer.layout.sale-order._table-order-history')
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dealer/js/sale-order/order-history.js') }}"></script>
@endpush
