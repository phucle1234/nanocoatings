@extends(backpack_view('blank'))

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">Sắp xếp block — {{ $sectorName }}</h1>
        <p class="ms-2 mb-0 text-muted" bp-section="page-subheading">Kéo thả · Bật/tắt từng block trang ngành</p>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-3">
                    <strong>Ảnh nền &amp; tiêu đề</strong> — sửa ảnh nền section, tiêu đề và mô tả.
                    <strong>Nội dung</strong> — quản lý bài viết / slide / video trong block.
                    </p>

                    <form method="post" action="{{ route('admin.sector-layout.update', $sector->id) }}" id="sector-layout-form">
                        @csrf
                        <ul class="list-group mb-3" id="sector-blocks-sortable">
                            @foreach ($blocks as $index => $block)
                                @php
                                    $translation = $block->translations->firstWhere('language', $locale)
                                        ?? $block->translations->first();
                                    $label = $translation->title ?? $block->section_type;
                                @endphp
                                <li class="list-group-item d-flex align-items-center gap-3 sector-block-item"
                                    data-id="{{ $block->id }}">
                                    <span class="handle text-muted" style="cursor: grab; font-size: 1.25rem;" title="Kéo để sắp xếp">⋮⋮</span>
                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                    <div class="flex-grow-1">
                                        <strong>{{ $label }}</strong>
                                        <div class="small text-muted"><code>{{ $block->section_type }}</code></div>
                                    </div>
                                    @if (!empty($blockBannerLinks[$block->section_type] ?? null))
                                        <a href="{{ $blockBannerLinks[$block->section_type] }}"
                                            class="btn btn-sm btn-outline-secondary text-nowrap"
                                            target="_blank" rel="noopener noreferrer"
                                            title="Sửa ảnh nền, tiêu đề và mô tả section">
                                            <i class="la la-image"></i> Ảnh nền &amp; tiêu đề
                                        </a>
                                    @endif
                                    @if (!empty($blockContentLinks[$block->section_type] ?? null))
                                        <a href="{{ $blockContentLinks[$block->section_type] }}"
                                            class="btn btn-sm btn-outline-primary text-nowrap"
                                            target="_blank" rel="noopener noreferrer"
                                            title="Mở quản lý bài viết / slide / video trong tab mới">
                                            <i class="la la-external-link"></i> Nội dung
                                        </a>
                                    @endif
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
                        @php
                            $sectorSlug = $sector->translations->firstWhere('language', 'vi')->slug ?? '';
                        @endphp
                        @if ($sectorSlug)
                            <a href="{{ route('sectors.show', $sectorSlug) }}" class="btn btn-outline-secondary ms-2" target="_blank" rel="noopener">
                                <i class="la la-external-link"></i> Xem trang ngành
                            </a>
                        @endif
                        <a href="{{ backpack_url('sector') }}" class="btn btn-outline-secondary ms-2">
                            <i class="la la-arrow-left"></i> Danh sách ngành
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
            const list = document.getElementById('sector-blocks-sortable');
            const form = document.getElementById('sector-layout-form');
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
                const items = list.querySelectorAll('.sector-block-item');
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
        #sector-blocks-sortable .sector-block-item.sortable-ghost {
            opacity: 0.5;
            background: #f8f9fa;
        }
    </style>
@endpush
