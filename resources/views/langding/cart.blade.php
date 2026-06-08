@extends('langding.index')
@section('title', __('messages.cart'))
@section('langding_content')

@php
$isSuccess = isset($orderData) && $orderData != null;
$cartItems = $cartData['data']['items'] ?? [];
$pricedSubtotal = 0;
$hasPricedItem = false;

foreach ($cartItems as $cartItem) {
$product = $cartItem['product'] ?? [];
$hasDisplayPrice = (bool)($product['has_display_price'] ?? false);
$price = floatval($product['price'] ?? 0);
$quantity = intval($cartItem['quantity'] ?? 1);

if ($hasDisplayPrice) {
$pricedSubtotal += ($price * max(1, $quantity));
$hasPricedItem = true;
}
}
@endphp

<div class="shopping-cart">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#" class="fs-15 text-black">{{ __('messages.home') }}</a></li>
                <li class="breadcrumb-item"><a href="#" class="fs-15 text-black">{{ __('messages.products') }}</a></li>
                <li class="breadcrumb-item"><a href="#" class="fs-15 text-black">{{ __('messages.cart') }}</a></li>
            </ol>
        </nav>
        <h1 class="fs-32 font-hanzel text-center mb-0 mt-4">{{ __('messages.shopping_cart') }}</h1>
        <div class="shopping-cart-wrap mt-5">
            <div class="shopping-cart-tab d-flex justify-content-center justify-content-center gap-5">
                <div class="shopping-cart-tab-item d-flex align-items-center gap-3 pb-3 {{ $isSuccess ? '' : 'active' }}" data-step="1">
                    <span class="step-num fs-16 fw-bold text-center text-white">1</span>
                    <span class="step-text fs-16 fw-bold">{{ __('messages.step_cart') }}</span>
                </div>
                <div class="shopping-cart-tab-item d-flex align-items-center gap-3 pb-3" data-step="2">
                    <span class="step-num fs-16 fw-bold text-center text-white">2</span>
                    <span class="step-text fs-16 fw-bold">{{ __('messages.step_select_dealer') }}</span>
                </div>
                <div class="shopping-cart-tab-item d-flex align-items-center gap-3 pb-3 {{ $isSuccess ? 'active' : '' }}" data-step="3">
                    <span class="step-num fs-16 fw-bold text-center text-white">3</span>
                    <span class="step-text fs-16 fw-bold">{{ __('messages.step_complete') }}</span>
                </div>
            </div>
            <div class="shopping-cart-content mt-5">
                <div class="shopping-cart-content-item {{ $isSuccess ? 'd-none' : 'active' }}" data-step="1">
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="shopping-cart-content-products">
                                @if(empty($cartData['data']['items']) || count($cartData['data']['items']) == 0)
                                <div id="cart-empty" class="text-center py-5 shopping-cart-content-sidebar border border-dark-subtle rounded-2 ms-0 ms-xl-4 mt-4 mt-xl-0">
                                    <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M50 100C77.6142 100 100 77.6142 100 50C100 22.3858 77.6142 0 50 0C22.3858 0 0 22.3858 0 50C0 77.6142 22.3858 100 50 100Z" fill="#F5F5F5" />
                                        <path d="M70 35H30L35 70H65L70 35Z" stroke="#C0C0C0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M40 35V30C40 27.2386 42.2386 25 45 25H55C57.7614 25 60 27.2386 60 30V35" stroke="#C0C0C0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <h3 class="fs-24 mt-4">{{ __('messages.cart_empty') }}</h3>
                                    <p class="text-muted">{{ __('messages.cart_empty_message') }}</p>

                                    <button class="btn btn-primary w-100" style="line-height: 40px;background-color: var(--color-red);border: 0;color: var(--bs-white) !important;" onclick="window.location.href='{{ route('home') }}'">{{ __('messages.continue_shopping') }}</button>
                                </div>
                                @else
                                <div id="cart-items" class="table-div">
                                    <div class="table-div-row table-div-thead border-bottom border-dark-subtle">
                                        <div class="table-div-cell">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="shopping-cart-content-products-checkall">
                                                <label class="form-check-label fs-16 fw-bold" for="shopping-cart-content-products-checkall">
                                                    <span class="d-none d-lg-block">{{ __('messages.select_all') }}</span>
                                                    <span class="d-lg-none">{{ __('messages.all') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="table-div-cell"><span class="fs-16 fw-bold d-none d-md-block">{{ __('messages.product') }}</span></div>
                                        <div class="table-div-cell"><span class="fs-16 fw-bold d-none d-md-block">{{ __('messages.quantity') }}</span></div>
                                        <div class="table-div-cell"><span class="fs-16 fw-bold d-none d-md-block">{{ __('messages.price') }}</span></div>
                                        <div class="table-div-cell text-center">
                                            <span class="fs-16 fw-bold d-none d-lg-block">{{ __('messages.delete') }}</span>
                                        </div>
                                    </div>

                                    @foreach($cartData['data']['items'] as $item)
                                    @php
                                    $product = $item['product'];
                                    $imageUrl = $product['image'];
                                    $price = floatval($product['price'] ?? 0);
                                    $oldPrice = floatval($product['old_price'] ?? 0);
                                    $quantity = intval($item['quantity'] ?? 1);
                                    @endphp
                                    <div class="table-div-row table-div-row-product" data-cart-id="{{ $product['id'] }}">
                                        <div class="table-div-cell shopping-cart-content-products-img-wrap">
                                            <div class="d-flex align-items-center">
                                                <div class="form-check">
                                                    <input class="form-check-input cart-item-checkbox" type="checkbox" value="{{ $product['id'] }}">
                                                </div>
                                                <div class="shopping-cart-content-products-img">
                                                    <img src="{{ $imageUrl }}" alt="{{ $product['name'] }}" class="img-fluid">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-div-cell name-cell">
                                            <p class="fs-18 font-hanzel mb-1">{{ $product['name'] }}</p>
                                            <p class="fs-12 mb-1">{{ $product['code'] ?? '' }}</p>
                                        </div>
                                        <div class="table-div-cell quantity-cell">
                                            <div class="quantity d-flex align-items-center">
                                                <div class="qty-box qty-decrease fs-20" data-cart-id="{{ $product['id'] }}">-</div>
                                                <input type="tel" class="form-control qty-input" value="{{ $quantity }}" data-cart-id="{{ $product['id'] }}">
                                                <div class="qty-box qty-increase fs-20" data-cart-id="{{ $product['id'] }}">+</div>
                                            </div>
                                        </div>
                                        <div class="table-div-cell price-cell">
                                            <div class="product-price d-flex gap-3 align-items-center">
                                                @if(($product['has_display_price'] ?? false) && $oldPrice > $price)
                                                <span class="fs-16 text-decoration-line-through opacity-50 fw-500">{{ number_format($oldPrice, 0, ',', '.') }}đ</span>
                                                @endif
                                                <span class="fs-20 fw-bold">{{ ($product['has_display_price'] ?? false) ? ($product['price_display'] ?? number_format($price, 0, ',', '.') . 'đ') : __('messages.contact') }}</span>
                                            </div>
                                        </div>
                                        <div class="table-div-cell delete-cell text-center">
                                            <svg width="18" height="22" viewBox="0 0 18 22" fill="none" xmlns="http://www.w3.org/2000/svg" class="cart-item-remove" data-cart-id="{{ $product['id'] }}" role="button">
                                                <path d="M17.3571 1.37501H12.5357L12.158 0.571492C12.078 0.399709 11.9548 0.255209 11.8022 0.154247C11.6496 0.0532863 11.4736 -0.000130108 11.2942 7.53359e-06H6.70179C6.52274 -0.000728544 6.34712 0.0524887 6.19506 0.153562C6.04299 0.254636 5.92062 0.399477 5.84196 0.571492L5.46429 1.37501H0.642857C0.472361 1.37501 0.308848 1.44744 0.188289 1.57637C0.0677294 1.7053 0 1.88017 0 2.06251L0 3.43751C0 3.61984 0.0677294 3.79471 0.188289 3.92364C0.308848 4.05257 0.472361 4.12501 0.642857 4.12501H17.3571C17.5276 4.12501 17.6912 4.05257 17.8117 3.92364C17.9323 3.79471 18 3.61984 18 3.43751V2.06251C18 1.88017 17.9323 1.7053 17.8117 1.57637C17.6912 1.44744 17.5276 1.37501 17.3571 1.37501ZM2.1375 20.0664C2.16816 20.59 2.38426 21.0815 2.74181 21.4407C3.09936 21.7999 3.57147 21.9999 4.06205 22H13.9379C14.4285 21.9999 14.9006 21.7999 15.2582 21.4407C15.6157 21.0815 15.8318 20.59 15.8625 20.0664L16.7143 5.50001H1.28571L2.1375 20.0664Z" fill="#A4A4A4" />
                                            </svg>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="shopping-cart-content-sidebar border border-dark-subtle rounded-2 ms-0 ms-xl-4 mt-4 mt-xl-0">
                                <h3 class="fs-20 fw-bold">{{ __('messages.order_value') }}</h3>
                                <!-- <div class="discount-code mt-3">
                                    <div class="fs-16 fw-500">{{ __('messages.discount_code') }}</div>
                                    <div class="fs-14 opacity-50">{{ __('messages.discount_message') }}</div>
                                    <div class="input-group input-group-lg mt-2">
                                        <input type="text" class="form-control rounded-0" placeholder="{{ __('messages.enter_code') }}" id="discount-code-input">
                                        <span class="input-group-text rounded-0 fw-500" role="button" id="apply-discount-btn">{{ __('messages.apply') }}</span>
                                    </div>
                                </div> -->
                                <div class="d-flex align-items-center justify-content-between mt-5 mb-3 pb-3 border-bottom border-light-subtle">
                                    <div class="fs-16">{{ __('messages.subtotal') }}</div>
                                    <div class="fs-16 fw-600">
                                        {{ $hasPricedItem ? number_format($pricedSubtotal, 0, ',', '.') . 'đ' : __('messages.contact') }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fs-20 fw-600">{{ __('messages.total') }}</div>
                                    <div class="fs-20 fw-600">
                                        {{ $hasPricedItem ? number_format($pricedSubtotal, 0, ',', '.') . 'đ' : __('messages.contact') }}
                                    </div>
                                </div>
                                @if(!empty($cartData['data']['items']) && count($cartData['data']['items']) > 0)
                                <button class="btn btn-primary w-100 btn-completed mt-4" id="btn-next-step-2">{{ __('messages.select_dealer_btn') }}</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="shopping-cart-content-item d-none " data-step="2">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="shopping-cart-content-location">
                                <ul class="nav flex-column nav-pills" id="dealer-list-container" role="tablist" aria-orientation="vertical">

                                    @forelse($distributors as $index => $dealer)
                                    <div class="nav-link-item ">
                                        <div class="d-flex justify-content-between">
                                            <div class="nav-link-item-info">
                                                <h3 class="fs-20 font-hanzel">{{ $dealer->name }}</h3>
                                                <ul class="list-unstyled mb-0">
                                                    <li class="d-flex align-items-center gap-2">
                                                        <img src="{{ asset('langding/imgs/icon-location.svg') }}" alt="{{ __('messages.icon') }}" width="13">
                                                        <a href="#" class="text-muted fs-16 opacity-75">
                                                            {{ $dealer->address }}{{ $dealer->city_name ? ', '.$dealer->city_name : '' }}
                                                        </a>
                                                    </li>
                                                    <li class="d-flex align-items-center gap-2 mt-3">
                                                        <img src="{{ asset('langding/imgs/telephone-call.svg') }}" alt="{{ __('messages.icon') }}" width="16">
                                                        <a href="tel:{{ $dealer->phone }}" class="text-muted fs-16 opacity-75">{{ $dealer->phone }}</a>
                                                    </li>
                                                    <li class="d-flex align-items-center gap-2 mt-3">
                                                        <img src="{{ asset('langding/imgs/icon-mail.svg') }}" alt="{{ __('messages.icon') }}" width="16">
                                                        <a href="mailto:{{ $dealer->email }}" class="text-muted fs-16 opacity-75">{{ $dealer->email }}</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="btn-choose">
                                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                                    id="shopping-cart-tab-{{ $index + 1 }}"
                                                    data-bs-toggle="pill"
                                                    data-bs-target="#shopping-cart-content-{{ $index + 1 }}"
                                                    data-dealer-code="{{ $dealer->code }}"
                                                    data-dealer-name="{{ $dealer->name }}"
                                                    type="button"
                                                    role="tab"
                                                    aria-controls="shopping-cart-content-{{ $index + 1 }}"
                                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                                    {{ __('messages.choose') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-muted">{{ __('messages.select_address') }}</div>
                                    @endforelse

                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="shopping-cart-content-sidebar border border-dark-subtle rounded-2 ms-xl-4 mt-4 mt-lg-0">
                                <!-- Form checkout -->
                                <form id="checkout-form" action="{{ route('cart.checkout') }}" method="POST">
                                    <div class="tab-content">
                                        <div class="tab-content">
                                            <h3 class="fs-20 fw-bold mb-3">{{ __('messages.delivery_info') }}</h3>

                                            <div class="mb-3">
                                                <label for="recipient_name" class="form-label fs-14 fw-500 mb-2">{{ __('messages.recipient_name') }}</label>
                                                <input
                                                    type="text"
                                                    required
                                                    class="form-control"
                                                    id="recipient_name"
                                                    name="recipient_name"
                                                    value="{{ old('recipient_name') ?: (Auth::check() ? Auth::user()->name : '') }}"
                                                    placeholder="{{ __('messages.recipient_name') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="recipient_phone" class="form-label fs-14 fw-500 mb-2">{{ __('messages.recipient_phone') }}</label>
                                                <input
                                                    type="tel"
                                                    required
                                                    class="form-control"
                                                    id="recipient_phone"
                                                    name="recipient_phone"
                                                    value="{{ old('recipient_phone') ?: (Auth::check() ? Auth::user()->phone : '') }}"
                                                    placeholder="{{ __('messages.recipient_phone') }}">
                                            </div>

                                            <select required class="form-select" id="city_code" name="city_code">
                                                <option value="">{{ __('messages.select_address') }}</option>
                                                @foreach($provainces as $province)
                                                <option value="{{ $province->code }}">{{ $province->name_vi }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mb-3">
                                                <label for="recipient_address" class="form-label fs-14 fw-500 mb-2">{{ __('messages.recipient_address') }}</label>
                                                <textarea
                                                    class="form-control"
                                                    id="recipient_address"
                                                    name="recipient_address"
                                                    rows="3"
                                                    placeholder="{{ __('messages.recipient_address') }}"
                                                    required>{{ old('recipient_address') ?: (Auth::check() ? Auth::user()->address : '') }}</textarea>
                                            </div>

                                            <div class="mb-0">
                                                <label for="recipient_note" class="form-label fs-14 fw-500 mb-2">{{ __('messages.recipient_note') }}</label>
                                                <textarea
                                                    class="form-control"
                                                    id="recipient_note"
                                                    name="recipient_note"
                                                    rows="3"
                                                    placeholder="{{ __('messages.recipient_note') }}">{{ old('recipient_note') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    @csrf
                                    <input type="hidden" name="payment_method" id="payment_method_input" value="cod">
                                    <input type="hidden" name="dealer_name" id="dealer_name_input" value="">
                                    <input type="hidden" name="dealer_address" id="dealer_address_input" value="">
                                    <input type="hidden" name="dealer_code" id="dealer_code_input" value="{{ $distributors[0]->code ?? '' }}">

                                    <button type="submit" class="btn btn-primary w-100 mt-4" id="btn-complete-order" style="line-height: 40px;background-color: var(--color-red);border: 0;color: var(--bs-white) !important;">
                                        {{ __('messages.place_order') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="shopping-cart-content-item {{ $isSuccess ? 'active' : 'd-none' }}" data-step="3">
                    <section class="order-success-modern">

                        <div class="success-checkmark-wrapper">
                            <div class="success-checkmark">
                                <div class="check-icon">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="success-header-modern">
                            <h2 class="success-title">Đơn hàng đã được tạo!</h2>
                            <p class="success-subtitle">Cảm ơn bạn đã tin tưởng chúng tôi</p>
                        </div>

                        <div class="success-body-modern">
                            <div class="success-products-grid">
                                <div class="product-done-list"></div>
                            </div>

                            <div class="success-details-modern">
                                <table class="product-done-info">
                                    <tbody>
                                        <tr>
                                            <td class="detail-label">Mã đơn:</td>
                                            <td class="detail-value"></td>
                                        </tr>
                                        <tr>
                                            <td class="detail-label">Ngày:</td>
                                            <td class="detail-value"></td>
                                        </tr>
                                        <tr>
                                            <td class="detail-label">Tổng:</td>
                                            <td class="detail-value total-amount"></td>
                                        </tr>
                                        <tr>
                                            <td class="detail-label">Thanh toán:</td>
                                            <td class="detail-value"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <a href="{{ route('customer.order-list') }}" class="btn-view-orders">
                                    Xem lịch sử <span>→</span>
                                </a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


<style>

</style>

@push('scripts')
<script>
    window.routes = {
        cartAdd: '{{ route("cart.add") }}',
        cartUpdate: '{{ route("cart.update", ":id") }}',
        cartRemove: '{{ route("cart.remove", ":id") }}',
        cartCheckout: '{{ route("cart.checkout") }}',
        cartDistributorsByCity: '{{ route("cart.distributors.by-city") }}'
    };

    $(document).ready(function() {
        $('#city_code').on('change', function() {
            const cityCode = $(this).val();
            loadDealersByCity(cityCode);
        });
        // Translation object for JavaScript messages
        const translations = {
            confirm_delete: '{{ __("messages.confirm_delete") }}',
            quantity_updated: '{{ __("messages.quantity_updated") }}',
            update_error: '{{ __("messages.update_error") }}',
            cannot_update_quantity: '{{ __("messages.cannot_update_quantity") }}',
            item_removed: '{{ __("messages.item_removed") }}',
            cannot_remove_item: '{{ __("messages.cannot_remove_item") }}',
            select_payment_warning: '{{ __("messages.select_payment_warning") }}',
            no_order_data: '{{ __("messages.no_order_data") }}',
            processing: '{{ __("messages.processing") }}',
            contact: '{{ __("messages.contact") }}',
            cash: '{{ __("messages.cash") }}',
            atm: '{{ __("messages.atm") }}',
            visa: '{{ __("messages.visa") }}',
            vnpay: '{{ __("messages.vnpay") }}'
        };

        // Tăng số lượng
        $('.qty-increase').on('click', function() {
            const cartId = $(this).data('cart-id');
            const input = $(`.qty-input[data-cart-id="${cartId}"]`);
            const newQuantity = parseInt(input.val());

            updateCartQuantity(cartId, newQuantity);
        });

        // Giảm số lượng
        $('.qty-decrease').on('click', function() {
            const cartId = $(this).data('cart-id');
            const input = $(`.qty-input[data-cart-id="${cartId}"]`);
            const newQuantity = Math.max(1, parseInt(input.val()));

            updateCartQuantity(cartId, newQuantity);
        });

        // Thay đổi số lượng trực tiếp
        $('.qty-input').on('change', function() {
            const cartId = $(this).data('cart-id');
            const newQuantity = Math.max(1, parseInt($(this).val()) || 1);

            updateCartQuantity(cartId, newQuantity);
        });

        // Xóa sản phẩm
        $('.cart-item-remove').on('click', function() {
            if (!confirm(translations.confirm_delete)) return;

            const cartId = $(this).data('cart-id');
            removeCartItem(cartId);
        });

        // Hàm cập nhật số lượng
        function updateCartQuantity(cartId, quantity) {

            $.ajax({
                url: window.routes.cartUpdate.replace(':id', cartId),
                method: 'PUT',
                data: {
                    quantity: quantity,
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    // Hiển thị loading
                    $(`.qty-input[data-cart-id="${cartId}"]`).prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        // Cập nhật lại số lượng trong input
                        $(`.qty-input[data-cart-id="${cartId}"]`).val(quantity);

                        toastr.success(translations.quantity_updated);

                    } else {
                        toastr.error(response.message || translations.update_error);
                        $(`.qty-input[data-cart-id="${cartId}"]`).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || translations.cannot_update_quantity;
                    toastr.error(message);
                    $(`.qty-input[data-cart-id="${cartId}"]`).prop('disabled', false);
                }
            });
        }

        // Hàm xóa sản phẩm
        function removeCartItem(cartId) {
            $.ajax({
                url: window.routes.cartRemove.replace(':id', cartId),
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(translations.item_removed);

                        setTimeout(function() {
                            location.reload();
                        }, 5000);
                    } else {
                        toastr.error(response.message || translations.update_error);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || translations.cannot_remove_item;
                    toastr.error('Lỗi: ' + message);
                }
            });
        }

        // Check all
        $('#shopping-cart-content-products-checkall').on('change', function() {
            $('.cart-item-checkbox').prop('checked', $(this).is(':checked'));
        });

        // Thay đổi phương thức thanh toán
        $('input[name="payment_method"]').on('change', function() {
            const paymentValue = $(this).val();
            const paymentText = $(this).next('label').find('.fs-14').text();

            $('#payment-method-name').text(paymentText);
            $('#payment_method_input').val(paymentValue);
        });

        // Xử lý submit form checkout
        $('#checkout-form').on('submit', function(e) {
            const paymentMethod = $('#payment_method_input').val();

            if (!paymentMethod) {
                e.preventDefault();
                toastr.warning(translations.select_payment_warning);
                return false;
            }

            // Disable button để tránh submit nhiều lần
            const $btn = $('#btn-complete-order');
            $btn.prop('disabled', true).html(translations.processing);

            // Form sẽ submit bình thường
            return true;
        });

        // Kiểm tra nếu có orderData (đã checkout thành công)
        @if(isset($orderData) && $orderData)
        // Tự động chuyển sang step 4
        $('.shopping-cart-content-item').removeClass('active').addClass('d-none');
        $('.shopping-cart-content-item[data-step="3"]').addClass('active').removeClass('d-none');

        $('.shopping-cart-tab-item').removeClass('active');
        $('.shopping-cart-tab-item[data-step="3"]').addClass('active');

        // Cập nhật thông tin đơn hàng
        updateStep4WithOrderData(@json($orderData));

        // Scroll to top
        $('html, body').animate({
            scrollTop: 0
        }, 300);
        @endif


        // Hàm cập nhật thông tin vào step 4
        function updateStep4WithOrderData(data) {
            if (!data) {
                return;
            }
            // Cập nhật mã đơn hàng
            $('.product-done-info tr:nth-child(1) td:nth-child(2)').text('#' + data.order_number);

            // Cập nhật ngày đặt
            $('.product-done-info tr:nth-child(2) td:nth-child(2)').text(data.created_at);

            // Cập nhật tổng tiền
            const pricedTotal = (data.items || []).reduce((sum, item) => {
                const unitPrice = Number(item.unit_price || 0);
                const totalPrice = Number(item.total_price || 0);
                return unitPrice > 0 ? sum + totalPrice : sum;
            }, 0);

            const totalText = pricedTotal > 0 ?
                new Intl.NumberFormat('vi-VN').format(pricedTotal) + 'đ' :
                translations.contact;

            $('.product-done-info tr:nth-child(3) td:nth-child(2)').text(totalText);

            // Cập nhật phương thức thanh toán
            const paymentMethods = {
                'cash': translations.cash,
                'cod': translations.cash,
                'atm': translations.atm,
                'visa': translations.visa,
                'vnpay': translations.vnpay
            };
            $('.product-done-info tr:nth-child(4) td:nth-child(2)').text(
                paymentMethods[data.payment_method] || data.payment_method
            );

            // Cập nhật danh sách sản phẩm
            if (data.items && data.items.length > 0) {
                const productListHtml = data.items.map(item => {
                    const product = item.product || {};
                    const imageUrl = product.image_urls[0] || '{{ asset("images/no-image.png") }}';
                    const quantity = item.quantity || 1;
                    const productName = product.name || '{{ __("messages.product") }}';

                    return `<div class="product-done-item mt-4">
                            <div class="product-done-item-img">
                                <img src="${imageUrl}" alt="${productName}" class="img-fluid">
                            </div>
                            <span class="product-done-item-quantity">${quantity}</span>
                        </div>
                    `;
                }).join('');

                $('.product-done-list').html(productListHtml);
            }
        }
    });
</script>

<script>
    function bindSelectedDealerToCheckout() {
        const $activeBtn = $('.shopping-cart-content-location .btn-choose .nav-link.active').first();
        if ($activeBtn.length) {
            $('#dealer_code_input').val($activeBtn.data('dealer-code') || '');
            $('#dealer_name_input').val($activeBtn.data('dealer-name') || '');
            $('#dealer_address_input').val($activeBtn.data('dealer-address') || '');
        }

    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderDealerList(dealers) {
        if (!Array.isArray(dealers) || dealers.length === 0) {
            return `<div class="text-muted">{{ __('messages.no_data') }}</div>`;
        }

        return dealers.map((dealer, index) => {
            const name = escapeHtml(dealer.name);
            const email = escapeHtml(dealer.email);
            const phone = escapeHtml(dealer.phone);
            const address = escapeHtml(dealer.address);
            const cityName = escapeHtml(dealer.city_name || '');
            const code = escapeHtml(dealer.code || '');

            const fullAddress = cityName ? `${address}, ${cityName}` : address;
            const activeClass = index === 0 ? '' : '';

            return `
            <div class="nav-link-item">
                <div class="d-flex justify-content-between">
                    <div class="nav-link-item-info">
                        <h3 class="fs-20 font-hanzel">${name}</h3>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center gap-2">
                                <img src="{{ asset('langding/imgs/icon-location.svg') }}" alt="{{ __('messages.icon') }}" width="13">
                                <a href="#" class="text-muted fs-16 opacity-75">${fullAddress}</a>
                            </li>
                            <li class="d-flex align-items-center gap-2 mt-3">
                                <img src="{{ asset('langding/imgs/telephone-call.svg') }}" alt="{{ __('messages.icon') }}" width="16">
                                <a href="tel:${phone}" class="text-muted fs-16 opacity-75">${phone}</a>
                            </li>
                            <li class="d-flex align-items-center gap-2 mt-3">
                                <img src="{{ asset('langding/imgs/icon-mail.svg') }}" alt="{{ __('messages.icon') }}" width="16">
                                <a href="mailto:${email}" class="text-muted fs-16 opacity-75">${email}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="btn-choose">
                        <button
                            class="nav-link ${activeClass}"
                            type="button"
                            data-dealer-code="${code}"
                            data-dealer-name="${name}"
                            data-dealer-address="${address}">
                            {{ __('messages.choose') }}
                        </button>
                    </div>
                </div>
            </div>
        `;
        }).join('');
    }

    function loadDealersByCity(cityCode) {
        const $container = $('#dealer-list-container');

        // Reset nếu chưa chọn city
        if (!cityCode) {
            $container.html(`<div class="text-muted">{{ __('messages.no_data') }}</div>`);
            $('#dealer_code_input').val('');
            $('#dealer_name_input').val('');
            $('#dealer_address_input').val('');
            return;
        }

        // Loading UI
        $container.html(`<div class="text-muted">{{ __('messages.processing') }}...</div>`);

        $.ajax({
            url: window.routes.cartDistributorsByCity,
            method: 'GET',
            data: {
                city_code: cityCode
            },
            success: function(response) {
                if (!response?.success) {
                    $container.html(`<div class="text-muted">{{ __('messages.no_data') }}</div>`);
                    $('#dealer_code_input').val('');
                    $('#dealer_name_input').val('');
                    $('#dealer_address_input').val('');
                    return;
                }

                const html = renderDealerList(response.data || []);
                $container.html(html);

                // Đồng bộ dealer được chọn (đang có sẵn trong file của bạn)
                bindSelectedDealerToCheckout();
            },
            error: function() {
                $container.html(`<div class="text-muted">{{ __('messages.no_data') }}</div>`);
                $('#dealer_code_input').val('');
                $('#dealer_name_input').val('');
                $('#dealer_address_input').val('');
                toastr.error('{{ __("messages.update_error") }}');
            }
        });
    }

    $(document).on('click', '.shopping-cart-content-location .btn-choose .nav-link', function() {
        $('.shopping-cart-content-location .btn-choose .nav-link').removeClass('d-none');
        $(this).addClass('d-none');
        $('.shopping-cart-content-location .nav-link-item').removeClass('dealer-selected');
        $(this).closest('.nav-link-item').addClass('dealer-selected');
        //bindSelectedDealerToCheckout();
        // SET hidden inputs để submit nhận dealer
        $('#dealer_code_input').val($(this).data('dealer-code') || '');
        $('#dealer_name_input').val($(this).data('dealer-name') || '');
        $('#dealer_address_input').val($(this).data('dealer-address') || '');
    });

    bindSelectedDealerToCheckout();
</script>
@endpush