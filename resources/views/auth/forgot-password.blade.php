@extends('auth.index')

@section('title', __('auth.forgot_password_title'))

@section('content')
<div class="auth-header">
    <a href="{{ route('home') }}"><img src="{{ asset('langding/imgs/logo3.svg') }}" alt="Casumina Logo"
            class="auth-logo"></a>
    <h1 class="auth-title font-hanzel fw-normal fs-38">{{ __('auth.forgot_password_title') }}</h1>
    <p class="auth-subtitle fs-16">{{ __('auth.forgot_password_subtitle') }}</p>
</div>

<div class="auth-body">
    <!-- Session Status -->
    @if (session('status'))
    <div class="alert alert-success" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('forgot-password') }}" class="auth-form">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <div class="position-relative">
                <input type="text" inputmode="email" autocomplete="email"
                    class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                    value="{{ old('email') }}" placeholder="{{ __('auth.forgot_password_placeholder_email') }}" required autofocus>
                <div class="input-icon">
                    <i class="bi bi-envelope"></i>
                </div>
            </div>
            @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted mt-2 d-block">
                <i class="bi bi-info-circle me-1"></i>
                {{ __('auth.forgot_password_info') }}
            </small>
        </div>
        @if (session('error'))
        <div class="invalid-feedback d-block mb-3 text-center">{{ session('error') }}</div>
        @endif

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-send me-2"></i>
            {{ __('auth.forgot_password_button') }}
        </button>

        <!-- Back to Login -->
        {{-- <div class="text-center">
                <a href="{{ route('login') }}" class="text-decoration-none d-inline-flex align-items-center"
        style="color: var(--color-red); font-weight: 600;">
        <i class="bi bi-arrow-left me-2"></i>
        Quay lại đăng nhập
        </a>
</div> --}}
</form>
<div class="mb-0 mt-3 text-center">
    <span>{{ __('auth.forgot_password_no_account') }}</span>
    <a class="text-red" href="{{ route('register') }}">{{ __('auth.forgot_password_signup_link') }}</a>
</div>
</div>

@endsection