@php
    $entry = $entry ?? ($crud->getCurrentEntry() ?? null);
    $scopes = is_array($entry?->display_scopes ?? null) ? $entry->display_scopes : [];
    $homepageChecked = (bool) ($scopes['homepage'] ?? false);
    $selectedSectorIds = array_map('intval', $scopes['sector_ids'] ?? []);
    $sectors = app(\App\Services\SectorService::class)->getActiveSectors();
    $blockLabel = $blockLabel ?? 'block danh mục sản phẩm';
@endphp

<div class="form-group mb-3">
    <label class="form-label fw-semibold">Hiển thị {{ $blockLabel }}</label>
    <p class="text-muted small mb-2">
        Chọn trang chủ và/hoặc các ngành ứng dụng sẽ hiển thị mục này.
    </p>

    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="display_scopes_homepage" id="display_scopes_homepage"
            value="1" {{ $homepageChecked ? 'checked' : '' }}>
        <label class="form-check-label" for="display_scopes_homepage">
            <strong>Home — Trang chủ</strong>
        </label>
    </div>

    @if ($sectors->isNotEmpty())
        <div class="border rounded p-3 mt-2">
            <div class="small text-muted mb-2">Ngành ứng dụng</div>
            @foreach ($sectors as $sector)
                @php
                    $sectorName = $sector->translations->firstWhere('language', 'vi')?->name
                        ?? $sector->translations->first()?->name
                        ?? ('Ngành #' . $sector->id);
                @endphp
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="display_scopes_sector_ids[]"
                        id="display_scopes_sector_{{ $sector->id }}" value="{{ $sector->id }}"
                        {{ in_array((int) $sector->id, $selectedSectorIds, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="display_scopes_sector_{{ $sector->id }}">
                        {{ $sectorName }}
                    </label>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted small mb-0">Chưa có ngành ứng dụng. Tạo ngành trong menu <em>Ngành ứng dụng</em>.</p>
    @endif
</div>
