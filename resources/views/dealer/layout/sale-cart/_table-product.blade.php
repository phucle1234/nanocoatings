<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" class="text-secondary fw-500 text-nowrap ps-0">Tên sản phẩm</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Danh mục</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Đơn giá</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-center">Số lượng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-end pe-0">Tác vụ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($list as $product)
                <tr data-product-id="{{ $product->id }}">
                    <td class="ps-0">
                        <a href="{{ route('dealer.sale-cart.product-detail', ['id' => $product->id]) }}">
                            <div class="product-item d-flex align-items-center gap-2">
                                <img src="{{ $product->image_urls[0] ?? '' }}" alt="" class="product-img" onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                                <div class="order-detail-customer-name fs-14 fw-600 text-nowrap">
                                    {{ $product?->translations->firstWhere('language', app()->getLocale())?->name ?: 'Chưa có tên sản phẩm' }}
                                    <div><span class="text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">{{ $product->sku }}</span></div>
                                </div>
                            </div>
                        </a>
                    </td>
                    <td class="text-nowrap">
                        <span class="product-cat text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">
                            {{ $product->category?->translations->firstWhere('language', app()->getLocale())?->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-nowrap text-end">
                        @if ($product->price > 0)
                            <span class="product-price fs-14 fw-600">{{ number_format($product->price ?? 0, 0, ',', '.') }}đ</span>
                        @else
                            <span class="product-price fs-14 fw-600">Liên hệ</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($product->price > 0)
                            <div class="quantity d-flex align-items-center">
                                <div class="qty-box qty-minus fs-20">-</div>
                                <input type="tel" class="form-control qty-input" value="1">
                                <div class="qty-box qty-plus fs-20">+</div>
                            </div>
                        @else
                            <span class="text-secondary">-</span>
                        @endif
                    </td>
                    <td class="pe-0">
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <a href="{{ route('dealer.sale-cart.product-detail', ['id' => $product->id]) }}"><i class="bi bi-eye fs-22"></i></a>
                            @if ($product->price > 0)
                                <span role="button" class="btn-add-to-cart"><i class="bi bi-cart-plus fs-22"></i></span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <p class="fs-16 fw-500 text-secondary">Không tìm thấy sản phẩm phù hợp</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($list->hasPages())
    <div class="pt-4 border-top">
        {{ $list->links('pagination::bootstrap-5') }}
    </div>
@endif
