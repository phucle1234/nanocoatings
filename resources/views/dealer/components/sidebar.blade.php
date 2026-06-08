<div class="sidebar-list bg-body-secondary rounded mb-5 mb-xl-0">

    {{-- User Profile --}}
    <div class="d-flex align-items-center gap-1 px-1 py-4 border-bottom">
        @if(auth()->user()->avatar)
        <img src="{{ auth()->user()->avatar }}"
            alt="{{ auth()->user()->name }}"
            class="rounded-circle flex-shrink-0"
            style="width:20px;height:20px;object-fit:cover;">
        @else
        <div class="text-center rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center text-white fs-11"
            style="width:20px;height:20px;background-color:#c8102e;">
            <i class="bi bi-person-fill"></i>
        </div>
        @endif
        <div class="overflow-hidden">
            <div class="fw-600 fs-11 text-truncate lh-sm">{{ auth()->user()->name }}</div>
            <div class="text-center text-secondary fs-13 text-truncate">{{ auth()->user()->user_name }}</div>
        </div>
    </div>

    <ul class="list-unstyled mb-0">
        <li class="{{ $sidebarActive == 'dashboard' ? 'active-parent' : '' }}">
            <a class="d-flex align-items-center gap-3 fw-500 px-3 py-4" href="{{ route('dealer.dashboard') }}">
                <i class="bi bi-border-all fs-18"></i>
                <span class="fw-500">Tổng quan</span>
            </a>
        </li>
        <li class="{{ $sidebarActive == 'order-parent' ? 'active-parent' : '' }}">
            <a class="d-flex align-items-center gap-3 fw-500 px-3 py-4" href="javascript:void(0)">
                <i class="bi bi-bag fs-18"></i>
                <span class="d-flex align-items-center justify-content-between gap-2 w-100">
                    Quản lý hàng hóa <i class="bi bi-caret-down"></i>
                </span>
            </a>
            <ul class="list-unstyled list-child bg-light ps-5 py-3">
                <li class="py-3">
                    <a title="NPP mua của Casumina" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'cart' ? 'text-red' : '' }}"
                        href="{{ route('dealer.cart') }}">
                        <span>Đặt hàng</span>
                    </a>
                </li>
                <li class="py-3">
                    <a title="Đơn hàng NPP mua của Casumina" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'order-history' ? 'text-red' : '' }}"
                        href="{{ route('dealer.order-history') }}">
                        <span>Theo dõi đơn hàng</span>
                    </a>
                </li>
                {{-- <li class="py-3">
                    <a title="Đơn hàng NPP cho NPP khác mượn" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'loan-order' ? 'text-red' : '' }}"
                href="{{ route('dealer.loan-order') }}">
                <span>Đơn hàng mượn</span>
                </a>
        </li> --}}
        <li class="py-3">
            <a title="Lịch sử mua và cho mượn của NPP" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'order-diary' ? 'text-red' : '' }}"
                href="{{ route('dealer.order-diary') }}">
                <span>Sổ nhật ký đơn hàng</span>
            </a>
        </li>
    </ul>
    </li>
    <li class="{{ $sidebarActive == 'warranty' ? 'active-parent' : '' }}">
        <a class="d-flex align-items-center gap-3 fw-500 px-3 py-4"
            href="{{ route('dealer.warranty') }}">
            <i class="bi bi-shield-check fs-18"></i>
            <span>Chứng nhận bảo hành</span>
        </a>
    </li>
    <li class="{{ $sidebarActive == 'casumina-parent' ? 'active-parent' : '' }}">
        <a class="d-flex align-items-center gap-3 fw-500 px-3 py-4" href="javascript:void(0)">
            <i class="bi bi-bookmark-check fs-18"></i>
            <span class="d-flex align-items-center justify-content-between gap-2 w-100">
                Đơn hàng Casumina <i class="bi bi-caret-down"></i>
            </span>
        </a>
        <ul class="list-unstyled list-child bg-light ps-5 py-3">
            <li class="py-3">
                <a title="NPP bán cho khách hàng" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'ecommerce' ? 'text-red' : '' }}"
                    href="{{ route('dealer.ecommerce') }}">
                    <span>Đơn hàng E-commerce</span>
                </a>
            </li>
            <li class="py-3">
                <a title="Đơn hàng NPP bán cho khách hàng" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'sale-cart' ? 'text-red' : '' }}"
                    href="{{ route('dealer.sale-cart') }}">
                    <span>Đơn hàng bán NPP</span>
                </a>
            </li>
            <li class="py-3">
                <a title="Lịch sử đơn hàng NPP bán cho khách" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'sale-order-history' ? 'text-red' : '' }}"
                    href="{{ route('dealer.sale-order-history') }}">
                    <span>Lịch sử bán hàng NPP</span>
                </a>
            </li>
            <li class="py-3">
                <a title="Lịch sử đơn NPP bán cho khách hàng" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'sale-order-diary' ? 'text-red' : '' }}"
                    href="{{ route('dealer.sale-order-diary') }}">
                    <span>Sổ nhật ký đơn hàng bán</span>
                </a>
            </li>
            <li class="py-3">
                <a title="Khách hàng của NPP" class="d-flex align-items-center gap-2 fw-500 {{ isset($sidebarChildActive) && $sidebarChildActive == 'customer' ? 'text-red' : '' }}"
                    href="{{ route('dealer.customer') }}">
                    <span>Quản lý khách hàng</span>
                </a>
            </li>
        </ul>
    </li>
    </ul>
</div>
<div class="mt-5 d-xl-block d-none">
    <a class="fs-14 d-flex align-items-center gap-2 fw-500" href="{{ route('logout') }}">
        <i class="bi bi-box-arrow-left text-red fs-18"></i>
        <span>Đăng xuất</span>
    </a>
</div>