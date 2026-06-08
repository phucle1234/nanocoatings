@extends('langding.index')
@section('title', __('messages.traceability_center') . ' - Casumina')
@section('langding_content')
<div class="page-traceability">
    <div class="container-fluid py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb breadcrumb-dark">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="fs-15 text-black">{{ __('messages.home') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#" class="fs-15 text-black">{{ __('messages.support') }}</a>
                </li>
                <li class="breadcrumb-item active fs-15 text-black" aria-current="page">
                    {{ __('messages.traceability') }}
                </li>
            </ol>
        </nav>
        <div class="mx-auto" style="max-width: 800px">
            <h1 class="font-hanzel fs-32 text-center mb-3 text-red">
                {{ __('messages.product_traceability_center') }}
            </h1>
            <p class="text-center fs-16 mb-5 mx-auto">
                {{ __('messages.traceability_description') }}
            </p>
            <div class="traceability-check mx-auto">
                <form action="{{ route('traceability.check') }}" method="GET" id="traceability-form">
                    {{-- Product Code Input --}}
                    <div class="mb-4">
                        <label for="trace_code" class="form-label fs-16 fw-500 mb-2">
                            {{ __('messages.enter_product_code') }} <span
                                class="text-red">{{ __('messages.required_field') }}</span>
                        </label>
                        <input type="text" class="form-control py-3" id="trace_code" name="trace_code"
                            value="{{ request('trace_code', request('qr', '')) }}"
                            placeholder="{{ __('messages.product_code_placeholder') }}">
                        <a href="#" class="text-red text-decoration-underline fs-14 mt-1 d-inline-block">
                            {{ __('messages.how_to_find_product_code') }}
                        </a>
                    </div>

                    {{-- Captcha Section --}}
                    <div class="mb-5">
                        {{-- Captcha Input --}}
                        <label for="captcha" class="form-label fs-16 fw-500 mb-2">
                            {{ __('messages.enter_captcha') }} <span
                                class="text-red">{{ __('messages.required_field') }}</span>
                        </label>
                        <div class="d-flex align-items-center align-items-stretch gap-2 wrap-captcha">
                            <input type="text" class="form-control py-3" id="captcha" name="captcha"
                                placeholder="{{ __('messages.captcha_placeholder') }}">
                            <div class="d-flex">
                                <img id="captchaImage" src="{{ $captchaImage ?? '' }}" alt="Captcha"
                                    class="w-100 rounded">
                            </div>
                        </div>
                        <a href="javascript:void(0);" id="refreshCaptcha" data-url="{{ route('traceability.refresh-captcha') }}"
                            class="text-red text-decoration-underline fs-14 mt-1 d-inline-block">
                            {{ __('messages.get_new_code') }}
                        </a>
                    </div>

                    {{-- Submit Button --}}
                    <div class="text-center">
                        <button type="submit" class="btn btn-lg fw-600 text-white bg-red px-5 py-3 rounded-1">
                            {{ __('messages.check') }}
                        </button>
                    </div>
                </form>
            </div>
            {{-- QR Scan Section --}}
            <div class="mb-5 hidden-again">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div
                        class="warranty-option-number fw-600 text-white d-flex align-items-center justify-content-center">
                        2
                    </div>
                    <div class="fw-600 text-red">Quét mã QR</div>
                </div>

                <div class="text-center">
                    {{-- Vùng render camera --}}
                    <div id="trace-qr-reader" class="d-none w-100"></div>
                    <img id="trace-qr-scan-placeholder" src="{{ asset('dealer/imgs/qr-scan.jpg') }}"
                        class="img-fluid" width="200" alt="QR Scan">
                </div>

                <div class="row">
                    <div class="d-flex justify-content-center">
                        <div class="col-md-6">
                            <div class="mt-3 text-center fs-14">
                                Quét mã QR trên sản phẩm để tự động điền mã truy xuất.
                            </div>
                            <button type="button" id="btn-trace-open-camera"
                                class="btn w-100 btn-outline-danger text-red rounded-pill px-2 py-2 fw-500 mt-3">
                                <i class="bi bi-camera me-2 fs-20"></i>Mở camera quét mã
                            </button>
                            <button type="button" id="btn-trace-stop-camera"
                                class="btn w-100 btn-danger text-white rounded-pill px-2 py-2 fw-500 mt-3 d-none">
                                <i class="bi bi-x-circle me-2 fs-20"></i>Đóng camera
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="{{ asset('langding/js/traceability/index.js') }}"></script>
@endpush