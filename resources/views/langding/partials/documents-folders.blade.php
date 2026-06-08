{{-- Partial: cây thư mục (danh sách folder) --}}
@if(isset($folders) && count($folders) > 0)
<ul class="document-tree list-unstyled mb-0">
    @foreach($folders as $folder)
    @php
    $folderSlug = $folder['slug'] ?? $folder['id'];
    $folderName = $folder['name'] ?? 'Thư mục';
    $url = isset($parentSlug) ? url('document/' . $parentSlug . '/' . $folderSlug) : url('document/' . $folderSlug);
    @endphp
    <li class="document-folder-item py-3 border-bottom">
        <a href="{{ $url }}" class="d-flex align-items-center text-dark text-decoration-none document-folder-link">
            <i class="bi bi-folder-fill fs-2 text-warning me-3"></i>
            <span class="fs-18">{{ $folderName }}</span>
            <i class="bi bi-chevron-right ms-2 text-muted"></i>
        </a>
    </li>
    @endforeach
</ul>
@else
<div class="text-center py-5">
    <p class="text-muted fs-18">{{ __('messages.no_subfolders') }}</p>
</div>
@endif