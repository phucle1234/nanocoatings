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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">THÔNG TIN CHI TIẾT KHÁCH HÀNG</h1>
        <p>Nơi kiểm tra và theo dõi thông tin khách hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row customer-detail">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div class="cart-confirm">
                    <div class="d-flex align-items-center justify-content-between pb-2 mb-5">
                        <span>Tiếp nhận khách hàng thông qua hình thức:</span>
                        @if ($customer->channel == 'online')
                            <span class="badge bg-success text-white fs-12 fw-400">Online</span>
                        @elseif($customer->channel == 'offline')
                            <span class="badge bg-warning text-white fs-12 fw-400">Offline</span>
                        @else
                            <span class="badge bg-secondary text-white fs-12 fw-400">Chưa xác định</span>
                        @endif
                    </div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Thông tin khách hàng
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="customer-info d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $customer->avatar }}" alt="" class="border"
                            onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                        <div class="customer-info-name">{{ $customer->name }}</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Liên hệ</span>
                        <span class="fw-500">{{ $customer->phone }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Địa chỉ</span>
                        <span class="fw-500">{{ $customer->address }}</span>
                    </div>
                    {{-- <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Nghề nghiệp</span>
                        <span class="fw-500">{{ $customer->occupation }}</span>
                    </div> --}}
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-secondary">Dòng xe đang chạy</span>
                        <span class="fw-500">{{ $customer->vehicle }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Mã số khách hàng:</span>
                        <span class="fw-500">{{ $customer->code }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Ngày tiếp nhận:</span>
                        <span class="fw-500">{{ $customer->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-secondary">Đơn hàng gần nhất:</span>
                        <span class="fw-500">{{ $orders[0]->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Lịch sử mua hàng
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Mã đơn hàng</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Thời gian</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Tổng giá trị</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Tác vụ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('dealer.sale-order-detail', $order->id) }}"
                                                class="fw-500">#{{ $order->order_number }}</a>
                                        </td>
                                        <td>
                                            <span class="fs-14 fw-600">{{ $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</span>
                                        </td>
                                        <td>
                                            <span class="product-price fs-14 fw-600">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-3">
                                                <a href="{{ route('dealer.sale-order-detail', $order->id) }}"><i class="bi bi-eye fs-22"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <span class="fs-14 fw-600">Không có đơn hàng nào</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-3 pt-3 border-top">
                        Tổng doanh thu
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Số đơn</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap text-center">Tăng trưởng</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="fs-14 fw-600">{{ $orders->count() }} đơn</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fs-14 fw-600">-12%</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fs-14 fw-600">{{ number_format($orders->sum('total_amount'), 0, ',', '.') }}đ</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
                    <div class="d-block">
                        <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                            <i class="bi bi-check2"></i>
                            <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                            <span class="fs-12 fw-600">{{ $orders->first()?->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <a href="" class="btn btn-dark text-white rounded-1 px-4 py-3 fw-500">QUAY LẠI</a>
                        <a href="" class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500">LIÊN HỆ KHÁCH HÀNG</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
