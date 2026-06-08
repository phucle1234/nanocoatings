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
        <h1 class="font-hanzel fs-42 fw-400 mt-5">ĐƠN HÀNG MƯỢN - QUÉT MÃ QR</h1>
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
                        <div class="cart-step-name fw-600 text-red">Tìm kiếm sản phẩm</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 completed">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">2
                        </div>
                        <div class="cart-step-name fw-600 text-red">Lựa chọn nhà phân phối</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 active">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">3
                        </div>
                        <div class="cart-step-name fw-600 text-red">Quét mã qr</div>
                    </div>
                    <div class="cart-step-item d-flex align-items-center gap-2 opacity-25">
                        <div class="cart-step-number fw-600 text-white d-flex align-items-center justify-content-center">4
                        </div>
                        <div class="cart-step-name fw-600">Xác nhận</div>
                    </div>
                </div>
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600">
                    Chi tiết đơn hàng mượn
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
                            @foreach ($cart?->items ?? [] as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('dealer.loan-order.product-detail', ['id' => $item->product->id]) }}"
                                            target="_blank">
                                            <div
                                                class="product-item d-flex align-items-center gap-2 {{ isset($item->error) && $item->error != '' ? 'opacity-50' : '' }}">
                                                <img src="{{ $item->product->image_urls[0] ?? '' }}" alt=""
                                                    onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                                                <div class="order-detail-customer-name fs-14 fw-600">
                                                    {{ $item->product?->translations->firstWhere('language', app()->getLocale())?->name ?: 'Chưa có tên sản phẩm' }}
                                                    <div>
                                                        <span
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
                                            class="product-price fs-14 fw-600">{{ number_format($item->unit_price ?? 0, 0, ',', '.') }}đ</span>
                                    </td>
                                    <td>
                                        <span class="fs-14 fw-600">{{ $item->quantity }}</span>
                                    </td>
                                    <td>
                                        <span
                                            class="product-price fs-14 fw-600">{{ number_format($item->total_price ?? 0, 0, ',', '.') }}đ</span>
                                    </td>
                                    <td id="warranty-{{ $item->id }}" class="text-end">
                                        @if ($item->qrcodes ?? [])
                                            @foreach ($item->qrcodes as $qrcode)
                                                <div class="d-flex align-items-center justify-content-end gap-2 qr-item">
                                                    <span><i class="bi bi-check2 fs-20 text-success"></i></span>
                                                    <span class="fs-14 text-secondary">{{ $qrcode }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                        @if (count($item->qrcodes ?? []) < $item->quantity)
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <span role="button" class="fs-12 text-red open-modal"
                                                    data-item-id="{{ $item->id }}">
                                                    <span class="text-nowrap">Quét hoặc<br>Nhập mã QR</span>
                                                </span>
                                                <span role="button">
                                                    <i class="bi bi-qr-code-scan fs-20 text-red"></i>
                                                </span>
                                            </div>
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
                    <span class="fw-500">{{ number_format($cart?->total_amount ?? 0, 0, ',', '.') }}đ</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">VAT</span>
                    <span class="fw-500">{{ number_format($cart?->vat ?? 0, 0, ',', '.') }}đ</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">Tổng thanh toán</span>
                    <span
                        class="fw-500">{{ number_format(($cart?->total_amount ?? 0) + ($cart?->vat ?? 0), 0, ',', '.') }}đ</span>
                </div>
                <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-3 pt-3 border-top">
                    Thông tin phà phân phối nhận hàng mượn
                    <i class="bi bi-info-circle fs-18 text-secondary"></i>
                </h4>
                <div class="partner-item border rounded p-3">
                    <div class="text-red fs-20 font-hanzel">{{ $dealerPartner->name }}</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <i class="bi bi-geo-alt fs-18"></i>
                        <span class="fw-500">{{ $dealerPartner->address }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <i class="bi bi-envelope fs-18"></i>
                        <span class="fw-500">{{ $dealerPartner->email }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <i class="bi bi-telephone-inbound fs-18"></i>
                        <span class="fw-500">{{ $dealerPartner->phone }}</span>
                    </div>
                </div>
                <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
                    <div class="d-block">
                        <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                            <i class="bi bi-check2"></i>
                            <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                            <span
                                class="fs-12 fw-600">{{ $cart->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('dealer.loan-order-partner') }}"
                            class="btn btn-dark text-white rounded-1 px-4 py-3 fw-500">QUAY LẠI</a>
                        <button data-url="{{ route('dealer.loan-order-confirm.submit') }}"
                            class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500 btn-checkout-success">BƯỚC TIẾP
                            THEO<i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="warrantyModal" tabindex="-1" aria-labelledby="warrantyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" data-url="{{ route('dealer.loan-order-qr.certification') }}">
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
    <script src="{{ asset('dealer/js/loan-order/qr.js') }}"></script>
@endpush
