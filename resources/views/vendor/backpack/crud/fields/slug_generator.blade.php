<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log('Slug generator script loaded');
        const languages = @json($field['data']['languages'] ?? array_keys(config('languages.supported', [])));

        // ✅ Kiểm tra xem đang ở trang create hay edit
        const isEditPage = window.location.pathname.includes('/edit') ||
            window.location.pathname.includes('/update') ||
            document.querySelector('input[name="id"]') !== null;

        languages.forEach(function(lang) {
            const nameField = document.querySelector('input[name="name_' + lang + '"]') ||
                document.querySelector('input[name="title_' + lang + '"]');
            const slugField = document.querySelector('input[name="slug_' + lang + '"]');

            console.log('Language:', lang, 'Name field:', nameField, 'Slug field:', slugField, 'Is Edit:', isEditPage);

            if (nameField && slugField) {
                // ✅ Chỉ tự động tạo slug khi đang tạo mới (create)
                if (!isEditPage) {
                    console.log('Setting up slug generator for', lang, '(CREATE mode)');

                    // Lưu giá trị slug ban đầu để so sánh
                    let lastGeneratedSlug = slugField.value || '';

                    // Tự động tạo slug khi nhập tên (chỉ khi create)
                    nameField.addEventListener("input", function() {
                        const currentValue = this.value;
                        if (!currentValue || currentValue.trim() === '') {
                            slugField.value = '';
                            lastGeneratedSlug = '';
                            return;
                        }

                        // ✅ Tạo slug mới từ tiêu đề
                        const slug = currentValue
                            .toLowerCase()
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '') // Xóa dấu
                            .replace(/[^a-z0-9\s-]/g, '') // Chỉ giữ chữ, số, khoảng trắng và dấu gạch ngang
                            .replace(/\s+/g, '-') // Thay khoảng trắng bằng dấu gạch ngang
                            .replace(/-+/g, '-') // Loại bỏ nhiều dấu gạch ngang liên tiếp
                            .replace(/^-+|-+$/g, ''); // Loại bỏ dấu gạch ngang ở đầu và cuối

                        // ✅ Chỉ cập nhật slug nếu slug field trống hoặc slug hiện tại giống với slug đã tạo trước đó
                        // (để cho phép người dùng tự chỉnh sửa slug nếu muốn)
                        if (!slugField.value || slugField.value.trim() === '' || slugField.value === lastGeneratedSlug) {
                            slugField.value = slug;
                            lastGeneratedSlug = slug;
                            console.log('Generated slug:', slug);
                        }
                    });

                    // Tự động tạo slug khi paste (chỉ khi create)
                    nameField.addEventListener("paste", function() {
                        setTimeout(() => {
                            const currentValue = this.value;
                            if (!currentValue || currentValue.trim() === '') {
                                slugField.value = '';
                                lastGeneratedSlug = '';
                                return;
                            }

                            // ✅ Tạo slug mới từ tiêu đề
                            const slug = currentValue
                                .toLowerCase()
                                .normalize('NFD')
                                .replace(/[\u0300-\u036f]/g, '')
                                .replace(/[^a-z0-9\s-]/g, '')
                                .replace(/\s+/g, '-')
                                .replace(/-+/g, '-')
                                .replace(/^-+|-+$/g, '');

                            // ✅ Chỉ cập nhật slug nếu slug field trống hoặc slug hiện tại giống với slug đã tạo trước đó
                            if (!slugField.value || slugField.value.trim() === '' || slugField.value === lastGeneratedSlug) {
                                slugField.value = slug;
                                lastGeneratedSlug = slug;
                                console.log('Generated slug from paste:', slug);
                            }
                        }, 100);
                    });
                } else {
                    console.log('Skipping slug generator for', lang, '(EDIT mode - no auto-fill)');
                }
            } else {
                console.log('Fields not found for language:', lang);
            }
        });
    });
</script>