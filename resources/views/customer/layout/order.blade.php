@extends('customer.index')
@section('title', __('messages.order_management_title'))
@section('customer_content')
<div class="page-order container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="fs-15 text-black">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active fs-15 text-black" aria-current="page">{{ __('messages.order_management_title') }}</li>
        </ol>
    </nav>
    <h1 class="font-hanzel fs-42 fw-400 mt-1">{{ __('messages.order_management_title') }} của <span class="text-red">{{ $user->name }}</span>
    </h1>
    <p>{{ __('messages.order_page_intro') }}</p>
    <div class="mt-1 row g-4">
        <div class="col-lg-3 col-md-4">
            @include('customer.components.sidebar')
        </div>
        <div class="col-lg-9 col-md-8">
            <ul class="status-items list-unstyled d-flex align-items-center mb-3">
                <li class="status-item px-4 py-2 fs-16 fw-500 {{ !isset($status) ? 'border-bottom border-2 border-danger' : '' }}">
                    <a href="{{ route('customer.order-list') }}"
                        class="{{ !isset($status) ? 'text-red' : 'text-body-tertiary opacity-50' }}">
                        {{ __('messages.all') }}
                    </a>
                </li>
                <li class="status-item px-4 py-2 fs-16 fw-500 {{ (isset($status) && $status === 0) ? 'border-bottom border-2 border-danger' : '' }}">
                    <a href="{{ route('customer.order-list-new') }}"
                        class="{{ (isset($status) && $status === 0) ? 'text-red' : 'text-body-tertiary opacity-50' }}">
                        {{ __('messages.new') }}
                    </a>
                </li>
                <li class="status-item px-4 py-2 fs-16 fw-500 {{ (isset($status) && $status === 1) ? 'border-bottom border-2 border-danger' : '' }}">
                    <a href="{{ route('customer.order-list-confirm') }}"
                        class="{{ (isset($status) && $status === 1) ? 'text-red' : 'text-body-tertiary opacity-50' }}">
                        {{ __('messages.confirmed') }}
                    </a>
                </li>
                <li class="status-item px-4 py-2 fs-16 fw-500 {{ (isset($status) && $status === -1) ? 'border-bottom border-2 border-danger' : '' }}">
                    <a href="{{ route('customer.order-list-cancel') }}"
                        class="{{ (isset($status) && $status === -1) ? 'text-red' : 'text-body-tertiary opacity-50' }}">
                        {{ __('messages.cancelled') }}
                    </a>
                </li>
            </ul>

            <div class="input-search mb-3">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="bi bi-search"></i>
                </button>
                <input type="text" name="keyword" value="{{ request('keyword') }}" id="customer-search-keyword"
                    class="form-control" placeholder="{{ __('messages.search_order') }}" autocomplete="off">
                <span id="customer-clear-search" role="button"
                    class="{{ request('keyword') ? '' : 'd-none' }}">
                    <i class="bi bi-x-lg"></i>
                </span>
            </div>
            <div id="order-history-table-container" data-url="{{ $listUrl ?? route('customer.order-list') }}">
                @include('customer.layout.partials._order-list', ['orders' => $orders])
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('customer/js/order.js') }}"></script>
@endpush