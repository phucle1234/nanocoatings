<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Tên sản phẩm</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Danh mục</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Đơn giá</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Số lượng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Thành tiền</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Tác vụ
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cart?->items ?? [] as $item)
                <tr data-item-id="{{ $item->id }}">
                    <td>
                        <a href="{{ route('dealer.loan-order.product-detail', ['id' => $item->product->id]) }}"
                            target="_blank">
                            <div
                                class="product-item d-flex align-items-center gap-2 {{ isset($item->error) && $item->error != '' ? 'opacity-50' : '' }}">
                                <img src="{{ $item->product->image_urls[0] ?? '' }}" alt=""
                                    onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                                <div class="order-detail-customer-name fs-14 fw-600">
                                    {{ $item->product?->translations->firstWhere('language', app()->getLocale())?->name ?: 'Chưa có tên sản phẩm' }}
                                    <div>
                                        <span
                                            class="text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">{{ $item->product->sku }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @if (isset($item->error))
                            <div class="error-message text-danger fs-12 mt-1 fw-500">{{ $item->error }}</div>
                        @endif
                    </td>
                    <td>
                        <span
                            class="product-cat text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1 text-nowrap">
                            {{ $item->product->category?->translations->firstWhere('language', app()->getLocale())?->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <span
                            class="product-price fs-14 fw-600">{{ number_format($item->unit_price ?? 0, 0, ',', '.') }}đ</span>
                    </td>
                    <td class="text-center">
                        <div class="quantity d-flex align-items-center">
                            <div class="qty-box qty-minus fs-20">-</div>
                            <input type="tel" class="form-control qty-input" value="{{ $item->quantity }}">
                            <div class="qty-box qty-plus fs-20">+</div>
                        </div>
                    </td>
                    <td>
                        <span class="product-price fs-14 fw-600"><span
                                class="subtotal">{{ number_format($item->total_price ?? 0, 0, ',', '.') }}</span>đ</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <span role="button" class="btn-delete-item"><i class="bi bi-trash3 fs-20"></i></span>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if ($type === 'index')
    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-3 pt-3 border-top">
        Giá trị đơn hàng
        <i class="bi bi-info-circle fs-18 text-secondary"></i>
    </h4>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-secondary">Tạm tính</span>
        <span class="fw-500">
            <span class="total_amount">{{ number_format($cart?->total_amount ?? 0, 0, ',', '.') }}đ</span>
        </span>
    </div>
    @if ($hasErrorItem > 0)
        <div class="text-danger bg-light fs-14 fw-500 text-center border border-danger rounded-1 p-2">Có
            {{ $hasErrorItem }} sản phẩm gặp vấn đề về giá, vui lòng kiểm tra lại, và xóa chúng trước khi tiếp tục.
        </div>
    @endif
    <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
        <div class="d-block">
            <div class="fw-500 d-flex align-items-center gap-2 mt-2">
                <i class="bi bi-check2"></i>
                <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                <span
                    class="fs-12 fw-600">{{ $cart->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if ($hasErrorItem > 0)
                <a href="#" class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500 disabled" tabindex="-1"
                    role="button" aria-disabled="true">BƯỚC TIẾP
                    THEO<i class="bi bi-arrow-right ms-2"></i></a>
            @else
                <a href="{{ route('dealer.loan-order-partner') }}"
                    class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500">BƯỚC TIẾP
                    THEO<i class="bi bi-arrow-right ms-2"></i></a>
            @endif
        </div>
    </div>
@endif
@if ($type === 'partner')
    <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center fw-600 mt-3">
        Giá trị đơn hàng
        <i class="bi bi-info-circle fs-18 text-secondary"></i>
    </h4>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-secondary">Tạm tính</span>
        <span class="fw-500">
            <span class="total_amount">{{ number_format($cart?->total_amount ?? 0, 0, ',', '.') }}</span>đ
        </span>
    </div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-secondary">VAT</span>
        <span class="fw-500">{{ number_format($cart?->vat ?? 0, 0, ',', '.') }}đ</span>
    </div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-secondary">Tổng thanh toán</span>
        <span class="fw-500">
            <span
                class="total_amount_val">{{ number_format(($cart?->total_amount ?? 0) + ($cart?->vat ?? 0), 0, ',', '.') }}</span>đ
        </span>
    </div>
@endif
