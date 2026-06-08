<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" class="text-secondary fw-500 text-nowrap">
                    <div class="fs-18">
                        <input class="form-check-input" type="checkbox" id="checkAll" aria-label="...">
                    </div>
                </th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Mã đơn hàng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-center">Tình trạng đơn hàng
                </th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Ngày đặt hàng</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Phân loại</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap">Trị giá</th>
                <th scope="col" class="text-secondary fw-500 text-nowrap text-end pe-0">Tác vụ</th>
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
                        <a href="{{ route('dealer.order-history-detail', ['id' => $item->id]) }}"
                            class="fw-500">#{{ $item->order_number }}</a>
                    </td>
                    <td class="py-4 fs-14 text-center">
                        {!! \App\Helpers\DealerHelper::buyStatusHtml($item->status) !!}
                    </td>
                    <td class="py-4 fs-14">
                        <span class="fw-500">{{ $item->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y \l\ú\c H:i A') }}</span>
                    </td>
                    <td class="py-4 fs-14">
                        <span class="fw-500">Đơn hàng đặt</span>
                    </td>
                    <td class="py-4 fs-14">
                        <span class="fw-500">{{ number_format($item->total_amount, 0, ',', '.') }}đ</span>
                    </td>
                    <td class="text-end pe-0">
                        <div class="d-flex align-items-center justify-content-end gap-3">
                            <a href="{{ route('dealer.order-history-detail', ['id' => $item->id]) }}"><i
                                    class="bi bi-eye fs-22"></i></a>
                            {{-- <a href="{{ route('dealer.order-history-detail-warehouse', ['id' => $item->id]) }}"><i
                                    class="bi bi-archive fs-20"></i></a> --}}
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
            const checkAll = document.getElementById('checkAll');
            const checkItems = () => document.querySelectorAll('.check-item');

            if (!checkAll) return;

            checkAll.addEventListener('change', function() {
                checkItems().forEach(cb => {
                    cb.checked = this.checked;
                });
            });

            document.addEventListener('change', function(e) {
                if (!e.target.classList.contains('check-item')) return;

                const all = checkItems();
                checkAll.checked = all.length > 0 && [...all].every(cb => cb.checked);
                checkAll.indeterminate = !checkAll.checked && [...all].some(cb => cb.checked);
            });
        })();
    </script>
@endpush
