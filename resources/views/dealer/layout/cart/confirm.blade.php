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
                        <div class="cart-step-name fw-600 text-red">Thông tin giỏ hàng</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 completed">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">2
                        </div>
                        <div class="cart-step-name fw-600 text-red">Thông tin thanh toán</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 completed">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">3
                        </div>
                        <div class="cart-step-name fw-600 text-red">Xác nhận đơn hàng</div>
                    </div>
                </div>
                <div class="cart-confirm">
                    <div class="text-success fs-20 fw-600"><i class="bi bi-check-lg me-2"></i>ĐƠN HÀNG ĐÃ ĐƯỢC TIẾP NHẬN
                        THÀNH CÔNG</div>
                    <div class="d-flex align-items-center justify-content-between mt-3 pb-2 mb-4 border-bottom">
                        <span>Tình trạng đơn hàng:</span>
                        <span class="badge bg-secondary fs-12 fw-400">
                            {!! \App\Helpers\DealerHelper::buyStatusHtml($order->status) !!}
                        </span>
                    </div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Thông tin đặt hàng
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="customer-info d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('langding/imgs/no-img.jpg') }}" alt="" class="border">
                        <div class="customer-info-name">{{ $user->name }}</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Liên hệ:</span>
                        <span class="fw-500">{{ $order->address?->phone }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-secondary">Địa chỉ:</span>
                        <span class="fw-500">{{ $order->address?->address }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Mã số đơn hàng:</span>
                        <span class="fw-500">{{ $order->order_number }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Ngày tiếp nhận:</span>
                        <span class="fw-500">{{ $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-secondary">Ngày hoàn tất:</span>
                        <span class="fw-500 text-warning">Đang xử lý</span>
                    </div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Chi tiết đơn hàng
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Tên sản phẩm</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Đơn giá</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap text-center">Số lượng</th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <a href="{{ route('dealer.cart.product-detail', ['id' => $item->product->id]) }}">
                                                <div class="product-item d-flex align-items-center gap-2">
                                                    <img src="{{ $item->product->image_urls[0] ?? '' }}" alt=""
                                                        class="product-img"
                                                        onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                                                    <div class="order-detail-customer-name fs-14 fw-600 text-nowrap">
                                                        {{ $item->product->translations->firstWhere('language', app()->getLocale())->name ?: 'Chưa có tên sản phẩm' }}
                                                        <div><span class="text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">{{ $item->product->sku }}</span></div>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <span
                                                class="product-price fs-14 fw-600">{{ number_format($item->unit_price, 0, ',', '.') }}đ</span>
                                        </td>
                                        <td class="text-center"><span class="fs-14 fw-600">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span
                                                class="product-price fs-14 fw-600">{{ number_format($item->total_price, 0, ',', '.') }}đ</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 mt-3 pt-3 border-top">
                        <span class="text-secondary">Tạm tính</span>
                        <span class="fw-500">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">VAT</span>
                        <span class="fw-500">{{ number_format($order->tax_amount, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Tổng thanh toán</span>
                        <span class="fw-500">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Phương thức thanh toán</span>
                        <span class="fw-500">Thanh toán khi nhận hàng</span>
                    </div>
                </div>
                <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
                    <div class="d-block">
                        <div class="fw-500 d-flex align-items-center gap-2">
                            <i class="bi bi-clipboard2-check"></i>
                            <span class="fs-12 text-secondary">Đơn hàng số</span>
                            <span class="fs-12 fw-600">{{ $order->order_number }}</span>
                        </div>
                        <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                            <i class="bi bi-check2"></i>
                            <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                            <span class="fs-12 fw-600">{{ $order->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <a href="" class="btn btn-dark text-white rounded-1 px-4 py-3 fw-500">LIÊN HỆ</a>
                        <a href="{{ route('dealer.order-history') }}"
                            class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500">THEO DÕI ĐƠN
                            HÀNG</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
