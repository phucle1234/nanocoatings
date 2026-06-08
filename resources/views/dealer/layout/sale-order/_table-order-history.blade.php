<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" class="text-secondary fw-500 text-nowrap"></th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Mã đơn hàng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Tình trạng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Thời gian</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Trị giá đơn hàng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-end">Tác vụ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($list as $item)
                <tr>
                    <td class="py-4 fs-18">
                        <div>
                            <input class="form-check-input check-item" type="checkbox" value="{{ $item->id }}"
                                aria-label="...">
                        </div>
                    </td>
                    <td class="py-4 fs-14">
                        <a href="{{ route('dealer.sale-order-detail', ['id' => $item->id]) }}"
                            class="fw-500">#{{ $item->order_number }}</a>
                    </td>
                    <td class="py-4 fs-14">
                        {!! \App\Helpers\DealerHelper::saleStatusHtml($item->status) !!}
                    </td>
                    <td class="py-4 fs-14">
                        <span
                            class="fw-500">{{ $item->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</span>
                    </td>

                    <td class="py-4 fs-14 text-end">
                        <span class="fw-500">{{ number_format($item->total_amount, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-3">
                            <a href="{{ route('dealer.sale-order-detail', ['id' => $item->id]) }}">
                                <i class="bi bi-eye fs-22"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <p class="fs-16 fw-500 text-secondary">Không tìm thấy đơn hàng phù hợp</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($list->hasPages())
    <div class="pt-4 border-top">
        {{ $list->links('pagination::bootstrap-5') }}
    </div>
@endif

@push('scripts')
    <script>
        (function() {
            document.addEventListener('change', function(e) {
                if (!e.target.classList.contains('check-item')) return;

                document.querySelectorAll('.check-item').forEach(cb => {
                    if (cb !== e.target) cb.checked = false;
                });
            });
        })();
    </script>
@endpush
