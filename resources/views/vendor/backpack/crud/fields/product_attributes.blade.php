{{-- Product Attributes Management Field --}}
<div class="form-group col-sm-12">
    <label class="form-label">Thuộc tính sản phẩm</label>
    <div class="product-attributes-container">

        {{-- Header với nút thêm thuộc tính --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Quản lý thuộc tính sản phẩm</h6>
            <button type="button" class="btn btn-sm btn-primary" id="add-attribute-btn">
                <i class="la la-plus"></i> Thêm thuộc tính
            </button>
        </div>

        {{-- Danh sách thuộc tính hiện tại --}}
        <div id="current-attributes" class="mb-3">
            @if(isset($entry) && $entry->exists)
            @php
            $productAttributes = \App\Models\ProductAttributeValue::whereHas('products', function($query) use ($entry) {
            $query->where('product_id', $entry->id);
            })->with(['attribute.translations', 'translations'])->get()->groupBy('attribute_id');
            @endphp
            <div class="row">
                @foreach($productAttributes as $attributeId => $attributeValues)
                @php
                $attribute = $attributeValues->first()->attribute;
                $attributeName = $attribute->translations->where('language', 'vi')->first()->name ?? $attribute->code;
                @endphp
                <div class="attribute-group mb-3 p-3 border rounded col-md-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="mb-0 text-primary">{{ $attributeName }} ({{ $attribute->type }})</h4>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-attribute" data-attribute-id="{{ $attributeId }}">
                            <i class="la la-trash"></i>
                        </button>
                    </div>
                    <div class="attribute-values text-success">
                        @foreach($attributeValues as $value)
                        @php
                        $translation = $value->translations->where('language', 'vi')->first();
                        $valueName = $translation ? $translation->value : $value->value;
                        $displayText = $translation ? $translation->value : $value->value;
                        @endphp
                        <div class="badge badge-success mr-1 mb-1 w-100">{{ $displayText }}</div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Form thêm thuộc tính mới --}}
        <div id="add-attribute-form" class="border rounded p-3" style="display: none;">
            <h3 class="text-danger">Thêm thuộc tính mới</h3>

            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Chọn thuộc tính</label>
                    <select class="form-control" id="attribute-select">
                        <option value="">-- Chọn thuộc tính --</option>
                        @php
                        $attributes = \App\Models\ProductAttribute::with('translations')->where('is_active', true)->get();
                        @endphp
                        @foreach($attributes as $attribute)
                        @php
                        $translation = $attribute->translations->where('language', 'vi')->first();
                        $attributeName = $translation ? $translation->name : $attribute->code;
                        @endphp
                        <option value="{{ $attribute->id }}" data-type="{{ $attribute->type }}">
                            {{ $attributeName }} ({{ $attribute->type }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Chọn giá trị</label>
                    <select class="form-control" id="value-select" multiple>
                        <option value="">-- Chọn giá trị --</option>
                    </select>
                </div>
            </div>

            {{-- ✅ Thêm checkbox showDetail --}}
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="show-detail-checkbox" checked>
                        <label class="form-check-label" for="show-detail-checkbox">
                            Hiển thị trong thông số kỹ thuật
                        </label>
                        <small class="form-text text-muted d-block">
                            Nếu chọn, thuộc tính này sẽ được hiển thị trong phần "Thông số kỹ thuật" của sản phẩm
                        </small>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="button" class="btn btn-success btn-sm" id="save-attribute-btn">
                    <i class="la la-save"></i> Lưu thuộc tính
                </button>
                <button type="button" class="btn btn-secondary btn-sm" id="cancel-attribute-btn">
                    <i class="la la-times"></i> Hủy
                </button>
            </div>
        </div>

        {{-- Hidden input để lưu dữ liệu --}}
        <input type="hidden" name="product_attributes_data" id="product-attributes-data" value="">
    </div>
</div>

{{-- Include CSS files --}}
@push('after_styles')
<link rel="stylesheet" href="{{ asset('css/admin/components.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/attributes.css') }}">
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Product attributes script loaded');
        const addAttributeBtn = document.getElementById('add-attribute-btn');
        const addAttributeForm = document.getElementById('add-attribute-form');
        const cancelAttributeBtn = document.getElementById('cancel-attribute-btn');
        const attributeSelect = document.getElementById('attribute-select');
        const valueSelect = document.getElementById('value-select');
        const saveAttributeBtn = document.getElementById('save-attribute-btn');
        const currentAttributes = document.getElementById('current-attributes');
        const productAttributesData = document.getElementById('product-attributes-data');

        // Kiểm tra các elements có tồn tại không
        if (!attributeSelect || !valueSelect) {
            console.error('Required elements not found:', {
                attributeSelect: !!attributeSelect,
                valueSelect: !!valueSelect
            });
            return;
        }

        // Hiển thị form thêm thuộc tính
        addAttributeBtn.addEventListener('click', function() {
            addAttributeForm.style.display = 'block';
            addAttributeBtn.style.display = 'none';
        });

        // Ẩn form thêm thuộc tính
        cancelAttributeBtn.addEventListener('click', function() {
            addAttributeForm.style.display = 'none';
            addAttributeBtn.style.display = 'block';
            attributeSelect.value = '';
            valueSelect.innerHTML = '<option value="">-- Chọn giá trị --</option>';
        });

        // Load giá trị khi chọn thuộc tính
        attributeSelect.addEventListener('change', function() {
            const attributeId = this.value;
            const attributeType = this.options[this.selectedIndex].getAttribute('data-type');

            console.log('Attribute selected:', attributeId, 'Type:', attributeType);

            if (attributeId) {
                // Reset dropdown
                valueSelect.innerHTML = '<option value="">Đang tải...</option>';
                valueSelect.disabled = true;

                const apiUrl = `/admin/api/attribute-values/${attributeId}`;
                console.log('Fetching from:', apiUrl);

                // Gọi AJAX để lấy giá trị thuộc tính
                fetch(apiUrl)
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('API Response:', data);
                        valueSelect.innerHTML = '<option value="">-- Chọn giá trị --</option>';

                        // Kiểm tra nếu có lỗi
                        if (data.error) {
                            valueSelect.innerHTML = '<option value="">Lỗi: ' + data.error + '</option>';
                            console.error('API Error:', data.error);
                            return;
                        }

                        // Kiểm tra nếu không có dữ liệu
                        if (!data || data.length === 0) {
                            valueSelect.innerHTML = '<option value="">Không có giá trị nào</option>';
                            console.warn('No attribute values found for attribute ID:', attributeId);
                            return;
                        }

                        console.log('Adding', data.length, 'options to select');

                        // Thêm các option
                        data.forEach(value => {
                            const option = document.createElement('option');
                            option.value = value.id;
                            // Ưu tiên text, sau đó name, cuối cùng là value
                            const displayText = value.text || value.name || value.value || 'N/A';
                            option.textContent = displayText;
                            console.log('Adding option:', value.id, displayText);
                            valueSelect.appendChild(option);
                        });

                        // Cho phép chọn nhiều nếu là multiselect
                        if (attributeType === 'multiselect') {
                            valueSelect.setAttribute('multiple', 'multiple');
                            console.log('Set multiselect mode');
                        } else {
                            valueSelect.removeAttribute('multiple');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading attribute values:', error);
                        valueSelect.innerHTML = '<option value="">Lỗi khi tải dữ liệu</option>';
                        alert('Có lỗi xảy ra khi tải giá trị thuộc tính: ' + error.message);
                    })
                    .finally(() => {
                        valueSelect.disabled = false;
                    });
            } else {
                valueSelect.innerHTML = '<option value="">-- Chọn giá trị --</option>';
                valueSelect.removeAttribute('multiple');
            }
        });

        // Lưu thuộc tính
        saveAttributeBtn.addEventListener('click', function() {
            const attributeId = attributeSelect.value;
            const selectedValues = Array.from(valueSelect.selectedOptions).map(option => option.value);
            const showDetail = document.getElementById('show-detail-checkbox').checked ? 'Y' : 'N'; // ✅ Lấy giá trị checkbox

            if (!attributeId || selectedValues.length === 0) {
                alert('Vui lòng chọn thuộc tính và ít nhất một giá trị');
                return;
            }

            // Gọi AJAX để lưu thuộc tính
            const formData = new FormData();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value ||
                '{{ csrf_token() }}';
            formData.append('_token', csrfToken);
            formData.append('attribute_id', attributeId);
            formData.append('values', JSON.stringify(selectedValues));
            formData.append('show_detail', showDetail); // ✅ Thêm show_detail vào form data

            @if(isset($entry) && $entry -> exists)
            formData.append('product_id', '{{ $entry->id }}');
            @endif

            fetch('/admin/api/product-attributes', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload trang để hiển thị thuộc tính mới
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi lưu thuộc tính');
                });
        });

        // Xóa thuộc tính
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-attribute')) {
                if (confirm('Bạn có chắc chắn muốn xóa thuộc tính này?')) {
                    const attributeId = e.target.closest('.remove-attribute').getAttribute('data-attribute-id');

                    const formData = new FormData();
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                        document.querySelector('input[name="_token"]')?.value ||
                        '{{ csrf_token() }}';
                    formData.append('_token', csrfToken);
                    formData.append('attribute_id', attributeId);

                    @if(isset($entry) && $entry -> exists)
                    formData.append('product_id', '{{ $entry->id }}');
                    @endif

                    fetch('/admin/api/product-attributes/remove', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Có lỗi xảy ra: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Có lỗi xảy ra khi xóa thuộc tính');
                        });
                }
            }
        });
    });
</script>