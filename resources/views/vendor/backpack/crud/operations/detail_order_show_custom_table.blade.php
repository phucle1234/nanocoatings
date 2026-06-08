@extends(backpack_view('blank'))

@php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;

// Helper format số & ngày
function fmt_number($v, $dec=0) { return is_numeric($v) ? number_format($v, $dec) : $v; }
function fmt_datetime($v) { try { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y H:i') : ''; } catch (\Throwable $e) { return $v; } }
@endphp

@section('after_styles')
<link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">
@endsection

@section('header')
<section class="container-fluid">
    <div class="order-header">
        <div class="order-header-content">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="order-title"></h1>
                    </h1>
                    <i class="la la-shopping-bag"></i> Chi tiết đơn hàng
                    </h1>
                    <div class="order-subtitle">
                        Mã đơn hàng: #{{ $entry->order_number ?? $entry->id }}
                    </div>
                    <div class="order-status-badges">
                        @php
                        $statusColors = [
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger'
                        ];
                        $statusLabels = [
                        'pending' => 'Chờ xử lý',
                        'processing' => 'Đang xử lý',
                        'shipped' => 'Đã giao hàng',
                        'delivered' => 'Đã nhận hàng',
                        'cancelled' => 'Đã hủy'
                        ];
                        $paymentColors = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        'cancelled' => 'secondary'
                        ];
                        $paymentLabels = [
                        'pending' => 'Chờ thanh toán',
                        'paid' => 'Đã thanh toán',
                        'failed' => 'Thanh toán thất bại',
                        'refunded' => 'Đã hoàn tiền',
                        'cancelled' => 'Đã hủy'
                        ];
                        @endphp
                        <span class="order-status-badge badge-{{ $statusColors[$entry->status] ?? 'secondary' }}">
                            {{ $statusLabels[$entry->status] ?? $entry->status }}
                        </span>
                        <span class="order-status-badge badge-{{ $paymentColors[$entry->payment_status] ?? 'secondary' }}">
                            {{ $paymentLabels[$entry->payment_status] ?? $entry->payment_status }}
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="order-amount">
                        {{ number_format($entry->total_amount ?? 0, 0, ',', '.') }} VNĐ
                    </div>
                    <small style="opacity: 0.8;">Tổng giá trị đơn hàng</small>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="container-fluid">

    {{-- ====== ORDER SUMMARY (GRID 3 CỘT) ====== --}}
    <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white">
            <strong><i class="la la-info-circle"></i> Tổng quan đơn hàng</strong>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                $summary = [
                ['label'=>'Mã đơn hàng', 'key'=>'order_number', 'icon'=>'la la-barcode', 'color'=>'primary'],
                ['label'=>'Khách hàng', 'key'=>'user.name', 'icon'=>'la la-user', 'color'=>'info'],
                ['label'=>'Email', 'key'=>'user.email', 'icon'=>'la la-envelope', 'color'=>'info'],
                ['label'=>'Trạng thái', 'key'=>'status', 'icon'=>'la la-flag', 'color'=>'warning'],
                ['label'=>'TT Thanh toán', 'key'=>'payment_status', 'icon'=>'la la-credit-card', 'color'=>'success'],
                ['label'=>'Tiền tệ', 'key'=>'currency', 'icon'=>'la la-coins', 'color'=>'secondary'],
                ['label'=>'Subtotal', 'key'=>'subtotal', 'type'=>'number', 'icon'=>'la la-calculator', 'color'=>'primary'],
                ['label'=>'Giảm giá', 'key'=>'discount_amount', 'type'=>'number', 'icon'=>'la la-tag', 'color'=>'danger'],
                ['label'=>'Thuế', 'key'=>'tax_amount', 'type'=>'number', 'icon'=>'la la-percent', 'color'=>'warning'],
                ['label'=>'Phí vận chuyển', 'key'=>'shipping_amount', 'type'=>'number', 'icon'=>'la la-truck', 'color'=>'info'],
                ['label'=>'Tổng thanh toán', 'key'=>'grand_total', 'type'=>'number', 'icon'=>'la la-money-bill-wave', 'color'=>'success'],
                ['label'=>'Ngày tạo', 'key'=>'created_at', 'type'=>'datetime', 'icon'=>'la la-calendar-plus', 'color'=>'secondary'],
                ['label'=>'Cập nhật', 'key'=>'updated_at', 'type'=>'datetime', 'icon'=>'la la-calendar-check', 'color'=>'secondary'],
                ];
                @endphp

                @foreach ($summary as $col)
                @php
                $raw = data_get($entry, $col['key']);
                $display = $raw;
                if (($col['type'] ?? 'text') === 'number') $display = fmt_number($raw, 0);
                if (($col['type'] ?? 'text') === 'datetime') $display = fmt_datetime($raw);
                @endphp

                @continue(is_null($raw)) {{-- ẩn ô nếu không có dữ liệu --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="p-3 border rounded h-100 shadow-sm summary-card border-left-{{ $col['color'] }}">
                        <div class="d-flex align-items-center mb-2">
                            <i class="{{ $col['icon'] }} text-{{ $col['color'] }} me-2"></i>
                            <div class="text-muted small">{{ $col['label'] }}</div>
                        </div>
                        <div class="fw-bold fs-6 text-{{ $col['color'] }}">{{ $display }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ====== ADDRESS (2 CỘT) ====== --}}
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-3 border-success address-card">
                <div class="address-header bg-success">
                    <strong><i class="la la-credit-card"></i> Địa chỉ thanh toán (Billing)</strong>
                </div>
                <div class="address-body">
                    @php $a = optional($entry->billingAddress); @endphp
                    @if($a->id)
                    <div class="address-line">
                        <i class="la la-user text-success"></i>
                        <strong>{{ $a->full_name ?? '' }}</strong>
                    </div>
                    <div class="address-line">
                        <i class="la la-phone text-success"></i>
                        {{ $a->phone ?? '' }}
                    </div>
                    <div class="address-line">
                        <i class="la la-map-marker text-success"></i>
                        {{ $a->address_line1 ?? '' }} {{ $a->address_line2 ?? '' }}
                    </div>
                    <div class="address-line">
                        <i class="la la-building text-success"></i>
                        {{ $a->ward ?? '' }}, {{ $a->district ?? '' }}, {{ $a->city ?? '' }}
                    </div>
                    <div class="address-line">
                        <i class="la la-globe text-success"></i>
                        {{ $a->province ?? '' }} {{ $a->postal_code ?? '' }}
                    </div>
                    <div class="address-line">
                        <i class="la la-flag text-success"></i>
                        {{ $a->country ?? '' }}
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="la la-exclamation-triangle"></i>
                        <h4>Không có dữ liệu</h4>
                        <p>Địa chỉ thanh toán chưa được cập nhật</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-3 border-info address-card">
                <div class="address-header bg-info">
                    <strong><i class="la la-truck"></i> Địa chỉ giao hàng (Shipping)</strong>
                </div>
                <div class="address-body">
                    @php $a = optional($entry->shippingAddress); @endphp
                    @if($a->id)
                    <div class="address-line">
                        <i class="la la-user text-info"></i>
                        <strong>{{ $a->full_name ?? '' }}</strong>
                    </div>
                    <div class="address-line">
                        <i class="la la-phone text-info"></i>
                        {{ $a->phone ?? '' }}
                    </div>
                    <div class="address-line">
                        <i class="la la-map-marker text-info"></i>
                        {{ $a->address_line1 ?? '' }} {{ $a->address_line2 ?? '' }}
                    </div>
                    <div class="address-line">
                        <i class="la la-building text-info"></i>
                        {{ $a->ward ?? '' }}, {{ $a->district ?? '' }}, {{ $a->city ?? '' }}
                    </div>
                    <div class="address-line">
                        <i class="la la-globe text-info"></i>
                        {{ $a->province ?? '' }} {{ $a->postal_code ?? '' }}
                    </div>
                    <div class="address-line">
                        <i class="la la-flag text-info"></i>
                        {{ $a->country ?? '' }}
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="la la-exclamation-triangle"></i>
                        <h4>Không có dữ liệu</h4>
                        <p>Địa chỉ giao hàng chưa được cập nhật</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ====== ITEMS (BẢNG) ====== --}}
    <div class="card mb-3 border-warning">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <strong><i class="la la-shopping-cart"></i> Sản phẩm trong đơn</strong>
            <span class="badge badge-dark badge-lg">{{ $entry->items->count() }} sản phẩm</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 items-table">
                    <thead class="table-warning">
                        <tr>
                            <th><i class="la la-hashtag"></i> #</th>
                            <th><i class="la la-barcode"></i> SKU</th>
                            <th><i class="la la-box"></i> Tên sản phẩm</th>
                            <th class="text-end"><i class="la la-sort-numeric-up"></i> SL</th>
                            <th class="text-end"><i class="la la-money-bill"></i> Giá</th>
                            <th class="text-end"><i class="la la-percent"></i> Thuế</th>
                            <th class="text-end"><i class="la la-tag"></i> Giảm</th>
                            <th class="text-end"><i class="la la-calculator"></i> Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entry->items as $i => $it)
                        <tr>
                            <td><span class="product-badge product-sku">{{ $i+1 }}</span></td>
                            <td><span class="product-badge product-sku">{{ $it->sku ?? '' }}</span></td>
                            <td><strong>{{ $it->product_name ?? optional($it->product)->name }}</strong></td>
                            <td class="text-end"><span class="product-badge product-quantity">{{ fmt_number($it->quantity ?? 0) }}</span></td>
                            <td class="text-end"><span class="product-badge product-price">{{ fmt_number($it->price ?? 0) }} VNĐ</span></td>
                            <td class="text-end text-warning">{{ fmt_number($it->tax_amount ?? 0) }} VNĐ</td>
                            <td class="text-end text-danger">{{ fmt_number($it->discount_amount ?? 0) }} VNĐ</td>
                            <td class="text-end"><span class="product-badge product-total">{{ fmt_number($it->total ?? ($it->quantity * $it->price)) }} VNĐ</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="la la-exclamation-triangle"></i>
                                    <h4>Không có sản phẩm</h4>
                                    <p>Đơn hàng này chưa có sản phẩm nào</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ====== PAYMENTS ====== --}}
    <div class="card mb-3 border-success">
        <div class="card-header bg-success text-white">
            <strong><i class="la la-credit-card"></i> Thanh toán</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 payment-table">
                    <thead class="table-success">
                        <tr>
                            <th><i class="la la-hashtag"></i> #</th>
                            <th><i class="la la-credit-card"></i> Phương thức</th>
                            <th><i class="la la-barcode"></i> Mã giao dịch</th>
                            <th><i class="la la-flag"></i> Trạng thái</th>
                            <th class="text-end"><i class="la la-money-bill-wave"></i> Số tiền</th>
                            <th><i class="la la-clock"></i> Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($entry->payments ?? []) as $i => $p)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $i+1 }}</span></td>
                            <td><strong>{{ optional($p->paymentMethod)->name ?? $p->method ?? '' }}</strong></td>
                            <td><code>{{ $p->transaction_id ?? '' }}</code></td>
                            <td>
                                @php
                                $paymentStatusColors = [
                                'pending' => 'warning',
                                'paid' => 'success',
                                'failed' => 'danger',
                                'refunded' => 'info',
                                'cancelled' => 'secondary'
                                ];
                                @endphp
                                <span class="badge badge-{{ $paymentStatusColors[$p->status] ?? 'secondary' }}">
                                    {{ $p->status ?? '' }}
                                </span>
                            </td>
                            <td class="text-end text-success"><strong>{{ fmt_number($p->amount ?? 0) }} VNĐ</strong></td>
                            <td><small class="text-muted">{{ fmt_datetime($p->created_at) }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="la la-exclamation-triangle fa-2x text-muted mb-2"></i>
                                <div class="text-muted">Chưa có thanh toán</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ====== SHIPMENTS ====== --}}
    <div class="card mb-3 border-info">
        <div class="card-header bg-info text-white">
            <strong><i class="la la-truck"></i> Vận chuyển</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 shipping-table">
                    <thead class="table-info">
                        <tr>
                            <th><i class="la la-hashtag"></i> #</th>
                            <th><i class="la la-truck"></i> ĐVVC / Phương thức</th>
                            <th><i class="la la-barcode"></i> Mã vận đơn</th>
                            <th><i class="la la-flag"></i> Trạng thái</th>
                            <th><i class="la la-sticky-note"></i> Ghi chú</th>
                            <th><i class="la la-clock"></i> Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($entry->shipments ?? []) as $i => $s)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $i+1 }}</span></td>
                            <td><strong>{{ optional($entry->shippingMethod)->name ?? $s->carrier ?? '' }}</strong></td>
                            <td><code>{{ $s->tracking_number ?? '' }}</code></td>
                            <td>
                                @php
                                $shippingStatusColors = [
                                'pending' => 'warning',
                                'shipped' => 'primary',
                                'in_transit' => 'info',
                                'delivered' => 'success',
                                'returned' => 'danger'
                                ];
                                @endphp
                                <span class="badge badge-{{ $shippingStatusColors[$s->status] ?? 'secondary' }}">
                                    {{ $s->status ?? '' }}
                                </span>
                            </td>
                            <td><small>{{ $s->notes ?? '' }}</small></td>
                            <td><small class="text-muted">{{ fmt_datetime($s->created_at) }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="la la-exclamation-triangle fa-2x text-muted mb-2"></i>
                                <div class="text-muted">Chưa có vận chuyển</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ====== STATUS HISTORY ====== --}}
    <div class="card mb-4 border-secondary">
        <div class="card-header bg-secondary text-white">
            <strong><i class="la la-history"></i> Lịch sử trạng thái</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0 history-table">
                    <thead class="table-secondary">
                        <tr>
                            <th><i class="la la-hashtag"></i> #</th>
                            <th><i class="la la-flag"></i> Trạng thái</th>
                            <th><i class="la la-sticky-note"></i> Ghi chú</th>
                            <th><i class="la la-clock"></i> Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($entry->statusHistory ?? []) as $i => $h)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $i+1 }}</span></td>
                            <td>
                                @php
                                $statusColors = [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger'
                                ];
                                @endphp
                                <span class="badge badge-{{ $statusColors[$h->status] ?? 'secondary' }}">
                                    {{ $h->status ?? '' }}
                                </span>
                            </td>
                            <td><small>{{ $h->comment ?? '' }}</small></td>
                            <td><small class="text-muted">{{ fmt_datetime($h->created_at) }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <i class="la la-exclamation-triangle fa-2x text-muted mb-2"></i>
                                <div class="text-muted">Không có lịch sử</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- FOOTER ACTIONS --}}
    <div class="d-flex gap-2 mb-5 action-buttons">
        <div class="flex-grow-1">
            <a href="{{ url($crud->route) }}" class="btn btn-secondary btn-lg btn-action">
                <i class="la la-angle-left"></i> Quay lại danh sách
            </a>
        </div>
        @if ($crud->hasAccess('update'))
        <div class="flex-grow-1">
            <a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}" class="btn btn-primary btn-lg btn-action">
                <i class="la la-edit"></i> Sửa đơn hàng
            </a>
        </div>
        @endif
    </div>
</div>
@endsection