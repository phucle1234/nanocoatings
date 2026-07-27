@php
    $links = $field['links'] ?? [];
    $items = [
        'footer_contact' => 'Liên hệ (địa chỉ, SĐT, email)',
        'footer_about' => 'Về Nanocoatings',
        'footer_social' => 'Mạng xã hội',
    ];
@endphp

<div class="form-group col-md-12">
    <label>Liên hệ &amp; Mạng xã hội riêng cho ngành này</label>
    <div class="d-flex flex-wrap" style="gap: 10px;">
        @foreach ($items as $key => $label)
            @if ($links[$key] ?? null)
                <a href="{{ $links[$key] }}" class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="la la-pencil"></i> {{ $label }}
                </a>
            @else
                <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Lưu ngành trước đã">
                    {{ $label }}
                </button>
            @endif
        @endforeach
    </div>
    <small class="form-text text-muted">
        Bấm để mở trang quản lý nội dung tương ứng cho riêng ngành này (mỗi mục là danh sách các dòng: icon + nội dung + link).
    </small>
</div>
