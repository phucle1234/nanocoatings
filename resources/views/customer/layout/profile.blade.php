@extends('customer.index')
@section('title', __('messages.profile_title'))
@section('customer_content')
<div class="page-profile container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="fs-15 text-black">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active fs-15 text-black" aria-current="page">{{ __('messages.account_info_sidebar') }}</li>
        </ol>
    </nav>
    <h1 class="font-hanzel fs-42 fw-400 mt-1">Hồ sơ của <span class="text-red">{{ $user->name }}</span>
    </h1>
    <p>{{ __('messages.profile_intro') }}</p>
    <div class="mt-1 row g-4">
        <div class="col-lg-3 col-md-4">
            @include('customer.components.sidebar')
        </div>
        <div class="col-lg-9 col-md-8">
            <ul class="nav nav-pills mb-1" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-5 fs-16 fw-500 text-body-tertiary {{ !$errors->has('password') && !$errors->has('password_confirmation') ? 'active' : '' }}"
                        id="pills-all-tab"
                        data-bs-toggle="pill" data-bs-target="#pills-all" type="button" role="tab"
                        aria-controls="pills-all" aria-selected="true">{{ __('messages.basic_info') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-5 fs-16 fw-500 text-body-tertiary {{ $errors->has('password') || $errors->has('password_confirmation') ? 'active' : '' }}"
                        id="pills-new-tab"
                        data-bs-toggle="pill" data-bs-target="#pills-new" type="button" role="tab"
                        aria-controls="pills-new" aria-selected="false">{{ __('messages.change_password') }}</button>
                </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade {{ !$errors->has('password') && !$errors->has('password_confirmation') ? 'show active' : '' }}" id="pills-all" role="tabpanel" aria-labelledby="pills-all-tab" tabindex="0">
                    <div class="row">
                        <div class="col-lg-12">
                            <form action="{{ route('customer.profile.update') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-500">{{ __('messages.email') }}</label>
                                    <p class="form-control-plaintext">{{ $user->email }}</p>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-500">{{ __('messages.name') }}</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-500">{{ __('messages.phone') }}</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                                    @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-500">{{ __('messages.province_city') }}</label>
                                    <select class="form-select @error('city_name') is-invalid @enderror" id="city_name" name="city_code">
                                        <option value="">{{ __('messages.select_province') }}</option>
                                        @foreach ($cities as $city)
                                        <option value="{{ $city->code }}" {{ old('city_code', $user->city_code) == $city->code ? 'selected' : '' }}>{{ $city->name_vi }}</option>
                                        @endforeach
                                    </select>
                                    @error('city_name')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label fw-500">{{ __('messages.address') }}</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $user->address) }}" required>
                                    @error('address')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="gender" class="form-label fw-500">{{ __('messages.gender') }}</label>
                                    <div class="">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="genderMale" value="1" {{ old('gender', $user->gender) == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="genderMale">{{ __('messages.male') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="2" {{ old('gender', $user->gender) == '2' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="genderFemale">{{ __('messages.female') }}</label>
                                        </div>
                                    </div>
                                    @error('gender')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>
                                <div class="profile-btn-link d-flex align-items-center justify-content-end gap-2 mt-5">
                                    <button type="submit" class="btn btn-dark text-white rounded-1 p-3 fw-500">{{ __('messages.update') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{ $errors->has('password') || $errors->has('password_confirmation') ? 'show active' : '' }}" id="pills-new" role="tabpanel" aria-labelledby="pills-new-tab" tabindex="0">
                    <div class="row">
                        <div class="col-lg-12">
                            <form action="{{ route('customer.password.update') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-500">{{ __('messages.new_password') }}</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="{{ __('messages.enter_new_password') }}" required>
                                    @error('password')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label fw-500">{{ __('messages.confirm_password') }}</label>
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" placeholder="{{ __('messages.re_enter_password') }}" required>
                                    @error('password_confirmation')<span class="text-danger small">{{ $message }}</span>@enderror
                                </div>
                                <div class="profile-btn-link d-flex align-items-center justify-content-end gap-2 mt-5">
                                    <button type="submit" class="btn btn-dark text-white rounded-1 p-3 fw-500">{{ __('messages.update_password') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection