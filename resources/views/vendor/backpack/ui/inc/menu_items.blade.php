{{-- This file is used for menu items by any Backpack v6 theme --}}
{{-- Custom styles for admin panel --}}
<style>
    .form-control.form-select {
        min-height: 200px;
    }

    /* Menu styling improvements */
    .nav-item.dropdown .dropdown-menu {
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        margin-top: 5px;
    }

    .nav-item.dropdown .dropdown-item {
        padding: 8px 16px;
        transition: all 0.3s ease;
    }

    .nav-item.dropdown .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }

    .nav-item.dropdown .dropdown-item i {
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }

    .dropdown-divider {
        margin: 8px 0;
        border-color: #e9ecef;
    }

    /* Menu icons */
    .nav-icon {
        margin-right: 8px;
        width: 16px;
        text-align: center;
    }

    /* Active menu item */
    .nav-item.active .nav-link {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
        border-radius: 4px;
    }

    /* Hover effects */
    .nav-link:hover {
        background-color: rgba(0, 123, 255, 0.05);
        border-radius: 4px;
    }
</style>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

{{-- Menu sản phẩm --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="la la-shopping-cart nav-icon"></i> Sản phẩm
    </a>
    <ul class="dropdown-menu" aria-labelledby="productsDropdown">
        <li><a class="dropdown-item" href="{{ backpack_url('product') }}"><i class="la la-box nav-icon"></i> Quản lý sản phẩm</a></li>
        <li><a class="dropdown-item" href="{{ backpack_url('product-category') }}"><i class="la la-folder nav-icon"></i> Danh mục sản phẩm</a></li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item" href="{{ backpack_url('product-attribute') }}"><i class="la la-tags nav-icon"></i> Thuộc tính sản phẩm</a></li>
        <li><a class="dropdown-item" href="{{ backpack_url('product-attribute-value') }}"><i class="la la-list nav-icon"></i> Giá trị thuộc tính</a></li>
        {{-- TÌM menu Products (khoảng line 30-50) và THÊM vào trong <ul> --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('product-vehicle-fitment') }}">
                <i class="nav-icon la la-car"></i> <span>Vehicle Fitments</span>
            </a>
        </li>
    </ul>
</li>

{{-- Menu hệ thống --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="systemDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="la la-cog nav-icon"></i> Hệ thống
    </a>
    <ul class="dropdown-menu" aria-labelledby="systemDropdown">
        <li><a class="dropdown-item" href="{{ backpack_url('user') }}"><i class="la la-users nav-icon"></i> Quản lý người dùng</a></li>
        <li><a class="dropdown-item" href="{{ backpack_url('contact') }}"><i class="la la-envelope nav-icon"></i> Quản lý contacts</a></li>
        <li><a class="dropdown-item" href="{{ backpack_url('api-request-log') }}"><i class="la la-history nav-icon"></i> Log Đến API</a></li>
        <li><a class="dropdown-item" href="{{ backpack_url('api-outbound-log') }}"><i class="la la-history nav-icon"></i> Log đi API</a></li>
        <!-- <li><a class="dropdown-item" href="{{ backpack_url('language') }}"><i class="la la-globe nav-icon"></i> Ngôn ngữ</a></li> -->
    </ul>
</li>

{{-- Menu Tin tức --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="newsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="la la-newspaper nav-icon"></i> Tin tức
    </a>
    <ul class="dropdown-menu" aria-labelledby="newsDropdown">
        <li><a class="dropdown-item" href="{{ backpack_url('post') }}"><i class="la la-list nav-icon"></i> Quản lý bài viết</a></li>
        <li><a class="dropdown-item" href="{{ backpack_url('post-category') }}"><i class="la la-folder nav-icon"></i> Danh mục bài viết</a></li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item" href="{{ route('admin.homepage-layout.index') }}"><i class="la la-sort nav-icon"></i> Sắp xếp trang chủ</a></li>

    </ul>
</li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="newsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="la la-image nav-icon"></i> Danh mục banner
    </a>
    <ul class="dropdown-menu" aria-labelledby="newsDropdown">
        <li><a class="dropdown-item" href="{{ backpack_url('banner-category') }}"><i class="la la-image nav-icon"></i> Quản lý danh mục banner</a></li>
        <!-- <li><a class="dropdown-item" href="{{ backpack_url('post') }}"><i class="la la-list nav-icon"></i> Quản lý banner</a></li> -->
    </ul>
</li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="newsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="la la-newspaper nav-icon"></i> Đơn hàng
    </a>
    <ul class="dropdown-menu" aria-labelledby="newsDropdown">
        <li><a class="dropdown-item" href="{{ backpack_url('order') }}"><i class="la la-list nav-icon"></i> Quản lý đơn hàng</a></li>
    </ul>
</li>