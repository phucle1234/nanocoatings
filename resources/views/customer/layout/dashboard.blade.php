@extends('customer.index')
@section('title', __('messages.dashboard'))
@section('customer_content')
<div class="page-dashboard container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="fs-15 text-black">{{ __('messages.dashboard') }}</a></li>

        </ol>
    </nav>

    <section class="dashboard-hero mt-4">
        <div class="dashboard-hero-content">
            <p class="dashboard-kicker mb-2">{{ __('messages.personal_dashboard') }}</p>
            <h1 class="font-hanzel fw-400 mb-2">{{ __('messages.hello_user') }} <span class="text-red">{{ $user->name }}</span></h1>
            <p class="mb-0">{{ __('messages.dashboard_intro') }}</p>
        </div>
        <div class="dashboard-hero-actions d-flex align-items-center gap-2 flex-wrap mt-3">
            <a href="{{ route('customer.order-list') }}" class="btn btn-danger text-white px-4">{{ __('messages.my_orders') }}</a>
            <a href="{{ route('customer.warranty-list') }}" class="btn btn-dark text-white px-4">{{ __('messages.warranty_tickets') }}</a>
        </div>
    </section>

    <section class="dashboard-section mt-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <h2 class="dashboard-title mb-0">{{ __('messages.quick_access') }}</h2>
        </div>
        <div class="row g-3">
            <div class="col-xl-3 col-sm-6">
                <a class="flash-link-item p-3 h-100 d-block" href="{{ route('customer.order-list') }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon d-flex align-items-center justify-content-center text-white">
                            <i class="bi bi-cart2 fs-18"></i>
                        </div>
                        <div>
                            <h3 class="fs-17 mb-1 fw-600">{{ __('messages.order_management') }}</h3>
                            <p class="mb-0 text-secondary fs-14">{{ __('messages.order_management_desc') }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6">
                <a class="flash-link-item p-3 h-100 d-block" href="{{ route('customer.warranty-list') }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon d-flex align-items-center justify-content-center text-white">
                            <i class="bi bi-patch-check fs-18"></i>
                        </div>
                        <div>
                            <h3 class="fs-17 mb-1 fw-600">{{ __('messages.warranty_management') }}</h3>
                            <p class="mb-0 text-secondary fs-14">{{ __('messages.warranty_management_desc') }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6">
                <a class="flash-link-item p-3 h-100 d-block" href="{{ route('customer.profile') }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon d-flex align-items-center justify-content-center text-white">
                            <i class="bi bi-person-gear fs-18"></i>
                        </div>
                        <div>
                            <h3 class="fs-17 mb-1 fw-600">{{ __('messages.account_info') }}</h3>
                            <p class="mb-0 text-secondary fs-14">{{ __('messages.account_info_desc') }}</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6">
                <a class="flash-link-item p-3 h-100 d-block" href="{{ route('logout') }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="icon d-flex align-items-center justify-content-center text-white">
                            <i class="bi bi-box-arrow-right fs-18"></i>
                        </div>
                        <div>
                            <h3 class="fs-17 mb-1 fw-600">{{ __('messages.logout') }}</h3>
                            <p class="mb-0 text-secondary fs-14">{{ __('messages.logout_desc') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section class="dashboard-section mt-4">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="box-action p-4 h-100">
                    <h3 class="dashboard-title mb-2">{{ __('messages.activity_management') }}</h3>
                    <p class="text-secondary mb-3">{{ __('messages.activity_desc') }}</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('customer.order-list') }}" class="btn btn-outline-dark d-flex align-items-center justify-content-between">
                            <span>{{ __('messages.order_list') }}</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('customer.warranty-list') }}" class="btn btn-outline-dark d-flex align-items-center justify-content-between">
                            <span>{{ __('messages.warranty_list') }}</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="{{ route('customer.profile') }}" class="btn btn-outline-dark d-flex align-items-center justify-content-between">
                            <span>{{ __('messages.edit_profile') }}</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="box-support p-4 h-100">
                    <h3 class="dashboard-title mb-2">{{ __('messages.quick_support') }}</h3>
                    <p class="text-secondary mb-3">{{ __('messages.support_desc') }}</p>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="support-item p-3 h-100">
                                <h4 class="fs-16 fw-600 mb-1">{{ __('messages.order_status') }}</h4>
                                <p class="mb-0 fs-14 text-secondary">{{ __('messages.order_status_desc') }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="support-item p-3 h-100">
                                <h4 class="fs-16 fw-600 mb-1">{{ __('messages.warranty_status') }}</h4>
                                <p class="mb-0 fs-14 text-secondary">{{ __('messages.warranty_status_desc') }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="support-item p-3 h-100">
                                <h4 class="fs-16 fw-600 mb-1">{{ __('messages.account_update') }}</h4>
                                <p class="mb-0 fs-14 text-secondary">{{ __('messages.account_update_desc') }}</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="support-item p-3 h-100">
                                <h4 class="fs-16 fw-600 mb-1">{{ __('messages.support_channel') }}</h4>
                                <p class="mb-0 fs-14 text-secondary">{{ __('messages.contact_hotline_support') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection