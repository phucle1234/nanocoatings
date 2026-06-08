<?php

return [
    // Login page
    'login_title' => 'Đăng Nhập',
    'login_subtitle' => 'Chào mừng đến với Nanocoatings!',
    'login_placeholder_username' => 'Nhập tài khoản của bạn',
    'login_placeholder_password' => 'Nhập mật khẩu',
    'login_remember_me' => 'Ghi nhớ đăng nhập',
    'login_forgot_password' => 'Quên mật khẩu?',
    'login_button' => 'Đăng Nhập',
    'login_no_account' => 'Chưa có tài khoản?',
    'login_signup_link' => 'Đăng ký',

    // Register page
    'register_title' => 'Đăng Ký',
    'register_subtitle' => 'Tạo tài khoản mới để bắt đầu',
    'register_placeholder_name' => 'Nhập họ và tên',
    'register_placeholder_email' => 'Nhập email của bạn',
    'register_placeholder_phone' => 'Nhập số điện thoại',
    'register_placeholder_address' => 'Nhập địa chỉ',
    'register_terms_agree' => 'Tôi đồng ý với',
    'register_terms_service' => 'Điều khoản dịch vụ',
    'register_privacy_policy' => 'Chính sách bảo mật',
    'register_button' => 'Tạo tài khoản',
    'register_have_account' => 'Đã có tài khoản?',
    'register_login_link' => 'Đăng nhập',

    // Forgot password page
    'forgot_password_title' => 'Quên Mật Khẩu',
    'forgot_password_subtitle' => 'Nhập email để nhận link đặt lại mật khẩu',
    'forgot_password_placeholder_email' => 'Nhập email của bạn',
    'forgot_password_info' => 'Chúng tôi sẽ gửi link đặt lại mật khẩu đến email này',
    'forgot_password_button' => 'Gửi Link Đặt Lại Mật Khẩu',
    'forgot_password_back_login' => 'Quay lại đăng nhập',
    'forgot_password_no_account' => 'Chưa có tài khoản?',
    'forgot_password_signup_link' => 'Đăng ký ngay',

    // Common UI text
    'page_title' => 'Đăng nhập',
    'toast_notification' => 'Thông báo',
    'toast_error' => 'Thông báo lỗi',
    'processing' => 'Đang xử lý...',

    // Login validation & error messages
    'login_invalid_credentials' => 'Thông tin đăng nhập không chính xác.',
    'login_account_inactive' => 'Tài khoản chưa được kích hoạt.',
    'account_not_found' => 'Tài khoản không tồn tại.',

    // Login validation attributes
    'attr_username' => 'Tài khoản',
    'attr_password' => 'Mật khẩu',

    // Register validation messages
    'register_success' => 'Đăng ký thành công. Vui lòng đăng nhập để tiếp tục.',
    'register_error' => 'Có lỗi xảy ra trong quá trình đăng ký tài khoản. Thử lại sau!',
    'register_email_unique' => 'Email đã được sử dụng. Vui lòng chọn email khác.',
    'register_phone_unique' => 'Số điện thoại đã được sử dụng. Vui lòng chọn số khác.',
    'register_phone_required' => 'Số điện thoại là bắt buộc.',

    // Register validation attributes
    'attr_name' => 'Họ và tên',
    'attr_email' => 'Email',
    'attr_phone' => 'Số điện thoại',
    'attr_address' => 'Địa chỉ',
    'attr_terms' => 'Điều khoản dịch vụ',

    // Forgot password validation messages
    'forgot_password_email_required' => 'Vui lòng nhập email.',
    'forgot_password_email_invalid' => 'Địa chỉ email không hợp lệ.',
    'forgot_password_email_not_found' => 'Email không tồn tại.',
    'forgot_password_success' => 'Lấy lại mật khẩu thành công! Vui lòng kiểm tra email của bạn.',
    'forgot_password_error' => 'Đã xảy ra lỗi khi xử lý yêu cầu. Vui lòng thử lại sau.',

    // User management validation attributes
    'attr_code' => 'mã',
    'attr_parent_code' => 'mã cha',
    'attr_user_name' => 'tên đăng nhập',
    'attr_role' => 'vai trò',
    'attr_latitude' => 'vĩ độ',
    'attr_longitude' => 'kinh độ',
    'attr_city_code' => 'mã thành phố',
    'attr_type' => 'loại',
    'attr_product_categories' => 'danh mục sản phẩm phủ nano',

    // Profile update messages
    'profile_update_success' => 'Cập nhật thông tin thành công.',
    'password_update_success' => 'Cập nhật mật khẩu thành công',

    // Middleware access messages
    'please_login' => 'Vui lòng đăng nhập.',
    'no_access_dealer' => 'Bạn không có quyền truy cập khu vực đối tác.',
    'no_access_customer' => 'Bạn không có quyền truy cập khu vực khách hàng.',
    'no_access_area' => 'Bạn không có quyền truy cập khu vực này.',
];
