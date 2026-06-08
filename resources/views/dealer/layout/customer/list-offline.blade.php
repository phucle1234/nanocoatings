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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">QUẢN LÝ KHÁCH HÀNG</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row order-history">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <ul class="status-items list-unstyled d-flex align-items-center">
                    <li class="status-item px-4 py-2 fs-16 fw-500">
                        <a href="{{ route('dealer.customer') }}" class="text-body-tertiary opacity-50">
                            Toàn bộ
                        </a>
                    </li>
                    <li class="status-item px-4 py-2 fs-16 fw-500">
                        <a href="{{ route('dealer.customer-online') }}" class="text-body-tertiary opacity-50">
                            Online
                        </a>
                    </li>
                    <li class="status-item px-4 py-2 fs-16 fw-500 border-bottom border-2 border-danger">
                        <a href="{{ route('dealer.customer-offline') }}" class="text-red">
                            Offline
                        </a>
                    </li>
                </ul>
                <div class="input-search mb-3">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" id="search-keyword"
                        class="form-control" placeholder="Tìm kiếm khách hàng" autocomplete="off">
                    <span id="clear-search" role="button" class="{{ request('keyword') ? '' : 'd-none' }}"><i
                            class="bi bi-x-lg"></i></span>
                </div>
                <div id="customer-table-container" data-url="{{ route('dealer.customer-offline') }}">
                    @include('dealer.layout.customer._table-customer')
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dealer/js/customer/list.js') }}"></script>
@endpush
