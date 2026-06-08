<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" class="text-secondary fw-500 text-nowrap ps-0">Tên sản phẩm
                </th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Danh mục</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Đơn giá</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Số lượng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Thành tiền</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-end pe-0">Tác
                    vụ
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cart?->items ?? [] as $item)
                <tr data-item-id="{{ $item->id }}">
                    <td class="ps-0">
                        <a href="{{ route('dealer.cart.product-detail', ['id' => $item->product->id]) }}" target="_blank">
                            <div class="product-item d-flex align-items-center gap-2">
                                <img src="{{ $item->product->image_urls[0] ?? '' }}" alt=""
                                    onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                                <div class="order-detail-customer-name fs-14 fw-600 text-nowrap">
                                    {{ $item->product?->translations->firstWhere('language', app()->getLocale())?->name ?: 'Chưa có tên sản phẩm' }}
                                    <div><span class="text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">{{ $item->product->sku }}</span></div>
                                </div>
                            </div>
                        </a>
                    </td>
                    <td class="text-nowrap">
                        <span class="product-cat text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">
                            {{ $item->product->category?->translations->firstWhere('language', app()->getLocale())?->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-nowrap text-end">
                        <span
                            class="product-price fs-14 fw-600">{{ number_format($item->unit_price ?? 0, 0, ',', '.') }}đ</span>
                    </td>
                    <td>
                        <div class="quantity d-flex align-items-center">
                            <div class="qty-box qty-minus fs-20">-</div>
                            <input type="tel" class="form-control qty-input" value="{{ $item->quantity }}">
                            <div class="qty-box qty-plus fs-20">+</div>
                        </div>
                    </td>
                    <td class="text-nowrap text-end">
                        <span class="product-price fs-14 fw-600"><span
                                class="subtotal">{{ number_format($item->total_price ?? 0, 0, ',', '.') }}</span>đ</span>
                    </td>
                    <td class="pe-0">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <span role="button" class="btn-delete-item"><i class="bi bi-trash3 fs-20"></i></span>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="d-flex align-items-center justify-content-between mb-3 mt-3 pt-3 border-top">
    <span class="text-secondary">Tạm tính</span>
    <span class="fw-500"><span
            class="total_amount">{{ number_format($cart?->total_amount ?? 0, 0, ',', '.') }}</span>đ</span>
</div>
<div class="d-flex align-items-center justify-content-between mb-3">
    <span class="text-secondary">VAT</span>
    <span class="fw-500">{{ number_format($cart?->vat ?? 0, 0, ',', '.') }}đ</span>
</div>
<div class="d-flex align-items-center justify-content-between mb-3">
    <span class="text-secondary">Tổng thanh toán</span>
    <span class="fw-500"><span
            class="total_amount_val">{{ number_format(($cart?->total_amount ?? 0) + ($cart?->vat ?? 0), 0, ',', '.') }}</span>đ</span>
</div>
<div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
    <div class="d-block">
        <div class="fw-500 d-flex align-items-center gap-2 mt-2">
            <i class="bi bi-check2"></i>
            <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
            <span class="fs-12 fw-600">{{ $cart->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('dealer.cart-checkout') }}" class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500">BƯỚC
            TIẾP THEO<i class="bi bi-arrow-right ms-2"></i></a>
    </div>
</div>
