@extends('customer.index')
@section('title', 'Dashboard')
@section('customer_content')
@php
$statusValue = is_numeric($order->status) ? (int) $order->status : $order->status;

$statusMap = [
0 => ['label' => 'Mới', 'class' => 'secondary'],
1 => ['label' => 'Xác nhận', 'class' => 'success'],
-1 => ['label' => 'Đã hủy', 'class' => 'danger'],
'pending' => ['label' => 'Chờ xử lý', 'class' => 'warning'],
'processing' => ['label' => 'Đang xử lý', 'class' => 'info'],
'shipped' => ['label' => 'Đã giao hàng', 'class' => 'primary'],
'delivered' => ['label' => 'Đã nhận hàng', 'class' => 'success'],
'cancelled' => ['label' => 'Đã hủy', 'class' => 'danger'],
];

$statusCfg = $statusMap[$statusValue] ?? ['label' => (string) $order->status, 'class' => 'secondary'];

$orderInfo = json_decode($order->address, true);
$recipientName = $orderInfo['name'] ?? $user->name;
$recipientPhone = $orderInfo['phone'] ?? ($user->phone ?? '---');
$recipientAddress = $orderInfo['address'] ?? ($user->address ?? '---');
$recipientNote = $orderInfo['note'] ?? null;

$pricedTotalAmount = collect($order->items ?? [])->reduce(function ($sum, $item) {
    return ((float) ($item->unit_price ?? 0) > 0)
        ? $sum + (float) ($item->total_price ?? 0)
        : $sum;
}, 0);
@endphp

<div class="page-order container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="fs-15 text-black">Dashboard</a></li>

            <li class="breadcrumb-item active fs-15 text-black" aria-current="page">Chi tiết đơn hàng</li>
        </ol>
    </nav>

    <h1 class="font-hanzel fs-42 fw-400 mt-5">
        Quản lý đơn hàng của <span class="text-red">{{ $user->name }}</span>
    </h1>
    <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>

    <div class="mt-1 row g-4">
        <div class="col-lg-3 col-md-4">
            @include('customer.components.sidebar')
        </div>

        <div class="col-lg-9 col-md-8">
            <h1 class="font-hanzel fs-22 fw-400 mb-5">
                Chi tiết đơn hàng: #{{ $order->order_number }}
            </h1>

            <div class="d-flex align-items-center justify-content-between mb-5">
                <span>Tình trạng đơn hàng:</span>
                <span class="badge bg-{{ $statusCfg['class'] }} fs-12 fw-400">{{ $statusCfg['label'] }}</span>
            </div>

            {{-- <div class="order-detail-customer d-flex align-items-center gap-3 mb-2">
                    <img src="{{ asset('langding/imgs/no-img.jpg') }}" alt="" class="order-detail-customer-img border">
            <div class="order-detail-customer-name">{{ $recipientName }}</div>
        </div> --}}

        <div class="order-detail-infomation mt-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary">Tên người nhận:</span>
                <span class="fw-500">{{ $recipientName }}</span>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary">Liên hệ:</span>
                <span class="fw-500">{{ $recipientPhone }}</span>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                <span class="text-secondary">Địa chỉ:</span>
                <span class="fw-500 text-end">{{ $recipientAddress }}</span>
            </div>

            @if($recipientNote)
            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                <span class="text-secondary">Lời nhắn:</span>
                <span class="fw-500 text-end">{{ $recipientNote }}</span>
            </div>
            @endif

            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary">Mã đơn hàng:</span>
                <span class="fw-500">{{ $order->order_number }}</span>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary">Ngày tiếp nhận:</span>
                <span class="fw-500">{{ optional($order->created_at)->format('d/m/Y H:i:s') }}</span>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                <span class="text-secondary">Ngày hoàn tất:</span>
                <span class="fw-500 {{ $order->delivered_at ? 'text-success' : 'text-warning' }}">
                    {{ $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i:s') : 'Chưa hoàn tất' }}
                </span>
            </div>

            <h4 class="fs-18 mb-3 text-secondary">Chi tiết đơn hàng</h4>

            @forelse($order->items as $item)
            @php
            $productName = $item->product?->translations?->firstWhere('language', app()->getLocale())?->name
            ?? $item->product_name
            ?? $item->product_name_with_options
            ?? ($item->product?->sku ?? 'Sản phẩm không tồn tại');

            $productImage = $item->product?->image_urls[0] ?? asset('images/no-image.png');
            @endphp

            <div class="order-product border-bottom pb-2 mb-2">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <img src="{{ $productImage }}" alt="" class="order-product-img border rounded"
                        onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}'">
                    <div class="order-product-info w-100">
                        <h5 class="fs-18 mb-1 text-red d-flex align-items-center gap-2">
                            <span>{{ $productName }}</span>
                        </h5>
                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <span class="fs-14 text-secondary mb-0">{{ __('messages.quantity') }}: x{{ $item->quantity }}</span>
                            <span class="fs-14 text-secondary mb-0">{{ __('messages.unit_price') }}: {{ (float) ($item->unit_price ?? 0) > 0 ? number_format($item->unit_price, 0, ',', '.') . 'đ' : __('messages.contact') }}</span>
                            <span class="fs-14 text-secondary mb-0">{{ __('messages.price') }}: {{ (float) ($item->unit_price ?? 0) > 0 ? number_format($item->total_price, 0, ',', '.') . 'đ' : __('messages.contact') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-muted mb-3">{{ __('messages.no_products') }}</div>
            @endforelse

            <div class="d-flex align-items-center justify-content-between mt-4 mb-3">
                <span class="text-secondary">{{ __('messages.order_total') }}:</span>
                <span class="fw-500">{{ $pricedTotalAmount > 0 ? number_format($pricedTotalAmount, 0, ',', '.') . 'đ' : __('messages.contact') }}</span>
            </div>

            <div class="d-flex align-items-center justify-content-between">
                <span class="text-secondary">{{ __('messages.payment_method') }}:</span>
                <span class="fw-500">{{ $order->getPaymentMethodLabel() }}</span>
            </div>
        </div>

        <div class="order-btn-link d-flex align-items-center justify-content-end gap-2 mt-5">
            <a href="{{ route('shop') }}" class="btn btn-dark text-white rounded-1 p-3 fw-500">{{ __('messages.continue_shopping') }}</a>
            <a href="{{ route('customer.order-list') }}" class="btn btn-danger text-white rounded-1 p-3 fw-500">{{ __('messages.manage_orders') }}</a>
        </div>
    </div>
</div>
</div>
@endsection