@extends('customer.index')
@section('title', __('messages.warranty_management_title'))
@section('customer_content')
<div class="page-warranty container pt-0">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="fs-15 text-black">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active fs-15 text-black" aria-current="page">{{ __('messages.warranty_management_title') }}</li>
        </ol>
    </nav>

    <h1 class="font-hanzel fs-42 fw-400 mt-1">{{ __('messages.warranty_management_title') }} của <span class="text-red">{{ $user->name }}</span></h1>
    <p>{{ __('messages.warranty_page_intro') }}</p>

    <div class="mt-1 row g-4">
        <div class="col-lg-3 col-md-4">
            @include('customer.components.sidebar')
        </div>

        <div class="col-lg-9 col-md-8">
            <ul class="status-items list-unstyled d-flex align-items-center mb-3">
                <li class="status-item px-4 py-2 fs-16 fw-500 {{ !$status ? 'border-bottom border-2 border-danger' : '' }}">
                    <a href="{{ route('customer.warranty-list', request()->filled('keyword') ? ['keyword' => request('keyword')] : []) }}"
                        class="{{ !$status ? 'text-red' : 'text-body-tertiary opacity-50' }}">
                        {{ __('messages.all') }} ({{ $countAll }})
                    </a>
                </li>
                <li class="status-item px-4 py-2 fs-16 fw-500 {{ $status === 'N' ? 'border-bottom border-2 border-danger' : '' }}">
                    <a href="{{ route('customer.warranty-list', array_filter(['status' => 'N', 'keyword' => request('keyword')])) }}"
                        class="{{ $status === 'N' ? 'text-red' : 'text-body-tertiary opacity-50' }}">
                        {{ __('messages.processing') }} ({{ $countNew }})
                    </a>
                </li>
                <li class="status-item px-4 py-2 fs-16 fw-500 {{ $status === 'Y' ? 'border-bottom border-2 border-danger' : '' }}">
                    <a href="{{ route('customer.warranty-list', array_filter(['status' => 'Y', 'keyword' => request('keyword')])) }}"
                        class="{{ $status === 'Y' ? 'text-red' : 'text-body-tertiary opacity-50' }}">
                        {{ __('messages.completed') }} ({{ $countSuccess }})
                    </a>
                </li>
                <li class="status-item px-4 py-2 fs-16 fw-500 {{ $status === 'C' ? 'border-bottom border-2 border-danger' : '' }}">
                    <a href="{{ route('customer.warranty-list', array_filter(['status' => 'C', 'keyword' => request('keyword')])) }}"
                        class="{{ $status === 'C' ? 'text-red' : 'text-body-tertiary opacity-50' }}">
                        {{ __('messages.cancelled') }} ({{ $countCancel }})
                    </a>
                </li>
            </ul>

            <div class="input-search mb-3">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="bi bi-search"></i>
                </button>
                <input type="text" name="keyword" value="{{ request('keyword') }}" id="customer-warranty-search-keyword"
                    class="form-control" placeholder="{{ __('messages.search_warranty') }}" autocomplete="off">
                <span id="customer-warranty-clear-search" role="button" class="{{ request('keyword') ? '' : 'd-none' }}">
                    <i class="bi bi-x-lg"></i>
                </span>
            </div>

            <div id="warranty-list-container"
                data-url="{{ route('customer.warranty-list', array_filter(['status' => $status])) }}"
                data-status="{{ $status }}">
                @include('customer.layout.partials._warranty-list', ['warranties' => $warranties])
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('customer/js/warranty.js') }}"></script>
@endpush