@extends('langding.index')

@section('title', __('messages.contact'))

@section('langding_content')

<div class="shopping-cart">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="fs-15 text-black">{{ __('messages.home') }}</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)" class="fs-15 text-black">{{ __('messages.contact') }}</a></li>
            </ol>
        </nav>
        <h1 class="fs-32 font-hanzel text-center mb-0 mt-4">{{ __('messages.contact') }}</h1>

        <div class="page-contact-form py-5">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <h2 class="fs-24 fw-700 text-center mb-4" style="color: #333;">{{ __('messages.send_message') }}</h2>

                        <div class="contact-form-wrapper" style="max-width: 800px; margin: 0 auto;">
                            <form action="{{ route('contact.store') }}" method="POST" id="contactForm">
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

                                {{-- Họ và Tên --}}
                                <div class="mb-3">
                                    <label for="name" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                                        {{ __('messages.name') }} <span class="text-red" style="color: #DC1A21;">{{ __('messages.required_mark') }}</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        id="name"
                                        name="name"
                                        placeholder="{{ __('messages.name_placeholder') }}"
                                        value="{{ old('name') }}"
                                        required
                                        style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Email và Số điện thoại --}}
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                                            {{ __('messages.email') }} <span class="text-red" style="color: #DC1A21;">{{ __('messages.required_mark') }}</span>
                                        </label>
                                        <input
                                            type="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            id="email"
                                            name="email"
                                            placeholder="{{ __('messages.email_placeholder') }}"
                                            value="{{ old('email') }}"
                                            required
                                            style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                                            {{ __('messages.phone') }} <span class="text-red" style="color: #DC1A21;">{{ __('messages.required_mark') }}</span>
                                        </label>
                                        <input
                                            type="tel"
                                            class="form-control @error('phone') is-invalid @enderror"
                                            id="phone"
                                            name="phone"
                                            placeholder="{{ __('messages.phone_placeholder') }}"
                                            value="{{ old('phone') }}"
                                            required
                                            style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                                        @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Tiêu đề --}}
                                <div class="mb-3">
                                    <label for="subject" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                                        {{ __('messages.subject') }} <span class="text-red" style="color: #DC1A21;">{{ __('messages.required_mark') }}</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control @error('subject') is-invalid @enderror"
                                        id="subject"
                                        name="subject"
                                        placeholder="{{ __('messages.subject_placeholder') }}"
                                        value="{{ old('subject') }}"
                                        required
                                        style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px;">
                                    @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Nội dung tin nhắn --}}
                                <div class="mb-4">
                                    <label for="message" class="form-label fs-16 fw-500 mb-2" style="color: #333;">
                                        {{ __('messages.message') }} <span class="text-red" style="color: #DC1A21;">{{ __('messages.required_mark') }}</span>
                                    </label>
                                    <textarea
                                        class="form-control @error('message') is-invalid @enderror"
                                        id="message"
                                        name="message"
                                        rows="6"
                                        placeholder="{{ __('messages.message_placeholder') }}"
                                        required
                                        style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 12px; border-radius: 4px; resize: vertical;">{{ old('message') }}</textarea>
                                    @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-5">
                                    {{-- Captcha Input --}}
                                    <label for="captcha" class="form-label fs-16 fw-500 mb-2">
                                        {{ __('messages.enter_captcha') }} <span
                                            class="text-red">{{ __('messages.required_field') }}</span>
                                    </label>
                                    <div class="d-flex align-items-center align-items-stretch gap-2 wrap-captcha">
                                        <input type="text" class="form-control py-3 @error('captcha') is-invalid @enderror" id="captcha" name="captcha"
                                            placeholder="{{ __('messages.captcha_placeholder') }}">
                                        <div class="d-flex">
                                            <img id="captchaImage" src="{{ $captchaImage ?? '' }}" alt="Captcha"
                                                class="w-100 rounded">
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);" id="refreshCaptcha" data-url="{{ route('contact.refresh-captcha') }}"
                                        class="text-red text-decoration-underline fs-14 mt-1 d-inline-block">
                                        {{ __('messages.get_new_code') }}
                                    </a>
                                </div>


                                {{-- Submit Button --}}
                                <div class="text-center">
                                    <button
                                        type="submit"
                                        class="btn btn-lg fw-700 text-white"
                                        style="background-color: #DC1A21; border: none; padding: 15px 50px; border-radius: 4px; font-size: 18px; min-width: 200px;">
                                        {{ __('messages.send_message_button') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-contact-form .form-control:focus,
    .page-contact-form textarea.form-control:focus {
        border-color: #DC1A21;
        box-shadow: 0 0 0 0.2rem rgba(220, 26, 33, 0.25);
        background-color: #fff;
        outline: none;
    }

    .page-contact-form .form-control::placeholder,
    .page-contact-form textarea.form-control::placeholder {
        color: #999;
    }

    .page-contact-form .btn:hover {
        background-color: #b81519 !important;
        transition: background-color 0.3s ease;
    }

    @media (max-width: 768px) {
        .page-contact-form h2 {
            font-size: 20px !important;
        }

        .page-contact-form .contact-form-wrapper {
            padding: 0 15px;
        }
    }
</style>

@endsection
@push('scripts')
    <script src="{{ asset('langding/js/traceability/index.js') }}"></script>
@endpush
