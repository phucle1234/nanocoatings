{{-- Partial: danh sách file tài liệu PDF --}}
@if(isset($dangKiemCategories) && count($dangKiemCategories) > 0)
@foreach($dangKiemCategories as $doc)
<div class="document-item"
    data-category="{{ $doc['slug'] ?? '' }}"
    data-title="{{ strtolower($doc['title'] ?? '') }}">
    <div class="row align-items-center py-4 border-bottom">
        <div class="col-md-1 text-center">
            <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
        </div>
        <div class="col-md-6">
            <h3 class="fs-16 fw-600 mb-1">
                @if(!empty($doc['file_url']))
                <a href="{{ $doc['file_url'] }}"
                    target="_blank"
                    class="text-dark text-decoration-none">
                    {{ $doc['file_name'] ?? $doc['title'] ?? '' }}
                </a>
                @else
                {{ $doc['file_name'] ?? $doc['title'] ?? '' }}
                @endif
            </h3>
            <div class="text-muted fs-13">
                <i class="bi bi-calendar3 me-1"></i>
                {{ isset($doc['published_at']) || isset($doc['created_at'])
                                ? \Carbon\Carbon::parse($doc['published_at'] ?? $doc['created_at'])->format('d/m/Y')
                                : '—' }}
                @if(!empty($doc['file_size']))
                <span class="ms-3">
                    <i class="bi bi-hdd me-1"></i>
                    {{ number_format($doc['file_size'] / 1024, 0) }} KB
                </span>
                @endif
            </div>
        </div>
        <div class="col-md-2">
            <span class="badge bg-danger bg-opacity-10 text-danger fs-12 fw-500">PDF</span>
        </div>
        <div class="col-md-3 d-flex gap-2 justify-content-end">
            @if(!empty($doc['file_url']))
            <a href="{{ $doc['file_url'] }}"
                target="_blank"
                class="btn btn-sm btn-outline-danger">
                <i class="bi bi-eye me-1"></i>{{ __('messages.view') }}
            </a>
            @endif
        </div>
    </div>
</div>
@endforeach
@else
<div class="text-center py-5">
    <i class="bi bi-folder2-open fs-1 text-muted"></i>
    <p class="text-muted fs-18 mt-3">{{ __('messages.no_documents') }}</p>
</div>
@endif