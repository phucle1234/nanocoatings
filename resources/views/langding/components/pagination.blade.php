@php
    $pageParam = $page_param ?? 'page';
@endphp

@if(!empty($pagination) && isset($pagination['last_page']) && $pagination['last_page'] > 1)
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        {{-- Nút về trang đầu --}}
        <li class="page-item d-none d-sm-inline-block {{ $pagination['current_page'] == 1 ? 'disabled' : '' }}">
            <a class="page-link" href="{{ request()->fullUrlWithQuery([$pageParam => 1]) }}" aria-label="Trang đầu">
                <i class="bi bi-chevron-double-left"></i>
            </a>
        </li>

        {{-- Nút trang trước --}}
        <li class="page-item {{ $pagination['current_page'] == 1 ? 'disabled' : '' }}">
            <a class="page-link" href="{{ request()->fullUrlWithQuery([$pageParam => max(1, $pagination['current_page'] - 1)]) }}" aria-label="Trang trước">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        {{-- Hiển thị các số trang --}}
        @php
            $currentPage = $pagination['current_page'];
            $lastPage = $pagination['last_page'];
            // Giảm số ô phân trang hiển thị để tránh vỡ layout mobile.
            $window = 1;
            $startPage = max(1, $currentPage - $window);
            $endPage = min($lastPage, $currentPage + $window);
        @endphp

        {{-- Trang đầu nếu không gần current page --}}
        @if($startPage > 1)
            <li class="page-item">
                <a class="page-link" href="{{ request()->fullUrlWithQuery([$pageParam => 1]) }}">1</a>
            </li>
            @if($startPage > 2)
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            @endif
        @endif

        {{-- Các trang xung quanh current page --}}
        @for($i = $startPage; $i <= $endPage; $i++)
            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                <a class="page-link" href="{{ request()->fullUrlWithQuery([$pageParam => $i]) }}">{{ $i }}</a>
            </li>
        @endfor

        {{-- Trang cuối nếu không gần current page --}}
        @if($endPage < $lastPage)
            @if($endPage < $lastPage - 1)
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            @endif
            <li class="page-item">
                <a class="page-link" href="{{ request()->fullUrlWithQuery([$pageParam => $lastPage]) }}">{{ $lastPage }}</a>
            </li>
        @endif

        {{-- Nút trang sau --}}
        <li class="page-item {{ $pagination['current_page'] == $pagination['last_page'] ? 'disabled' : '' }}">
            <a class="page-link" href="{{ request()->fullUrlWithQuery([$pageParam => min($pagination['last_page'], $pagination['current_page'] + 1)]) }}" aria-label="Trang sau">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>

        {{-- Nút đến trang cuối --}}
        <li class="page-item d-none d-sm-inline-block {{ $pagination['current_page'] == $pagination['last_page'] ? 'disabled' : '' }}">
            <a class="page-link" href="{{ request()->fullUrlWithQuery([$pageParam => $pagination['last_page']]) }}" aria-label="Trang cuối">
                <i class="bi bi-chevron-double-right"></i>
            </a>
        </li>
    </ul>
</nav>
@endif