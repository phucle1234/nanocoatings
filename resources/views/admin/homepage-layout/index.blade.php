@extends(backpack_view('blank'))

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">Sắp xếp trang chủ</h1>
        <p class="ms-2 mb-0 text-muted" bp-section="page-subheading">Kéo thả để đổi thứ tự · Bật/tắt hiển thị từng block</p>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Trang chủ có đúng <strong>9 block</strong> (gồm footer). Không thêm/xóa block tại đây — chỉ sắp xếp và bật/tắt.
                    </p>

                    <form method="post" action="{{ route('admin.homepage-layout.update') }}" id="homepage-layout-form">
                        @csrf
                        <ul class="list-group mb-3" id="homepage-blocks-sortable">
                            @foreach ($blocks as $index => $block)
                                @php
                                    $translation = $block->translations->firstWhere('language', $locale)
                                        ?? $block->translations->first();
                                    $label = $translation->title ?? $block->section_type;
                                @endphp
                                <li class="list-group-item d-flex align-items-center gap-3 homepage-block-item"
                                    data-id="{{ $block->id }}">
                                    <span class="handle text-muted" style="cursor: grab; font-size: 1.25rem;" title="Kéo để sắp xếp">⋮⋮</span>
                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                    <div class="flex-grow-1">
                                        <strong>{{ $label }}</strong>
                                        <div class="small text-muted"><code>{{ $block->section_type }}</code></div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input type="hidden" name="blocks[{{ $index }}][id]" value="{{ $block->id }}">
                                        <input type="hidden" name="blocks[{{ $index }}][is_active]" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            name="blocks[{{ $index }}][is_active]" value="1"
                                            id="block-active-{{ $block->id }}"
                                            {{ $block->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="block-active-{{ $block->id }}">Hiển thị</label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <button type="submit" class="btn btn-primary">
                            <i class="la la-save"></i> Lưu thứ tự
                        </button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary ms-2" target="_blank" rel="noopener">
                            <i class="la la-external-link"></i> Xem trang chủ
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('homepage-blocks-sortable');
            const form = document.getElementById('homepage-layout-form');
            if (!list || !form || typeof Sortable === 'undefined') {
                return;
            }

            Sortable.create(list, {
                handle: '.handle',
                animation: 150,
                onEnd: function () {
                    reindexFormFields();
                }
            });

            form.addEventListener('submit', function () {
                reindexFormFields();
            });

            function reindexFormFields() {
                const items = list.querySelectorAll('.homepage-block-item');
                items.forEach(function (item, index) {
                    item.querySelectorAll('input[name*="[id]"]').forEach(function (input) {
                        input.name = 'blocks[' + index + '][id]';
                    });
                    item.querySelectorAll('input[name*="[is_active]"]').forEach(function (input) {
                        input.name = 'blocks[' + index + '][is_active]';
                    });
                    const badge = item.querySelector('.badge');
                    if (badge) {
                        badge.textContent = String(index + 1);
                    }
                });
            }
        });
    </script>
@endpush

@push('after_styles')
    <style>
        #homepage-blocks-sortable .homepage-block-item.sortable-ghost {
            opacity: 0.5;
            background: #f8f9fa;
        }
    </style>
@endpush
