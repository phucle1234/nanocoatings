{{-- Ẩn/hiện & xóa chọn danh mục NPP khi role khác dealer (form vẫn gửi — server luôn sync lại) --}}
@push('after_scripts')
<script>
(function () {
    function roleSelect() {
        return document.querySelector('select[name="role"]');
    }
    function wrap() {
        return document.getElementById('user-npp-product-categories-wrap');
    }
    function clearMultiSelect() {
        var w = wrap();
        if (!w) return;
        var sel = w.querySelector('select[multiple]');
        if (!sel) return;
        Array.from(sel.options).forEach(function (o) { o.selected = false; });
    }
    function syncVisibility() {
        var w = wrap();
        if (!w) return;
        var r = roleSelect();
        w.style.display = (r && r.value === 'dealer') ? '' : 'none';
    }
    function onRoleChange() {
        var r = roleSelect();
        if (r && r.value !== 'dealer') {
            clearMultiSelect();
        }
        syncVisibility();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            syncVisibility();
            roleSelect()?.addEventListener('change', onRoleChange);
        });
    } else {
        syncVisibility();
        roleSelect()?.addEventListener('change', onRoleChange);
    }
})();
</script>
@endpush
