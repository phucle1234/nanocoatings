@extends('auth.index')

@section('title', __('auth.register_title'))

@section('content')
<div class="auth-header">
    <a href="{{ route('home') }}"><img src="{{ asset('langding/imgs/logo3.svg') }}" alt="Casumina Logo"
            class="auth-logo"></a>
    <h1 class="auth-title font-hanzel fw-normal fs-38">{{ __('auth.register_title') }}</h1>
    <p class="auth-subtitle mb-0 fs-16">{{ __('auth.register_subtitle') }}</p>
</div>

<div class="auth-body">
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <!-- Name -->
        <div class="mb-4">
            <input type="text" class="rounded form-control @error('name') is-invalid @enderror" id="name"
                name="name" value="{{ old('name') }}" placeholder="{{ __('auth.register_placeholder_name') }}">
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="mb-4">
            <input type="email" class="rounded form-control @error('email') is-invalid @enderror" id="email"
                name="email" value="{{ old('email') }}" placeholder="{{ __('auth.register_placeholder_email') }}">
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Phone -->
        <div class="mb-4">
            <input type="tel" class="rounded form-control @error('phone') is-invalid @enderror" id="phone"
                name="phone" value="{{ old('phone') }}" placeholder="{{ __('auth.register_placeholder_phone') }}">
            @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Address -->
        <div class="mb-4">
            <input type="text" class="rounded form-control @error('address') is-invalid @enderror" id="address"
                name="address" value="{{ old('address') }}" placeholder="{{ __('auth.register_placeholder_address') }}">
            @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Gender -->
        <div class="mb-4">
            <select name="gender" id="gender" class="rounded form-control @error('gender') is-invalid @enderror">
                <option value="0">{{ __('messages.choose_gender') }}</option>
                <option value="1">{{ __('messages.male') }}</option>
                <option value="2">{{ __('messages.female') }}</option>
                <option value="3">{{ __('messages.other') }}</option>
            </select>
            @error('gender')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Terms -->
        <div class="mb-4">
            <div class="form-check">
                <input required class="form-check-input" type="checkbox" name="terms" id="terms" value="1">

                <label class="form-check-label" for="terms" style="font-size: 14px;">
                    {{ __('auth.register_terms_agree') }}
                    <a href="#" style="color: #2ccc81; font-size: 14px;">{{ __('auth.register_terms_service') }}</a>
                    và
                    <a href="#" style="color: #2ccc81; font-size: 14px;">{{ __('auth.register_privacy_policy') }}</a>
                </label>

                <!-- Thời gian người dùng tick đồng ý điều khoản -->
                <input type="hidden" name="terms_accepted_at_utc" id="terms_accepted_at_utc"
                    value="{{ old('terms_accepted_at_utc') }}">

                <input type="hidden" name="terms_accepted_at_local" id="terms_accepted_at_local"
                    value="{{ old('terms_accepted_at_local') }}">

                <input type="hidden" name="terms_accepted_at_timestamp" id="terms_accepted_at_timestamp"
                    value="{{ old('terms_accepted_at_timestamp') }}">

                <input type="hidden" name="terms_accepted_timezone" id="terms_accepted_timezone"
                    value="{{ old('terms_accepted_timezone') }}">
            </div>

            @error('terms')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror

            @error('terms_accepted_at_utc')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100">
            {{ __('auth.register_button') }}
        </button>
    </form>
    <div class="mb-0 mt-3 text-center">
        <span>{{ __('auth.register_have_account') }}</span>
        <a class="text-decoration-none" style="color: #2ccc81; font-size: 14px;" href="{{ route('login') }}">{{ __('auth.register_login_link') }}</a>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const registerForm = document.querySelector(".auth-form");
        const termsCheckbox = document.getElementById("terms");

        const termsAcceptedAtUtcInput = document.getElementById("terms_accepted_at_utc");
        const termsAcceptedAtLocalInput = document.getElementById("terms_accepted_at_local");
        const termsAcceptedAtTimestampInput = document.getElementById("terms_accepted_at_timestamp");
        const termsAcceptedTimezoneInput = document.getElementById("terms_accepted_timezone");

        function pad(number) {
            return String(number).padStart(2, "0");
        }

        function getLocalIsoString(date) {
            const timezoneOffset = -date.getTimezoneOffset();
            const sign = timezoneOffset >= 0 ? "+" : "-";
            const absOffset = Math.abs(timezoneOffset);
            const offsetHours = pad(Math.floor(absOffset / 60));
            const offsetMinutes = pad(absOffset % 60);

            return (
                date.getFullYear() +
                "-" + pad(date.getMonth() + 1) +
                "-" + pad(date.getDate()) +
                "T" + pad(date.getHours()) +
                ":" + pad(date.getMinutes()) +
                ":" + pad(date.getSeconds()) +
                sign + offsetHours +
                ":" + offsetMinutes
            );
        }

        function getUserTimezone() {
            try {
                return Intl.DateTimeFormat().resolvedOptions().timeZone || "";
            } catch (e) {
                return "";
            }
        }

        function setTermsAcceptedTime() {
            const now = new Date();

            termsAcceptedAtUtcInput.value = now.toISOString();
            termsAcceptedAtLocalInput.value = getLocalIsoString(now);
            termsAcceptedAtTimestampInput.value = String(now.getTime());
            termsAcceptedTimezoneInput.value = getUserTimezone();
        }

        function clearTermsAcceptedTime() {
            termsAcceptedAtUtcInput.value = "";
            termsAcceptedAtLocalInput.value = "";
            termsAcceptedAtTimestampInput.value = "";
            termsAcceptedTimezoneInput.value = "";
        }

        if (termsCheckbox) {
            termsCheckbox.addEventListener("change", function() {
                if (termsCheckbox.checked) {
                    setTermsAcceptedTime();
                } else {
                    clearTermsAcceptedTime();
                }
            });
        }

        if (registerForm) {
            registerForm.addEventListener("submit", function() {
                // Trường hợp trình duyệt tự restore checkbox checked nhưng hidden input chưa có giá trị
                if (termsCheckbox && termsCheckbox.checked && !termsAcceptedAtUtcInput.value) {
                    setTermsAcceptedTime();
                }
            });
        }
    });
</script>
@endsection