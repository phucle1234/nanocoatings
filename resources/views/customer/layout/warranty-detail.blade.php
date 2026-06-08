@extends('customer.index')
@section('title', __('messages.warranty_detail_title'))
@section('customer_content')
@php
$statusCode = strtoupper((string) ($warranty->Status ?? 'N'));
$statusMap = [
'N' => ['label' => __('messages.processing'), 'class' => 'secondary'],
'Y' => ['label' => __('messages.completed'), 'class' => 'success'],
'C' => ['label' => __('messages.cancelled'), 'class' => 'danger'],
];
$statusCfg = $statusMap[$statusCode] ?? ['label' => $statusCode, 'class' => 'secondary'];

$warrantyCode = ($warranty->order_number ?: ('BH-' . $warranty->id));
$requestDate = $warranty->Date
? \Carbon\Carbon::parse($warranty->Date)->format('d/m/Y H:i:s')
: optional($warranty->created_at)->format('d/m/Y H:i:s');

$completedDate = $statusCode === 'Y'
? (optional($warranty->updated_at)->format('d/m/Y H:i:s') ?: '---')
: __('messages.not_completed');

$orderItems = $warranty->order?->items ?? collect();
@endphp

<div class="page-warranty container pt-0">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}" class="fs-15 text-black">{{ __('messages.dashboard') }}</a></li>
            <li class="breadcrumb-item active fs-15 text-black" aria-current="page">{{ __('messages.warranty_detail') }}</li>
        </ol>
    </nav>
    <h1 class="font-hanzel fs-42 fw-400 mt-5">{{ __('messages.warranty_detail_of') }} <span class="text-red">{{ $user->name }}</span>
    </h1>
    <p>{{ __('messages.warranty_detail_intro') }}</p>
    <div class="mt-5 row g-4">
        <div class="col-lg-3 col-md-4">
            @include('customer.components.sidebar')
        </div>
        <div class="col-lg-9 col-md-8">
            <h1 class="font-hanzel fs-22 fw-400 mb-5">{{ __('messages.warranty_detail') }}: #{{ $warrantyCode }}</h1>
            <div class="d-flex align-items-center justify-content-between mb-5">
                <span>{{ __('messages.warranty_status') }}:</span>
                <span class="badge bg-{{ $statusCfg['class'] }} fs-12 fw-400">{{ $statusCfg['label'] }}</span>
            </div>
            <div class="warranty-detail-infomation mt-4">
                <h4 class="fs-18 mb-3 d-flex align-items-center gap-2 mb-4">
                    <span>{{ __('messages.applicant_info') }}</span>
                    <i class="bi bi-info-circle text-secondary"></i>
                </h4>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">{{ __('messages.applicant_name') }}:</span>
                    <span class="fw-500">{{ $warranty->Fullname ?: ($user->name ?: '---') }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">{{ __('messages.contact') }}:</span>
                    <span class="fw-500">{{ $warranty->Phone ?: '---' }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <span class="text-secondary">{{ __('messages.email') }}:</span>
                    <span class="fw-500">{{ $warranty->Email ?: '---' }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">{{ __('messages.warranty_code') }}:</span>
                    <span class="fw-500">{{ $warrantyCode }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">{{ __('messages.invoice_number') }}:</span>
                    <span class="fw-500">{{ $warranty->Invoice ?: ($warranty->order_number ?: '---') }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">{{ __('messages.date_received') }}:</span>
                    <span class="fw-500">{{ $requestDate ?: '---' }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">{{ __('messages.date_completed') }}:</span>
                    <span class="fw-500 {{ $statusCode === 'Y' ? 'text-success' : 'text-warning' }}">{{ $completedDate }}</span>
                </div>

                <h4 class="fs-18 mb-3 d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                    <span>{{ __('messages.acceptance_detail') }}</span>
                    <i class="bi bi-info-circle text-secondary"></i>
                </h4>
                <div class="d-flex align-items-center justify-content-between mt-4 mb-3">
                    <span class="text-secondary">{{ __('messages.warranty_content') }}</span>
                    <span class="fw-500 text-end">{{ $warranty->Content ?: '---' }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-secondary">{{ __('messages.qr_code') }}</span>
                    <span class="fw-500">{{ $warranty->QRcode ?: '---' }}</span>
                </div>

                <h4 class="fs-18 mb-3 d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                    <span>{{ __('messages.product_info') }}</span>
                    <i class="bi bi-info-circle text-secondary"></i>
                </h4>
                @forelse ($orderItems as $item)
                @php
                $productName = $item->product?->translations?->firstWhere('language', app()->getLocale())?->name
                ?? $item->product_name
                ?? $item->product_sku
                ?? 'Sản phẩm không tồn tại';
                $productImage = $item->product?->image_urls[0] ?? asset('images/no-image.png');
                @endphp
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $productImage }}" alt="{{ $productName }}" class="warranty-product-img border rounded"
                        onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}'" style="width: 100px; height: 100px;">
                    <div class="warranty-product-info w-100">
                        <h5 class="fs-18 mb-1 text-red d-flex align-items-center gap-2">
                            <span>{{ $productName }}</span>
                        </h5>
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="fs-14 text-secondary mb-0">{{ __('messages.quantity') }}: x{{ $item->quantity }}</span>
                            <span class="fs-14 text-secondary mb-0">{{ __('messages.unit_price') }}: {{ number_format($item->unit_price, 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-secondary mb-3 text-center fw-bold">{{ __('messages.no_products_warranty') }}</div>
                @endforelse

            </div>
            <div class="warranty-btn-link d-flex align-items-center justify-content-end gap-2 mt-5">
                <a href="{{ route('customer.warranty-list') }}" class="btn btn-dark text-white rounded-1 p-3 fw-500">{{ __('messages.back') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection