@extends('langding.index')
@section('title', __('messages.warranty_request'))
@section('langding_content')
<div class="page-warranty">
    <div class="container-fluid py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="fs-15 text-black">{{ __('messages.home') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="#" class="fs-15 text-black">{{ __('messages.support') }}</a>
                </li>
                <li class="breadcrumb-item active fs-15 text-black fw-600" aria-current="page">
                    {{ __('messages.warranty_request') }}
                </li>
            </ol>
        </nav>

        <h1 class="font-hanzel fs-32 text-center mb-4 text-red" style="color: #DC1A21 !important;">
            {{ __('messages.warranty_request_title') }}
        </h1>

        <p class="text-center fs-16 mb-5" style="max-width: 900px; margin: 0 auto; color: #333; line-height: 1.6;">
            {{ __('messages.warranty_description') }}
        </p>

        <div class="warranty-form" style="max-width: 900px; margin: 0 auto;">
            <form action="{{ route('warranty.store') }}" method="POST" id="warrantyForm">
                @csrf
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="form-section">
                            <label class="form-label fs-16 fw-500 mb-3" style="color: #333;">
                                {{ __('messages.applicant_info') }} <span class="text-red">{{ __('messages.required_field') }}</span>
                            </label>

                            <div class="mb-3">
                                <input
                                    type="text"
                                    class="form-control @error('applicant_name') is-invalid @enderror"
                                    id="applicant_name"
                                    name="applicant_name"
                                    placeholder="{{ __('messages.full_name') }}"
                                    value="{{ old('applicant_name') }}"
                                    required
                                    style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                                @error('applicant_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                                    {{ __('messages.email_address') }} <span class="text-red">{{ __('messages.required_field') }}</span>
                                </label>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    placeholder="{{ __('messages.email_address') }}"
                                    value="{{ old('email') }}"
                                    required
                                    style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="form-section">
                            <label class="form-label fs-16 fw-500 mb-3" style="color: #333;">
                                {{ __('messages.contact_info') }} <span class="text-red">{{ __('messages.required_field') }}</span>
                            </label>

                            <div class="mb-3">
                                <input
                                    type="tel"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    id="phone"
                                    name="phone"
                                    placeholder="{{ __('messages.phone_number') }}"
                                    value="{{ old('phone') }}"
                                    required
                                    style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Số hóa đơn --}}
                            <div class="mb-3">
                                <label for="invoice_number" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                                    {{ __('messages.invoice_number') }}
                                </label>
                                <input
                                    type="text"
                                    class="form-control @error('invoice_number') is-invalid @enderror"
                                    id="invoice_number"
                                    name="invoice_number"
                                    value="{{ request()->query('order_no') ?? old('invoice_number') }}"
                                    placeholder="{{ __('messages.invoice_info_placeholder') }}"
                                    style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                                @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="qr_code" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                        {{ __('messages.qr_code') }}
                    </label>
                    <input
                        type="text"
                        class="form-control @error('qr_code') is-invalid @enderror"
                        id="qr_code"
                        name="qr_code"
                        placeholder="{{ __('messages.qr_code_placeholder') }}"
                        value="{{ request()->query('qr') ?? old('qr_code') }}"
                        style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                    @error('qr_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="warranty_content" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                        {{ __('messages.warranty_content') }} <span class="text-red">{{ __('messages.required_field') }}</span>
                    </label>
                    <textarea
                        class="form-control @error('warranty_content') is-invalid @enderror"
                        id="warranty_content"
                        name="warranty_content"
                        rows="6"
                        placeholder="{{ __('messages.warranty_content_placeholder') }}"
                        required
                        style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px; resize: vertical;">{{ old('warranty_content') }}</textarea>
                    @error('warranty_content')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Captcha Section --}}
                <div class="mb-5">
                    {{-- Captcha Input --}}
                    <label for="captcha" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                        {{ __('messages.enter_captcha') }} <span class="text-red" style="color: #DC1A21;">{{ __('messages.required_mark') }}</span>
                    </label>
                    <div class="d-flex align-items-center align-items-stretch gap-2 wrap-captcha">
                        <input type="text" class="form-control py-3 @error('captcha') is-invalid @enderror" id="captcha" name="captcha"
                            placeholder="{{ __('messages.captcha_placeholder') }}"
                            style="background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 4px;">
                        <div class="d-flex">
                            <img id="captchaImage" src="{{ $captchaImage ?? '' }}" alt="Captcha"
                                class="w-100 rounded" style="max-width: 150px; border: 1px solid #ddd;">
                        </div>
                    </div>
                    @error('captcha')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <a href="javascript:void(0);" id="refreshCaptcha" data-url="{{ route('warranty.refresh-captcha') }}"
                        class="text-red text-decoration-underline fs-14 mt-2 d-inline-block" style="color: #DC1A21;">
                        {{ __('messages.get_new_code') }}
                    </a>
                </div>

                <div class="text-start">
                    <button
                        type="submit"
                        class="btn btn-lg fw-700 text-white"
                        style="background-color: #555; border: none; padding: 15px 50px; border-radius: 4px; font-size: 18px; min-width: 200px;">
                        {{ __('messages.send_now') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .page-warranty {
        padding-top: 47px;
        padding-bottom: 150px;
    }

    .page-warranty .text-red {
        color: #DC1A21 !important;
    }

    .page-warranty .form-control:focus,
    .page-warranty textarea.form-control:focus {
        border-color: #DC1A21;
        box-shadow: 0 0 0 0.2rem rgba(220, 26, 33, 0.25);
        background-color: #fff;
        outline: none;
    }

    .page-warranty .form-control::placeholder,
    .page-warranty textarea.form-control::placeholder {
        color: #999;
    }

    .page-warranty .btn:hover {
        background-color: #444 !important;
        transition: background-color 0.3s ease;
    }

    .page-warranty .form-section {
        height: 100%;
    }

    @media (max-width: 768px) {
        .page-warranty h1 {
            font-size: 24px !important;
        }

        .page-warranty .warranty-form {
            padding: 0 15px;
        }

        .page-warranty .row.g-4 {
            margin-bottom: 0;
        }

        .page-warranty .col-md-6 {
            margin-bottom: 1rem;
        }
    }
</style>
@endsection
@push('scripts')
    <script src="{{ asset('langding/js/traceability/index.js') }}"></script>
@endpush
