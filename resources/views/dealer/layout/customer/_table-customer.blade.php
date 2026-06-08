<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" class="text-secondary fw-500 text-nowrap"></th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Mã khách hàng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Tên khách hàng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Tiếp nhận</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Doanh thu</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Liên hệ</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Địa chỉ</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Tác vụ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td class="py-4 fs-18">
                        <div>
                            <input class="form-check-input check-item" type="checkbox" value="{{ $customer->id }}"
                                aria-label="...">
                        </div>
                    </td>
                    <td class="py-4 fs-14">
                        <a href="{{ route('dealer.customer-detail', $customer->id) }}" class="fw-500">#{{ $customer->code }}</a>
                    </td>
                    <td class="py-4 fs-14">
                        <span class="fw-500">{{ $customer->name }}</span>
                    </td>
                    <td class="py-4 fs-14">
                        @if ($customer->channel == 'online')
                            <span class="badge bg-success text-white fs-12 fw-400">Online</span>
                        @elseif($customer->channel == 'offline')
                            <span class="badge bg-warning text-white fs-12 fw-400">Offline</span>
                        @else
                            <span class="badge bg-secondary text-white fs-12 fw-400">Chưa xác định</span>
                        @endif
                    </td>
                    <td class="py-4 fs-14 text-end">
                        <span class="fw-500">{{ number_format($customer->orders_sum_total_amount, 0, ',', '.') }}đ</span>
                    </td>
                    <td class="py-4 fs-14">
                        <span class="fw-500">{{ $customer->phone }}</span>
                    </td>
                    <td class="py-4 fs-14">
                        <span class="fw-500">{{ $customer->address }}</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-3">
                            <a href="{{ route('dealer.customer-detail', $customer->id) }}"><i class="bi bi-eye fs-22"></i></a>
                            {{-- <span role="button"><i class="bi bi-trash3 fs-20"></i></span> --}}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <p class="fs-16 fw-500 text-secondary">Không tìm thấy khách hàng phù hợp</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($customers->hasPages())
    <div class="pt-4 border-top">
        {{ $customers->links('pagination::bootstrap-5') }}
    </div>
@endif
