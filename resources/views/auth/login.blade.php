@extends('auth.index')

@section('title', __('auth.login_title'))

@section('content')
    <div class="auth-header">
        <a href="{{ route('home') }}"><img src="{{ asset('langding/imgs/logo3.svg') }}" alt="Casumina Logo"
                class="auth-logo"></a>
        <h1 class="auth-title font-hanzel fw-normal fs-38">{{ __('auth.login_title') }}</h1>
        <p class="auth-subtitle mb-0 fs-16">{{ __('auth.login_subtitle') }}</p>
    </div>

    <div class="auth-body">
        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf
            <!-- Username -->
            <div class="mb-4">
                <input type="text" class="rounded form-control @error('username') is-invalid @enderror" id="username"
                    name="username" value="{{ old('username') }}" placeholder="{{ __('auth.login_placeholder_username') }}">
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <!-- Password -->
            <div class="mb-4">
                <div class="input-group overflow-hidden">
                    <input type="password" class="rounded form-control @error('password') is-invalid @enderror" id="password"
                        name="password" placeholder="{{ __('auth.login_placeholder_password') }}">
                    <button type="button" class="toggle-password" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            @if (session('error'))
                <div class="invalid-feedback d-block mb-3 text-center">{{ session('error') }}</div>
            @endif
            <!-- Remember Me & Forgot Password -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        {{ __('auth.login_remember_me') }}
                    </label>
                </div>
                <a href="{{ route('forgot-password') }}" class="text-decoration-none"
                    style="color: #2ccc81; font-size: 14px;">
                    {{ __('auth.login_forgot_password') }}
                </a>
            </div>
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100">
                {{ __('auth.login_button') }}
            </button>
            <!--
            <div class="divider">
                <span>hoặc</span>
            </div>
             -->
            <!-- <div class="social-login">
                    <button type="button" class="social-btn">
                        <i class="bi bi-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn">
                        <i class="bi bi-facebook"></i>
                        Facebook
                    </button>
                </div> -->
        </form>
        <div class="mb-0 mt-3 text-center">
            <span>{{ __('auth.login_no_account') }}</span>
            <a class="text-decoration-none" style="color: #2ccc81;" href="{{ route('register') }}">{{ __('auth.login_signup_link') }}</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $('.toggle-password').on('click', function() {
            const input = $(this).siblings('input');
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });
</script>
@endpush
