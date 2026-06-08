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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">CHI TIẾT ĐƠN HÀNG BÁN</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã bán.</p>
        <div class="mt-5 row">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div class="d-flex align-items-center justify-content-between pb-2 mb-5">
                    <span>Tình trạng đơn hàng:</span>
                    {!! \App\Helpers\DealerHelper::saleStatusHtml($order->status) !!}
                </div>
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                    Chi tiết đơn hàng bán
                    <i class="bi bi-info-circle fs-18 text-secondary"></i>
                </h4>
                <div id="loan-order-qr" class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col" class="text-secondary fw-500 text-nowrap">Tên sản phẩm</th>
                                <th scope="col" class="text-secondary fw-500 text-nowrap">Danh mục</th>
                                <th scope="col" class="text-secondary fw-500 text-nowrap">Đơn giá</th>
                                <th scope="col" class="text-secondary fw-500 text-nowrap">Số lượng</th>
                                <th scope="col" class="text-secondary fw-500 text-nowrap">Thành tiền</th>
                                <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <a
                                            href="{{ route('dealer.sale-cart.product-detail', ['id' => $item->product->id]) }}">
                                            <div class="product-item d-flex align-items-center gap-2">
                                                <img src="{{ $item->product->image_urls[0] ?? '' }}" alt=""
                                                    class="product-img"
                                                    onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                                                <div class="order-detail-customer-name fs-14 fw-600">
                                                    {{ $item->product?->translations->firstWhere('language', app()->getLocale())?->name ?: 'Chưa có tên sản phẩm' }}
                                                    <div><span
                                                            class="text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">{{ $item->product->sku }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span
                                            class="product-cat text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1 text-nowrap">
                                            {{ $item->product->category?->translations->firstWhere('language', app()->getLocale())?->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="product-price fs-14 fw-600">{{ number_format($item->unit_price, 0, ',', '.') }}đ</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fs-14 fw-600">{{ $item->quantity }}</span>
                                    </td>
                                    <td>
                                        <span
                                            class="product-price fs-14 fw-600">{{ number_format($item->total_price, 0, ',', '.') }}đ</span>
                                    </td>
                                    <td id="warranty-{{ $item->id }}" class="text-end">
                                        @if ($item->qrcode)
                                            @foreach ($item->qrcode as $qrcode)
                                                <div class="d-flex align-items-center justify-content-end gap-2 qr-item">
                                                    <span><i class="bi bi-check2 fs-20 text-success"></i></span>
                                                    <span class="fs-14 text-secondary">{{ $qrcode }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                        @if (in_array($order->status, [0, 1, 2]))
                                            @if (count($item->qrcode ?? []) < $item->quantity)
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <span role="button" class="fs-12 text-red open-modal"
                                                        data-order-code="{{ $order->order_number }}"
                                                        data-item-id="{{ $item->id }}">
                                                        <span class="text-nowrap">Quét hoặc<br>Nhập mã QR</span>
                                                    </span>
                                                    <span role="button">
                                                        <i class="bi bi-qr-code-scan fs-20 text-red"></i>
                                                    </span>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-3 pt-3 border-top">
                    Giá trị đơn hàng
                    <i class="bi bi-info-circle fs-18 text-secondary"></i>
                </h4>
                <div class="d-flex align-items-center justify-content-between mb-3">
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
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-3 pt-3 border-top">
                    Thông tin khách hàng
                    <i class="bi bi-info-circle fs-18 text-secondary"></i>
                </h4>
                <div class="customer-info d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset('langding/imgs/no-img.jpg') }}" alt="" class="border">
                    <div class="customer-info-name">{{ $recipient->name }}</div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">Ngày sinh</span>
                    <span class="fw-500">{{ $recipient?->birthday?->format('d/m/Y') }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">Liên hệ</span>
                    <span class="fw-500">{{ $recipient->phone }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">Địa chỉ</span>
                    <span class="fw-500">{{ $order->address?->address }}</span>
                </div>
                {{-- <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">Nghề nghiệp</span>
                    <span class="fw-500">Kinh doanh</span>
                </div> --}}
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Dòng xe đang chạy</span>
                    <span class="fw-500">{{ $recipient->vehicle }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 mt-3 pt-3 border-top">
                    <span class="text-secondary">Mã số khách hàng</span>
                    <span class="fw-500">{{ $recipient->code }}</span>
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
                            <span
                                class="fs-12 fw-600">{{ $order->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('dealer.sale-cart') }}"
                            class="btn btn-dark text-white rounded-1 px-4 py-3 fw-500">QUAY LẠI</a>
                        <a href="#" class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500">QUẢN LÝ ĐƠN
                            HÀNG</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="warrantyModal" tabindex="-1" aria-labelledby="warrantyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" data-url="{{ route('dealer.sale-cart-certification') }}">
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
    <script src="{{ asset('dealer/js/sale-cart/confirm.js') }}"></script>
@endpush
