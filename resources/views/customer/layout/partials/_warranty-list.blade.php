@forelse ($warranties as $warranty)
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
    @endphp

    <div class="warranty-item bg-light rounded-3 p-3 mt-3">
        <div class="d-flex align-items-center justify-content-between gap-3 border-bottom pb-2 mb-2">
            <div class="warranty-status d-flex align-items-center gap-2">
                <span class="badge bg-{{ $statusCfg['class'] }} fw-400 fs-12">{{ $statusCfg['label'] }}</span>
                <span class="text-red fw-500">{{ __('messages.warranty_code_label') }} {{ $warrantyCode }}</span>
            </div>
            <div class="warranty-date fs-14">{{ __('messages.warranty_request_date') }} {{ $requestDate ?? '---' }}</div>
        </div>

        <div class="warranty-product mb-2">
            @if($warranty->order && $warranty->order->items->isNotEmpty())
                @foreach($warranty->order->items as $item)
                    @php
                        $productName = $item->product?->translations?->firstWhere('language', app()->getLocale())?->name
                            ?? $item->product_name
                            ?? $item->product_sku
                            ?? 'Sản phẩm không tồn tại';

                        $productImage = $item->product?->image_urls[0] ?? asset('images/no-image.png');
                    @endphp

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $productImage }}" alt="{{ $productName }}"
                            class="warranty-product-img border rounded"
                            onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}'">
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
                @endforeach
            @else
                <div class="text-secondary fs-14 mb-2 text-center fw-bold">{{ __('messages.no_products_warranty') }}</div>
            @endif

            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <span class="fs-14 text-secondary mb-0">{{ __('messages.invoice_order_label') }}</span>
                <span class="fs-14 fw-500 mb-0">{{ $warranty->Invoice  }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <span class="fs-14 text-secondary mb-0">{{ __('messages.qr_code') }}</span>
                <span class="fs-14 fw-500 mb-0">{{ $warranty->QRcode ?: '---' }}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between gap-2">
                <span class="fs-14 text-secondary mb-0">{{ __('messages.contact') }}</span>
                <span class="fs-14 fw-500 mb-0">{{ $warranty->Phone ?: '---' }}</span>
            </div>
        </div>

        <div class="warranty-btn-link d-flex align-items-center justify-content-between mt-3">
            <a href="tel:{{ $warranty->Phone ?: config('app.hotline') }}"
                class="fw-600 fs-18 d-flex align-items-center gap-2">
                <i class="bi bi-telephone"></i>{{ __('messages.contact') }}
            </a>
            <div class="warranty-btn-group d-flex align-items-center gap-2">
                <a href="{{ route('customer.warranty-detail', ['id' => $warranty->id]) }}"
                    class="btn btn-dark text-white rounded px-5 py-2 fw-500">
                    {{ __('messages.warranty_detail') }}
                </a>
            </div>
        </div>
    </div>
@empty
    <div class="text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted"></i>
        <p class="text-muted fs-18 mt-3 mb-0">{{ __('messages.no_warranty_requests') }}</p>
    </div>
@endforelse

<div class="mt-4">
    {{ $warranties->withQueryString()->links() }}
</div>
