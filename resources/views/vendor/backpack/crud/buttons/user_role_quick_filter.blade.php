@php
    $current = request()->query('role');
    $baseUrl = url($crud->route);
    $queryBase = request()->query();
    unset($queryBase['role'], $queryBase['page']); // đổi role thì reset page

    $makeUrl = function (?string $role) use ($baseUrl, $queryBase) {
        $q = $queryBase;
        if ($role) {
            $q['role'] = $role;
        }
        return $baseUrl . (count($q) ? ('?' . http_build_query($q)) : '');
    };
@endphp

<div class="btn-group me-2" role="group" aria-label="Filter vai trò">
    <a href="{{ $makeUrl(null) }}"
        class="btn btn-sm {{ empty($current) ? 'btn-primary' : 'btn-outline-primary' }}">
        Tất cả
    </a>
    <a href="{{ $makeUrl('admin') }}"
        class="btn btn-sm {{ $current === 'admin' ? 'btn-primary' : 'btn-outline-primary' }}">
        Admin
    </a>
    <a href="{{ $makeUrl('customer') }}"
        class="btn btn-sm {{ $current === 'customer' ? 'btn-primary' : 'btn-outline-primary' }}">
        Khách hàng
    </a>
    <a href="{{ $makeUrl('dealer') }}"
        class="btn btn-sm {{ $current === 'dealer' ? 'btn-primary' : 'btn-outline-primary' }}">
        Đại lý / NPP
    </a>
</div>

