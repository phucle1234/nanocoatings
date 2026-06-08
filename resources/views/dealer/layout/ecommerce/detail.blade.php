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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">CHI TIẾT ĐƠN HÀNG</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row order-history-detail">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div id="cart-ecommerce" class="cart-confirm" data-url="{{ route('dealer.ecommerce-change-status-order') }}"
                    data-order-code="{{ $order->order_number }}">
                    <div class="d-flex align-items-center justify-content-between pb-2 mb-5">
                        <span>Tình trạng đơn hàng:</span>
                        {!! \App\Helpers\DealerHelper::saleStatusHtml($order->status) !!}
                    </div>
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                        Thông tin đặt hàng
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="customer-info d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('langding/imgs/no-img.jpg') }}" alt="" class="border">
                        <div class="customer-info-name">{{ $order->user->name }}</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Liên hệ:</span>
                        <span class="fw-500">{{ $order->user->phone }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-secondary">Địa chỉ:</span>
                        <span class="fw-500">{{ $order->user->address }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Mã số đơn hàng:</span>
                        <span class="fw-500">{{ $order->order_number }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Ngày tiếp nhận:</span>
                        <span
                            class="fw-500">{{ $order->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-secondary">Ngày hoàn tất:</span>
                        @if ($order->status == 5 || $order->status == -1)
                            <span
                                class="fw-500">{{ $order->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</span>
                        @else
                            <span class="fw-500 text-warning">Đang xử lý</span>
                        @endif
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
                                    <th scope="col" class="text-secondary fw-500 text-nowrap text-center">Số lượng đặt
                                    </th>
                                    <th scope="col" class="text-secondary fw-500 text-nowrap">Thành tiền</th>
                                    @if (in_array($order->status, [0, 1, 2]))
                                        <th scope="col" class="text-secondary fw-500 text-nowrap text-end">QR Code</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <a href="{{ route('product.detail', ['slug' => $item->product->slug ?? $item->product->id]) }}"
                                                target="_blank">
                                                <div class="product-item d-flex align-items-center gap-2">
                                                    <img src="{{ $item->product->image_urls[0] ?? '' }}" alt=""
                                                        class="product-img"
                                                        onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                                                    <div class="order-detail-customer-name fs-14 fw-600">
                                                        {{ $item->product->translations->firstWhere('language', app()->getLocale())->name ?: 'Chưa có tên sản phẩm' }}
                                                        <div><span
                                                                class="text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">{{ $item->product->sku }}</span>
                                                        </div>
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
                                        @if (in_array($order->status, [0, 1, 2]))
                                            <td id="warranty-{{ $item->id }}" class="text-end">
                                                @if ($item->qrcode)
                                                    @foreach ($item->qrcode as $qrcode)
                                                        <div
                                                            class="d-flex align-items-center justify-content-end gap-2 qr-item">
                                                            <span><i class="bi bi-check2 fs-20 text-success"></i></span>
                                                            <span class="fs-14 text-secondary">{{ $qrcode }}</span>
                                                        </div>
                                                    @endforeach
                                                    @if (count($item->qrcode ?? []) < $item->quantity)
                                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                                            <span role="button" class="fs-12 text-red open-modal"
                                                                data-order-code="{{ $order->order_number }}"
                                                                data-item-id="{{ $item->id }}">
                                                                <span class="text-nowrap">Quét hoặc<br>Nhập mã QR</span>
                                                            </span>
                                                            <span role="button"><i
                                                                    class="bi bi-qr-code-scan fs-20 text-red"></i></span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                                        <span role="button" class="fs-12 text-red open-modal"
                                                            data-order-code="{{ $order->order_number }}"
                                                            data-item-id="{{ $item->id }}">
                                                            <span class="text-nowrap">Quét hoặc<br>Nhập mã QR</span>
                                                        </span>
                                                        <span role="button"><i
                                                                class="bi bi-qr-code-scan fs-20 text-red"></i></span>
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
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
                    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-3 pt-3 border-top">
                        Thông tin vận chuyển
                        <i class="bi bi-info-circle fs-18 text-secondary"></i>
                    </h4>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Người nhận hàng</span>
                        <span class="fw-500">{{ $order->address->name ?? $order->user->name }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Liên hệ</span>
                        <span class="fw-500">{{ $order->address->phone ?? $order->user->phone }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Địa chỉ</span>
                        <span class="fw-500">{{ $order->address->address ?? $order->user->address }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Phương thức vận chuyển</span>
                        <span class="fw-500">Giao tại đại lý</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                        <span class="text-secondary">Ghi chú vận chuyển</span>
                        <span class="fw-500">{{ $order->notes }}</span>
                    </div>
                    @if ($cancelHistory)
                        <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                            Xác nhận đơn hàng
                            <i class="bi bi-info-circle fs-18 text-secondary"></i>
                        </h4>
                        <div class="casumina-note mb-3 pb-3 border-bottom">
                            <p class="fs-14 text-secondary mb-1">Nguyên nhân hủy đơn hàng</p>
                            <p class="fs-14 fw-500 text-danger">{{ $cancelHistory->notes }}</p>
                        </div>
                    @elseif ($order->status == 0)
                        <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                            Xác nhận đơn hàng
                            <i class="bi bi-info-circle fs-18 text-secondary"></i>
                        </h4>
                        <div class="casumina-note mb-3">
                            <p class="fs-14 text-secondary mb-1">Nguyên nhân hủy đơn hàng</p>
                            <textarea class="form-control bg-body-secondary rounded-1 fs-14 py-2" rows="5"
                                placeholder="Nhập nguyên nhân trong trường hợp xác nhận từ chối đơn hàng" name="cancel_reason"
                                id="cancel_reason"></textarea>
                        </div>
                    @endif
                    <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
                        <div class="d-block">
                            <div class="fw-500 d-flex align-items-center gap-2">
                                <i class="bi bi-clipboard2-check"></i>
                                <span class="fs-12 text-secondary">Đơn hàng số</span>
                                <span class="fs-12 fw-600">{{ $order->order_number }}</span>
                                {!! \App\Helpers\DealerHelper::saleStatusHtml($order->status) !!}
                            </div>
                            <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                                <i class="bi bi-check2"></i>
                                <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                                <span
                                    class="fs-12 fw-600">{{ $order->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            @if ($order->status == 0)
                                <button
                                    class="btn btn-dark text-white rounded-1 px-4 py-3 fw-500 text-nowrap btn-cancelled">TỪ
                                    CHỐI ĐƠN HÀNG</button>
                            @endif
                            @if ($order->status != -1 && $order->status != 5)
                                <select name="change-status" id="change-status"
                                    class="form-select bg-danger rounded-1 text-white py-3 fw-500">
                                    @foreach (\App\Helpers\DealerHelper::saleStatus($order->status) as $key => $value)
                                        @if ($key >= $order->status)
                                            <option value="{{ $key }}"
                                                {{ $order->status == $key ? 'data-current=' . $value : '' }}
                                                {{ $order->status == $key ? 'selected' : '' }}>{{ $value }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="warrantyModal" tabindex="-1" aria-labelledby="warrantyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" data-url="{{ route('dealer.ecommerce-certification') }}">
                <input type="hidden" id="modal_order_code">
                <input type="hidden" id="modal_item_id">
                <div class="modal-body text-center">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <div class="warranty-active-input">
                        <div class="mt-5">
                            <input type="text" class="form-control bg-body-secondary fs-14 py-3" id="qrcode"
                                name="qrcode" placeholder="Ex: ABC123">
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-danger text-white rounded-1 px-4 py-2 fw-500 btn-warranty-active">LƯU MÃ
                                QR SẢN PHẨM</button>
                        </div>
                    </div>
                    <div class="text-red mt-4 mb-3 mt-3 pt-3 border-top fs-14">Hoặc quét mã QR</div>
                    <div class="warranty-active-scan">
                        <div class="text-center mt-5">
                            {{-- Vùng render camera --}}
                            <div id="qr-reader" class="d-none w-100"></div>
                            <img src="{{ asset('dealer/imgs/qr-scan.jpg') }}" class="img-fluid" width="150">
                        </div>
                        <div class="mt-3 fs-14">Để kích hoạt bằng mã QR, vui lòng sử dụng thiết bị được trang
                            bị
                            bởi hãng của bạn để quét mã trên sản phẩm</div>
                        <button id="btn-open-camera"
                            class="btn w-100 btn-outline-danger text-red rounded-pill px-2 py-1 fw-500 mt-3">
                            <i class="bi bi-camera me-2 fs-18"></i>Mở quyền truy cập thiết bị
                        </button>
                        <button id="btn-stop-camera"
                            class="btn w-100 btn-danger text-white rounded-pill px-2 py-1 fw-500 mt-3 d-none">
                            <i class="bi bi-x-circle me-2 fs-18"></i>Đóng camera
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('dealer/js/ecommerce/detail.js') }}"></script>
@endpush
