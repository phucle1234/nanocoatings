@extends('dealer.index')
@section('title', 'Dashboard')
@section('dealer_content')
    <div class="page-order container-xl container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Thành viên</a>
                </li>
                <li class="breadcrumb-item active fs-15 text-black" aria-current="page">Quản lý đơn hàng</li>
            </ol>
        </nav>
        <h1 class="font-hanzel fs-42 fw-400 mt-5">ĐẶT HÀNG - CHI TIẾT SẢN PHẨM</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div class="product-detail" data-product-id="{{ $product->id }}"
                    data-url="{{ route('dealer.cart.add-to-cart') }}">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                        <h1 class="font-hanzel fs-22 fw-400 mb-0">Chi tiết sản phẩm</h1>
                        <a href="{{ route('dealer.cart') }}" class="fw-500 d-flex align-items-center gap-2"><i
                                class="bi bi-arrow-left"></i>Quay
                            lại</a>
                    </div>
                    <div class="product-small d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $product->image_urls[0] ?? '' }}" alt="" class="product-img"
                            onerror="this.onerror=null; this.src='{{ asset('langding/imgs/no-img.jpg') }}'">
                        <div class="product-name fw-600">
                            {{ $product->translations->firstWhere('language', app()->getLocale())->name ?: 'Chưa có tên sản phẩm' }}
                            <div><span class="text-secondary bg-body-secondary fs-12 fw-500 rounded-1 px-2 py-1">{{ $product->sku }}</span></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Danh mục</span>
                        <span class="fw-500 cat-small text-secondary fs-12 px-2 py-1 rounded-1 bg-body-secondary">
                            {{ $product->category?->translations->firstWhere('language', app()->getLocale())?->name ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-secondary">Mô tả sản phẩm</span>
                        <span class="fw-500">
                            {{ $product->translations->firstWhere('language', app()->getLocale())->short_description ?: 'N/A' }}
                        </span>
                    </div>
                    <div class="order-detail-infomation pt-3 border-top">
                        <h4 class="fs-18 mb-3 d-flex gap-2 align-items-center">Thông số sản phẩm<i
                                class="bi bi-info-circle fs-18 text-secondary"></i></h4>
                        @foreach ($product->attributeValues as $attrValue)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="text-secondary">
                                    {{ $attrValue->attribute->translations->first()?->name ?: $attrValue->attribute->code }}
                                </span>
                                <span class="fw-500">
                                    {{ $attrValue->translations->first()?->value ?: $attrValue->value }}
                                </span>
                            </div>
                        @endforeach
                        <div class="d-flex align-items-center justify-content-between mb-3 mt-3 pt-3 border-top">
                            <span class="text-secondary">Đơn giá</span>
                            <span class="fw-500">
                                @if ($product->price > 0)
                                    {{ number_format($product->price ?? 0, 0, ',', '.') }}đ
                                @else
                                    Liên hệ
                                @endif
                            </span>
                        </div>
                        @if ($product->price > 0)
                            <div class="d-flex align-items-center justify-content-between mb-3 quantity-order">
                                <span class="text-secondary">Số lượng</span>

                                <div class="quantity d-flex align-items-center" data-price="{{ $product->price }}">
                                    <div class="qty-box qty-minus fs-20">-</div>
                                    <input type="tel" class="form-control qty-input" value="1">
                                    <div class="qty-box qty-plus fs-20">+</div>
                                </div>
                            </div>
                        @endif
                        @if ($product->price > 0)
                            <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
                                <span class="text-secondary">Thành tiền</span>
                                <span class="fw-500"><span
                                        class="subtotal-price">{{ number_format($product->price ?? 0, 0, ',', '.') }}</span>đ</span>
                            </div>
                        @endif
                    </div>
                    <div class="order-btn-link d-flex align-items-center justify-content-between gap-2 mt-5">
                        <div href="" class="fw-500 d-flex align-items-center gap-2">
                            <i class="bi bi-check2"></i>
                            <span class="fs-12 text-secondary">Dữ liệu đồng bộ lần cuối</span>
                            <span class="fs-12 fw-600">{{ $product->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                        </div>
                        @if ($product->price > 0)
                            <button class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500 btn-add-to-cart">THÊM VÀO
                                GIỎ HÀNG</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('dealer/js/cart/product-detail.js') }}"></script>
@endpush
