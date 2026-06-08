<div class="warranty-certification">
    <div class="warranty-certification-card border rounded-5 mt-4">
        <div class="row">
            <div class="col-sm-3">
                <div class="p-1 d-flex align-items-center justify-content-center h-100">
                    <img src="{{ $traceInfo?->url ? $traceInfo->url : asset('langding/imgs/no-img.jpg') }}"
                        class="warranty-certification-image img-fluid" style="width: 100px">
                </div>
            </div>
            <div class="col-sm-9">
                <div class="warranty-certification-info py-4 px-3 ps-sm-0 pe-sm-3">
                    <p class="fs-16 fw-600 mb-1 text-uppercase">
                        {{ __('messages.traceability_certification.product_name_label') }}
                    </p>
                    <h2 class="font-hanzel fs-26 text-red">{{ $traceInfo?->item_name ?? 'N/A' }}</h2>
                    <div class="d-flex justify-content-between mt-4">
                        <div class="warranty-certification-status">
                            <p class="fw-500 mb-1 fs-14">{{ __('messages.traceability_certification.order_no_label') }}
                            </p>
                            <p class="text-red fw-600">
                                {{ $traceInfo?->order_no != '' ? $traceInfo->order_no : 'N/A' }}
                            </p>

                            <p class="fw-500 mt-4 mb-1 fs-14">
                                {{ __('messages.traceability_certification.status_label') }}
                            </p>
                            @if ($traceInfo?->status == 1)
                                <p class="text-success fw-600 mb-1">
                                    {{ __('messages.traceability_certification.status_activated') }}
                                </p>
                                <p class="fs-14">
                                    <em>{{ __('messages.traceability_certification.date_activated_label') }}:
                                        {{ $traceInfo?->date ?? 'N/A' }}</em>
                                </p>
                            @else
                                <p class="text-danger fw-600 mb-1">
                                    {{ __('messages.traceability_certification.status_unactivated') }}
                                </p>
                            @endif
                        </div>
                        <div class="warranty-certification-qr">
                            <p class="fw-600 mb-1">QR code</p>
                            <div class="text-start border rounded-3 p-2">
                                <div class="warranty-qr-canvas" data-qrcode="{{ $traceInfo?->qrcode ?? 'N/A' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        @if ($traceInfo?->status == 1)
                            <button type="button"
                                class="btn btn-danger rounded-1 px-3 py-2 fw-500 text-white text-uppercase fs-15"
                                data-bs-toggle="modal"
                                data-bs-target="#warrantyModal">{{ __('messages.traceability_certification.btn_detail_text') }}</button>
                            <a href="{{ route('warranty') }}?qr={{ $traceInfo?->qrcode ?? '' }}&order_no={{ $traceInfo?->order_no ?? '' }}"
                                class="btn btn-outline-secondary rounded-1 px-3 py-2 fw-500 text-uppercase fs-15">{{ __('messages.traceability_certification.btn_warranty_text') }}</a>
                        @endif
                        <button type="button"
                            class="btn btn-outline-danger rounded-1 px-3 py-2 fw-500 text-uppercase fs-15 btn-trace-scan-again"
                            data-trace-url="{{ route('traceability') }}">
                            <i class="bi bi-camera me-1"></i>
                            {{ __('messages.traceability_certification.scan_other_order') }}
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="order-btn-link d-flex align-items-center justify-content-end gap-2 mt-5">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <a href="{{ route('traceability') }}"
                class="fw-600">{{ __('messages.traceability_certification.check_other_product') }}</a>
        </div>
    </div>
</div>
<!-- Modal -->
@if ($traceInfo?->status == 1)
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
                    <p class="fs-14 fw-500 my-1">{{ __('messages.traceability_certification.certification_company') }}
                    </p>
                    <h4 class="font-hanzel">CASUMINA</h4>
                    <h2 class="font-hanzel mt-4">
                        <div>{{ __('messages.traceability_certification.certification_text') }}</div>
                        <div class="text-uppercase">
                            {{ __('messages.traceability_certification.certification_product') }}
                        </div>
                    </h2>
                    <div class="d-flex justify-content-center mt-4">
                        <div class="text-start border rounded-3 p-2 bg-white">
                            <div class="warranty-qr-canvas" data-qrcode="{{ $traceInfo?->qrcode ?? 'N/A' }}"></div>
                        </div>
                    </div>
                    <p class="fs-14 fw-500 mt-4 mb-2">
                        {{ __('messages.traceability_certification.product_name_label') }}:
                        {{ $traceInfo?->item_name ?? 'N/A' }}
                    </p>
                    <p class="fs-14 fw-500 mt-2 mb-2">
                        {{ __('messages.traceability_certification.product_code_label') }}:
                        {{ $traceInfo?->item_no ?? 'N/A' }}
                    </p>
                    <p class="fs-14 fw-500 mt-2 mb-2">{{ __('messages.traceability_certification.status_label') }}:
                        {{ __('messages.traceability_certification.status_activated') }}
                    </p>
                    <p class="fs-14 fw-500 mt-2 mb-2">
                        {{ __('messages.traceability_certification.date_activated_label') }}:
                        {{ $traceInfo?->date ?? 'N/A' }}
                    </p>
                    <hr class="my-4 border-light">
                    <div class="fs-14 fw-600">
                        {{ __('messages.traceability_certification.certification_headquarters') }}:
                    </div>
                    <p class="fs-14 fw-400 mt-1 mb-4">180 Nguyen Thi Minh Khai,<br>Vo Thi Sau Ward, District 3, Ho Chi
                        Minh
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
@endif
<div class="modal fade" id="traceScanAgainModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-600">Quét mã truy xuất khác</h5>
                <button type="button" class="btn-close btn-trace-stop-scan-again" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="trace-scan-again-reader" class="w-100"></div>
                <div class="fs-14 text-muted mt-3">
                    Đưa mã QR vào vùng camera để quét.
                </div>
            </div>
        </div>
    </div>
</div>
