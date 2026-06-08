/**
 * Cart Management JS
 * Xử lý thêm sản phẩm vào giỏ hàng
 */

(function($) {
    'use strict';

    // Hàm thêm sản phẩm vào giỏ hàng
    window.addToCart = function(productId, productName, quantity = 1, callback) {
        if (!productId) {
            toastr.error('Không tìm thấy thông tin sản phẩm');
            return;
        }

        $.ajax({
            url: '/cart/add',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                console.log('Đang thêm sản phẩm vào giỏ hàng...');
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Đã thêm "' + productName + '" vào giỏ hàng');
                    
                    // Callback nếu có
                    if (typeof callback === 'function') {
                        callback(response);
                    } else {
                        // Mặc định reload sau 500ms
                        setTimeout(function() {
                            location.reload();
                        }, 5000);
                    }
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Không thể thêm sản phẩm vào giỏ hàng';
                toastr.error('Lỗi: ' + message);
                console.error('Add to cart error:', xhr);
            }
        });
    };

    // Hàm mua ngay - Thêm vào giỏ và chuyển trang
    window.buyNow = function(productId, productName, quantity = 1) {
        if (!productId) {
            toastr.error('Không tìm thấy thông tin sản phẩm');
            return;
        }

        $.ajax({
            url: '/cart/add',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Chuyển đến trang giỏ hàng
                    window.location.href = '/cart';
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Không thể thêm sản phẩm vào giỏ hàng';
                toastr.error('Lỗi: ' + message);
            }
        });
    };

    $(document).ready(function() {
        // ==================== XỬ LÝ TĂNG/GIẢM SỐ LƯỢNG ====================
        $(document).on('click', '.qty-increase', function() {
            const $input = $(this).siblings('.qty-input');
            let value = parseInt($input.val()) || 1;
            $input.val(value);
        });

        $(document).on('click', '.qty-decrease', function() {
            const $input = $(this).siblings('.qty-input');
            let value = parseInt($input.val()) || 1;
            if (value > 1) {
                $input.val(value);
            }
        });

        // Validate input số lượng
        $(document).on('change', '.qty-input', function() {
            let value = parseInt($(this).val()) || 1;
            if (value < 1) {
                $(this).val(1);
            }
        });

        // ==================== NÚT THÊM VÀO GIỎ HÀNG ====================
        $(document).on('click', '.add-to-cart-btn', function (e) {
            e.preventDefault();
            
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const productName = $btn.data('product-name') || 'Sản phẩm';
            const url = $btn.data('add-to-cart-url');
            
            // Lấy số lượng từ input (nếu có)
            const $qtyInput = $('.qty-input').first();
            const quantity = $qtyInput.length ? parseInt($qtyInput.val()) || 1 : 1;
            
            if (!productId) {
                toastr.error('Không tìm thấy thông tin sản phẩm');
                return;
            }

            // Disable button và hiển thị loading
            const originalText = $btn.html();
            $btn.prop('disabled', true);
            
            // Thêm vào giỏ hàng
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Hiển thị trạng thái thành công
                        toastr.success('Đã thêm "' + productName + '" (x' + quantity + ') vào giỏ hàng');
                        
                        // Reload sau 500ms
                        setTimeout(function() {
                            location.reload();
                        }, 5000);
                    } else {
                        toastr.error(response.message || 'Có lỗi xảy ra');
                        $btn.prop('disabled', false).css('opacity', '1').html(originalText);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Không thể thêm sản phẩm vào giỏ hàng';
                    toastr.error('Lỗi: ' + message);
                    $btn.prop('disabled', false).css('opacity', '1').html(originalText);
                }
            });
        });

        // ==================== NÚT MUA NGAY ====================
        $(document).on('click', '#btn-buy-now, .btn-buy-now', function(e) {
            // Chỉ prevent default nếu không phải link download
            if (!$(this).attr('href') || $(this).attr('href') === 'javascript:void(0)') {
                e.preventDefault();
            } else if ($(this).attr('href').includes('/storage/') || $(this).attr('href').includes('.pdf')) {
                // Cho phép download file
                return true;
            } else {
                e.preventDefault();
            }
            
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const productName = $btn.data('product-name') || 'Sản phẩm';
            const url = $btn.data('add-to-cart-url');
            const viewCartUrl = $btn.data('view-cart-url');
            
            // Lấy số lượng từ input (nếu có)
            const $qtyInput = $('.qty-input').first();
            const quantity = $qtyInput.length ? parseInt($qtyInput.val()) || 1 : 1;
            
            if (!productId) {
                toastr.error('Không tìm thấy thông tin sản phẩm');
                return;
            }

            // Disable button và hiển thị loading
            const originalText = $btn.html();
            $btn.prop('disabled', true)
                .css('opacity', '0.7')
                .html('<span class="spinner-border spinner-border-sm" style="width: 14px; height: 14px; margin-right: 6px;"></span>Đang xử lý...');
            
            // Thêm vào giỏ và chuyển trang
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Hiển thị trạng thái chuyển trang
                        $btn.html('<i class="bi bi-arrow-right-circle"></i> Chuyển trang...');
                        
                        // Chuyển đến trang giỏ hàng
                        setTimeout(function() {
                            window.location.href = viewCartUrl;
                        }, 300);
                    } else {
                        toastr.error(response.message || 'Có lỗi xảy ra');
                        $btn.prop('disabled', false).css('opacity', '1').html(originalText);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Không thể thêm sản phẩm vào giỏ hàng';
                    toastr.error('Lỗi: ' + message);
                    $btn.prop('disabled', false).css('opacity', '1').html(originalText);
                }
            });
        });

        // ==================== THÊM VÀO GIỎ TỪ FORM ====================
        $(document).on('submit', '.add-to-cart-form', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const productId = $form.find('input[name="product_id"]').val();
            const productName = $form.data('product-name') || 'Sản phẩm';
            const quantity = $form.find('input[name="quantity"]').val() || 1;
            
            addToCart(productId, productName, quantity);
        });
    });

})(jQuery);