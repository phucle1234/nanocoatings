<div class="warranty-certification">
    <div class="warranty-certification-search d-flex align-items-center gap-3">
        <span class="font-hanzel fs-30 text-red">01</span>
        <span class="fw-600">Sản phẩm được tìm thấy</span>
    </div>
    <div class="warranty-certification-card border rounded-5 mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="p-2 d-flex align-items-center justify-content-center h-100">
                    <img src="{{ $warrantyInfo?->url ? $warrantyInfo->url : asset('langding/imgs/no-img.jpg') }}"
                        class="warranty-certification-image img-fluid">
                </div>
            </div>
            <div class="col-md-9">
                <div class="warranty-certification-info py-4 pe-4">
                    <p class="fs-18 fw-600 mb-1">TÊN SẢN PHẨM</p>
                    <h2 class="font-hanzel fs-30 text-red">{{ $warrantyInfo?->item_name ?? 'N/A' }}</h2>
                    <div class="d-flex justify-content-between mt-4">
                        <div class="warranty-certification-status">
                            <p class="fw-600 mb-1">Mã đơn hàng</p>
                            <p class="text-red fw-600">
                                {{ $warrantyInfo?->order_no != '' ? $warrantyInfo->order_no : 'N/A' }}</p>
                            <p class="fw-600 mt-4 mb-1">Tình trạng bảo hành</p>
                            @if ($warrantyInfo?->status == 1)
                                <p class="text-success fw-600 mb-1">Đã kích hoạt</p>
                                <p class="fs-14"><em>Ngày kích hoạt: {{ $warrantyInfo?->date ?? 'N/A' }}</em></p>
                            @else
                                <p class="text-danger fw-600">Chưa kích hoạt</p>
                            @endif
                        </div>
                        <div class="warranty-certification-qr me-5">
                            <p class="fw-600 mb-1">QR code</p>
                            <div class="text-start border rounded-3 p-2">
                                <div class="warranty-qr-canvas" data-qrcode="{{ $warrantyInfo?->qrcode ?? 'N/A' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-3">
                        @if ($warrantyInfo?->status == 1)
                            <button type="button" class="btn btn-danger rounded-1 px-3 py-2 fw-500 text-white"
                                data-bs-toggle="modal" data-bs-target="#warrantyModal">XEM CHỨNG NHẬN</button>
                            <button type="button" class="btn btn-dark text-white rounded-1 px-3 py-2 fw-500"
                                data-bs-toggle="modal" data-bs-target="#requestWarrantyModal">ĐỀ NGHỊ BẢO HÀNH</button>
                        @elseif($warrantyInfo?->order_no != '')
                            <button data-url="{{ route('dealer.warranty-certification') }}"
                                class="btn btn-danger rounded-1 px-3 py-2 fw-500 text-white btn-certification"
                                data-order-code="{{ $warrantyInfo->order_no }}"
                                data-qrcode="{{ $warrantyInfo->qrcode }}">KÍCH HOẠT BẢO HÀNH</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
        <div class="d-block">
            <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                <i class="bi bi-check2"></i>
                <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                <span
                    class="fs-12 fw-600">{{ now()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <a href="{{ route('dealer.warranty') }}" class="fw-600">Tìm kiếm sản phẩm khác</a>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="warrantyModal" tabindex="-1" aria-labelledby="warrantyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-img-cover"
            style="background-image: url('{{ asset('dealer/imgs/warranty-background.jpg') }}');">
            <div class="modal-body text-center text-white">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                <div class="warranty-logo mx-auto">
                    <img src="{{ asset('dealer/imgs/warranty-logo.svg') }}" alt="Casumina Logo" class="img-fluid">
                </div>
                <p class="fs-14 fw-500 my-1">Công ty Cổ phần Công nghiệp Cao su Miền Nam</p>
                <h4 class="font-hanzel">CASUMINA</h4>
                <h2 class="font-hanzel mt-4">Chứng nhận<BR>SẢN PHẨM CHÍNH HÃNG</h2>
                <div class="d-flex justify-content-center mt-4">
                    <div class="text-start border rounded-3 p-2 bg-white">
                        <div class="warranty-qr-canvas" data-qrcode="{{ $warrantyInfo?->qrcode ?? 'N/A' }}"></div>
                    </div>
                </div>
                <p class="fs-14 fw-500 mt-4 mb-2">Tên sản phẩm: {{ $warrantyInfo?->item_name ?? 'N/A' }}</p>
                <p class="fs-14 fw-500 mt-2 mb-2">Mã sản phẩm: {{ $warrantyInfo?->item_no ?? 'N/A' }}</p>
                <p class="fs-14 fw-500 mt-2 mb-2">Tình trạng bảo hành:
                    {{ $warrantyInfo?->status == 1 ? 'Đã kích hoạt' : 'Chưa kích hoạt' }}</p>
                @if ($warrantyInfo?->status == 1)
                    <p class="fs-14 fw-500 mt-2 mb-2">Ngày kích hoạt: {{ $warrantyInfo?->date ?? 'N/A' }}</p>
                @endif
                <hr class="my-4 border-light">
                <div class="fs-14 fw-600">Trụ sở chính:</div>
                <p class="fs-14 fw-400 mt-1 mb-4">180 Nguyen Thi Minh Khai,<br>Vo Thi Sau Ward, District 3, Ho Chi Minh
                    City
                </p>
                <p class="fs-14 d-flex align-items-center justify-content-center gap-1 mb-1">
                    <span class="fw-600">Phone:</span>
                    <span class="fw-400">(028) 38 362 369 - 362 373</span>
                </p>
                <p class="fs-14 d-flex align-items-center justify-content-center gap-1 mb-1">
                    <span class="fw-600">Fax:</span>
                    <span class="fw-400">(028) 38 362 367</span>
                </p>
                <p class="fs-14 d-flex align-items-center justify-content-center gap-1 mb-1">
                    <span class="fw-600">Email:</span>
                    <span class="fw-400">casumina@casumina.com.vn</span>
                </p>
            </div>
        </div>
    </div>
</div>
@if ($warrantyInfo?->status == 1)
    <div class="modal fade" id="requestWarrantyModal" tabindex="-1" aria-labelledby="requestWarrantyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-1" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                <div class="modal-body">
                    <div class="warranty-logo mx-auto text-center">
                        <img src="{{ asset('dealer/imgs/warranty-logo.svg') }}" alt="Casumina Logo"
                            class="img-fluid" width="90">
                    </div>
                    <h4 class="font-hanzel text-center fs-22 text-red mt-3 mb-5">ĐỀ NGHỊ BẢO HÀNH SẢN PHẨM</h4>
                    <form method="POST" action="{{ route('dealer.warranty-request') }}">
                        <input type="hidden" name="type" value="product">
                        <div class="mb-3">
                            <label for="fullname" class="form-label fw-600 fs-14">
                                Họ tên khách hàng
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="fullname"
                                name="fullname" placeholder="Nhập họ và tên khách hàng"
                                value="{{ $warrantyInfo->customer_name ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-600 fs-14">
                                Số điện thoại
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="phone"
                                name="phone" placeholder="Nhập số điện thoại"
                                value="{{ $warrantyInfo->phone ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-600 fs-14">
                                Email
                                <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control bg-body-secondary fs-14 py-2" id="email"
                                name="email" placeholder="Nhập email" value="{{ $warrantyInfo->email ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="order_no" class="form-label fw-600 fs-14">Số hóa đơn (nếu có)</label>
                            <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="order_no"
                                name="order_no" placeholder="Nhập số hóa đơn"
                                value="{{ $warrantyInfo->order_no ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="qrcode" class="form-label fw-600 fs-14">
                                QR code
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control bg-body-secondary fs-14 py-2" id="qrcode"
                                name="qrcode" placeholder="Nhập QR code" value="{{ $warrantyInfo->qrcode ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label fw-600 fs-14">
                                Nội dung bảo hành
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control bg-body-secondary fs-14 py-2" id="content" name="content" rows="4"
                                placeholder="Nội dung bảo hành, tình trạng..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary rounded-1 px-4 py-2 fw-500"
                                data-bs-dismiss="modal">HỦY</button>
                            <button type="submit" class="btn btn-danger text-white rounded-1 px-4 py-2 fw-500 btn-request-warranty">GỬI
                                YÊU
                                CẦU</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
