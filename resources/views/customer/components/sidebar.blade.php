<div class="sidebar-list bg-body-secondary rounded-3 px-4 py-3">
    <h3 class="fs-18 mb-4 fw-600">{{ __('messages.personal_dashboard_sidebar') }}</h3>
    <ul class="list-unstyled">
        <li class="mb-3">
            <a class="fs-14 d-flex align-items-center gap-2 fw-500 {{ $sidebarActive == 'order' ? 'text-red' : '' }}" href="{{ route('customer.order-list') }}">
                <i class="bi bi-cart2 text-red fs-18"></i>
                <span>{{ __('messages.manage_orders_sidebar') }}</span>
            </a>
        </li>
        <li class="mb-3">
            <a class="fs-14 d-flex align-items-center gap-2 fw-500 {{ $sidebarActive == 'warranty' ? 'text-red' : '' }}" href=" {{ route('customer.warranty-list') }}">
                <i class="bi bi-code-square text-red fs-18"></i>
                <span>{{ __('messages.manage_warranty_sidebar') }}</span>
            </a>
        </li>
        <li class="mb-2">
            <a class="fs-14 d-flex align-items-center gap-2 fw-500 {{ $sidebarActive == 'profile' ? 'text-red' : '' }}" href="{{ route('customer.profile') }}">
                <i class="bi bi-gear text-red fs-18"></i>
                <span>{{ __('messages.account_info_sidebar') }}</span>
            </a>
        </li>
    </ul>
    <div class="mt-5">
        <a class="fs-14 d-flex align-items-center gap-2 fw-500" href="{{ route('logout') }}">
            <i class="bi bi-box-arrow-left text-red fs-18"></i>
            <span>{{ __('messages.logout') }}</span>
        </a>
    </div>
</div>
