<div id="customer-order-list">
    @if(isset($orders) && $orders->count())
        @foreach($orders as $order)
            @php
                $pricedTotalAmount = collect($order->items ?? [])->reduce(function ($sum, $item) {
                    return ((float) ($item->unit_price ?? 0) > 0)
                        ? $sum + (float) ($item->total_price ?? 0)
                        : $sum;
                }, 0);
            @endphp
            <div class="order-item bg-light rounded-3 p-3 mt-3">
                <div class="d-flex align-items-center justify-content-between gap-3 border-bottom pb-2 mb-2">
                    <div class="order-status d-flex align-items-center gap-2">
                        @php
                            // Map status numeric -> label & class
                            $status = (int) ($order->status ?? 0);
                            $statusConfig = [
                                0 => ['label' => __('messages.new'), 'class' => 'secondary'],
                                1 => ['label' => __('messages.confirmed'), 'class' => 'success'],
                                -1 => ['label' => __('messages.cancelled'), 'class' => 'danger'],
                            ];
                            $cfg = $statusConfig[$status] ?? ['label' => $order->status, 'class' => 'secondary'];
                        @endphp
                        <span class="badge bg-{{ $cfg['class'] }} fw-400 fs-12">
                            {{ $cfg['label'] }}
                        </span>
                        <span>{{ __('messages.order_code') }}: {{ $order->order_number }}</span>
                    </div>
                    <div class="order-date fs-14">
                        {{ __('messages.order_date') }} {{ optional($order->created_at)->format('d/m/Y H:i:s') }}
                    </div>
                </div>
                <div class="order-product border-bottom pb-2 mb-2">
                    @foreach($order->items as $item)
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img src="{{ $item->product_image ?? asset('images/no-image.png') }}"
                                 alt="{{ $item->product_name ?? '' }}"
                                 class="order-product-img border rounded">
                            <div class="order-product-info w-100">
                                <div class="fs-14 fw-400 text-red mb-1">
                                    {{ $item->product?->brand ?? '' }}
                                </div>
                                <h5 class="fs-18 mb-1 text-red d-flex align-items-center gap-2">
                                    <span>{{ $item->product_name ?? $item->product_name_with_options }}</span>
                                </h5>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <span class="fs-14 text-secondary mb-0">
                                        {{ __('messages.product_code') }}: {{ $item->product?->sku ?? '' }}
                                    </span>

                                    <span class="fs-14 text-secondary mb-0">
                                        {{ __('messages.quantity') }}: x{{ $item->quantity }}
                                    </span>
                                    
                                    <span class="fs-14 text-secondary mb-0">
                                        {{ __('messages.price') }}:
                                        {{ (float) ($item->unit_price ?? 0) > 0 ? number_format($item->unit_price, 0, ',', '.') . 'đ' : __('messages.contact') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-end">
                    <b>{{ __('messages.total_amount') }} {{ $pricedTotalAmount > 0 ? number_format($pricedTotalAmount, 0, ',', '.') . 'đ' : __('messages.contact') }}</b>
                </div>
                <div class="order-btn-link d-flex align-items-center justify-content-between mt-3">
                    <a href="tel:{{ config('app.hotline') }}"
                       class="fw-600 fs-18 d-flex align-items-center gap-2">
                        <i class="bi bi-telephone"></i>{{ __('messages.contact') }}
                    </a>
                    <div class="order-btn-group d-flex align-items-center gap-2">
                        <a href="{{ route('shop') }}"
                           class="btn btn-danger text-white rounded px-5 py-2 fw-500">{{ __('messages.buy_more') }}</a>
                        <a href="{{ route('customer.order-detail', ['id' => $order->id]) }}"
                           class="btn btn-dark text-white rounded px-5 py-2 fw-500">{{ __('messages.order_detail') }}</a>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-4">
            {{ $orders->withQueryString()->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-bag-x fs-1 text-muted"></i>
            <p class="text-muted fs-18 mt-3">{{ __('messages.no_orders') }}</p>
            <a href="{{ route('shop') }}" class="btn btn-danger mt-2 px-5 py-2 fw-500">{{ __('messages.buy_now') }}</a>
        </div>
    @endif
</div>

