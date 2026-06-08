<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PostRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\HomepageLayoutService;
use App\Traits\HasSlugGenerator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Prologue\Alerts\Facades\Alert;

class PostCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as traitDestroy;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    use HasSlugGenerator;

    public function setup()
    {
        CRUD::setModel(Post::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/post');
        CRUD::setEntityNameStrings('bài viết', 'Bài viết');
    }

    protected function setupListOperation()
    {


        CRUD::addColumn([
            'name' => 'row_number',
            'type' => 'row_number',
            'label' => 'STT',
            'orderable' => false,
        ]);
        CRUD::button('create')->stack('top')->view('crud::buttons.quick')->meta([
            'wrapper' => [
                'href' => function ($entry, $crud) {
                    $categoryId = request('category_id');
                    return backpack_url('post/create' . ($categoryId ? '?category_id=' . $categoryId : ''));
                },
            ]
        ]);
        // Refactored: Dùng model_function thay vì closure
        CRUD::column('title')
            ->label('Tiêu đề')
            ->type('model_function')
            ->function_name('getTitleDisplay');


        // Refactored: Dùng model_function thay vì closure
        CRUD::column('postcategories')
            ->label('Danh mục')
            ->type('model_function')
            ->function_name('getCategoryNamesDisplay');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('image')
            ->label('Hình ảnh')
            ->type('model_function')
            ->function_name('getImageThumbnailHtml')
            ->escaped(false);

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('status')
            ->label('Trạng thái')
            ->type('model_function')
            ->function_name('getStatusBadgeHtml')
            ->escaped(false);

        CRUD::column('is_active')->label('Kích hoạt')->type('boolean');
        CRUD::column('is_featured')->label('Nổi bật')->type('boolean');
        CRUD::column('sort_order')->label('Thứ tự')->type('number');
        CRUD::column('published_at')->label('Ngày xuất bản')->type('datetime');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        // Default ordering
        CRUD::orderBy('sort_order', 'asc');
        CRUD::orderBy('created_at', 'desc');

        if (request()->has('category_id')) {
            $categoryId = request()->query('category_id');

            // ✅ Nếu category_id = 0, chỉ hiển thị bài viết không phải banner
            if ($categoryId == 0) {
                CRUD::addClause('whereHas', 'postcategories', function ($query) {
                    $query->where('is_banner', false);
                });

                Alert::info('Đang hiển thị bài viết thường (không phải banner)')->flash();
            }
            // Filter theo category_id bình thường
            elseif ($categoryId) {
                CRUD::addClause('where', function ($query) use ($categoryId) {
                    $query->where(function ($q) use ($categoryId) {
                        $q->whereHas('postcategories', function ($relation) use ($categoryId) {
                            $relation->where('postcategory_id', $categoryId);
                        })->orWhere('postcategory_id', $categoryId);
                    });
                });

                if ($category = PostCategory::find($categoryId)) {
                    Alert::info('Đang hiển thị bài viết thuộc danh mục: ' . $category->name)->flash();
                }
            }
        } else {
            // ✅ Mặc định: Chỉ hiển thị bài viết có category is_banner = false
            CRUD::addClause('whereHas', 'postcategories', function ($query) {
                $query->where('is_banner', false);
            });
        }

        CRUD::addClause('where', 'post_type', '!=', config('homepage_layout.post_type', 'homepage_block'));

        CRUD::removeButton('show');
        CRUD::addButtonFromView('line', 'custom_view', 'custom_view', 'beginning');
        CRUD::setOperationSetting('responsiveTable', false);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(PostRequest::class);


        // ✅ Lấy category_id từ query string
        $categoryId = request()->query('category_id');
        $defaultCategories = [];

        if ($categoryId && PostCategory::find($categoryId)) {
            $defaultCategories = [(int)$categoryId]; // Chuyển thành array vì field là select_multiple

            // Hiện thông báo
            $category = PostCategory::find($categoryId);
            Alert::info('Bài viết sẽ được tạo trong danh mục: ' . ($category->name ?? ''))->flash();
        }

        // Thay đổi field từ postcategory_id thành postcategories
        CRUD::field('postcategories')->label('Danh mục')->type('select_multiple')
            ->entity('postcategories')
            ->model(PostCategory::class)
            ->attribute('name')
            ->options(function ($query) {
                return $query->with('translations')->get();
            })
            ->default($defaultCategories) // ✅ Set default từ query string
            ->hint('Chọn một hoặc nhiều danh mục cho bài viết')
            ->tab('Chung');

        CRUD::field('icon')->label('Icon')->type('text')
            ->hint('Tên icon từ thư viện icon (ví dụ: fa-file-text)')
            ->tab('Chung');

        CRUD::field('status')->label('Trạng thái')->type('select_from_array')
            ->options([
                'draft' => 'Bản nháp',
                'published' => 'Đã xuất bản',
                'archived' => 'Đã lưu trữ'
            ])
            ->default('draft')
            ->tab('Chung');

        CRUD::field('is_active')->label('Kích hoạt')->type('boolean')->default(true)->tab('Chung');
        CRUD::field('is_featured')->label('Nổi bật')->type('boolean')->default(false)->tab('Chung');
        CRUD::field('sort_order')->label('Thứ tự')->type('number')->default(0)->tab('Chung');
        CRUD::field('published_at')->label('Ngày xuất bản')->type('datetime')->tab('Chung');

        // Document upload field - dùng hidden field để lưu giá trị
        // Hidden field luôn được gửi trong form submit
        CRUD::field('document_file_id')
            ->label('')
            ->type('hidden')
            ->default(null)
            ->tab('Chung');

        // View field để hiển thị UI upload
        CRUD::field('document_upload_view')
            ->label('Tải lên tài liệu PDF')
            ->type('view')
            ->view('vendor.backpack.crud.fields.document_upload')
            ->attributes([
                'context' => 'post'
            ])
            ->hint('Tải lên tài liệu PDF cho bài viết (tối đa 30MB)')
            ->tab('Chung');

        $this->addMultilangFieldsWithSections();
        $this->addSlugGenerator('title_vi', 'slug', false);

        // ✅ Thêm view để inject JavaScript
        CRUD::field('summernote_sync_view')
            ->type('view')
            ->view('vendor.backpack.crud.fields.summernote-sync')
            ->onlyOn(['create', 'edit']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        // Đảm bảo các field image_urls đa ngôn ngữ có giá trị mặc định
        $languages = array_keys(config('languages.supported'));
        $entry = $this->crud->getCurrentEntry();

        foreach ($languages as $lang) {
            $translation = $entry->translations()->where('language', $lang)->first();
            $imageUrls = $translation ? $translation->image_urls : [];
            $defaultValue = is_array($imageUrls) ? implode("\n", $imageUrls) : $imageUrls;

            CRUD::modifyField('image_urls_' . $lang, [
                'default' => $defaultValue
            ]);
        }

        // Đảm bảo field document_file_id có giá trị mặc định từ entry
        CRUD::modifyField('document_file_id', [
            'default' => $entry->document_file_id ?? null
        ]);
    }

    protected function setupShowOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('slug')->label('Slug');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('postcategories')
            ->label('Danh mục')
            ->type('model_function')
            ->function_name('getCategoryNamesDisplay');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('image')
            ->label('Hình ảnh')
            ->type('model_function')
            ->function_name('getImageMediumHtml')
            ->escaped(false);

        CRUD::column('status')->label('Trạng thái')->type('text');
        CRUD::column('is_active')->label('Kích hoạt')->type('boolean');
        CRUD::column('is_featured')->label('Nổi bật')->type('boolean');
        CRUD::column('view_count')->label('Lượt xem')->type('number');
        CRUD::column('sort_order')->label('Thứ tự')->type('number');
        CRUD::column('published_at')->label('Ngày xuất bản')->type('datetime');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        $this->addMultilangColumns();
    }

    protected function addMultilangFieldsWithSections()
    {
        $languages = config('languages.supported');

        foreach ($languages as $lang => $langName) {
            // Tiêu đề bài viết
            CRUD::field('title_' . $lang)
                ->label('Tiêu đề (' . $langName . ')')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'title'))
                ->attributes([
                    'placeholder' => 'Nhập tiêu đề bài viết bằng ' . $langName
                ])
                ->tab($langName);

            // Slug
            CRUD::field('slug_' . $lang)
                ->label('Slug (' . $langName . ')')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'slug'))
                ->attributes([
                    'placeholder' => 'Sẽ được tạo tự động từ tiêu đề'
                ])
                ->tab($langName);

            // Hình ảnh cho từng ngôn ngữ
            $entry = $this->crud->getCurrentEntry();
            $translation = $entry ? $entry->translations()->where('language', $lang)->first() : null;
            $imageUrls = $translation ? $translation->image_urls : [];
            $defaultValue = is_array($imageUrls) ? implode("\n", $imageUrls) : $imageUrls;

            CRUD::field('image_urls_' . $lang)
                ->label('Hình ảnh (' . $langName . ')')
                ->type('view')
                ->view('components.multiple-images')
                ->default($defaultValue)
                ->data(['value' => $defaultValue])
                ->hint('Thêm nhiều hình ảnh cho bài viết bằng ' . $langName)
                ->tab($langName);

            // URL
            CRUD::field('url_' . $lang)
                ->label('Link URL (' . $langName . ')')
                ->type('url')
                ->default($this->getTranslationValue($lang, 'url'))
                ->attributes([
                    'placeholder' => 'https://example.com/link'
                ])
                ->hint('Nhập URL liên kết cho bài viết (nếu có)')
                ->tab($langName);

            // Nội dung bài viết
            CRUD::field('content_' . $lang)
                ->label('Nội dung (' . $langName . ')')
                ->type('summernote')
                ->default($this->getTranslationValue($lang, 'content'))
                ->options([
                    'height' => 300,
                    'toolbar' => [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'italic']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture']],
                        ['view', ['fullscreen', 'codeview']],
                    ],
                    // ✅ Thêm callback để sync khi codeview thay đổi
                    'callbacks' => [
                        'onCodeview' => 'function() {
                            var $editor = jQuery(this).closest(".note-editor");
                            var $textarea = $editor.prev("textarea");
                            var $codeview = $editor.find(".note-codable");
                            
                            // Sync khi blur khỏi codeview
                            $codeview.off("blur.sync").on("blur.sync", function() {
                                $textarea.val(jQuery(this).val());
                            });
                        }',
                        'onCodeviewClose' => 'function() {
                            var $editor = jQuery(this).closest(".note-editor");
                            var $textarea = $editor.prev("textarea");
                            var $codeview = $editor.find(".note-codable");
                            
                            // Sync khi đóng codeview
                            if ($codeview.length) {
                                $textarea.val($codeview.val());
                            }
                        }'
                    ]
                ])
                ->tab($langName);

            // Tóm tắt
            CRUD::field('excerpt_' . $lang)
                ->label('Tóm tắt (' . $langName . ')')
                ->type('textarea')
                ->default($this->getTranslationValue($lang, 'excerpt'))
                ->attributes([
                    'rows' => 3,
                    'placeholder' => 'Nhập tóm tắt bài viết bằng ' . $langName
                ])
                ->tab($langName);

            // SEO Fields
            CRUD::field('meta_title_' . $lang)
                ->label('Meta Title (' . $langName . ')')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'meta_title'))
                ->attributes([
                    'placeholder' => 'Tiêu đề SEO cho ' . $langName
                ])
                ->tab($langName);

            CRUD::field('meta_description_' . $lang)
                ->label('Meta Description (' . $langName . ')')
                ->type('textarea')
                ->default($this->getTranslationValue($lang, 'meta_description'))
                ->attributes([
                    'rows' => 2,
                    'placeholder' => 'Mô tả SEO cho ' . $langName
                ])
                ->tab($langName);

            CRUD::field('meta_keywords_' . $lang)
                ->label('Meta Keywords (' . $langName . ')')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'meta_keywords'))
                ->attributes([
                    'placeholder' => 'Từ khóa SEO cho ' . $langName
                ])
                ->tab($langName);

            CRUD::field('canonical_url_' . $lang)
                ->label('Canonical URL (' . $langName . ')')
                ->type('url')
                ->default($this->getTranslationValue($lang, 'canonical_url'))
                ->attributes([
                    'placeholder' => 'URL chuẩn cho ' . $langName
                ])
                ->tab($langName);

            // Open Graph Fields
            CRUD::field('og_title_' . $lang)
                ->label('OG Title (' . $langName . ')')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'og_title'))
                ->attributes([
                    'placeholder' => 'Tiêu đề chia sẻ mạng xã hội cho ' . $langName
                ])
                ->tab($langName);

            CRUD::field('og_description_' . $lang)
                ->label('OG Description (' . $langName . ')')
                ->type('textarea')
                ->default($this->getTranslationValue($lang, 'og_description'))
                ->attributes([
                    'rows' => 2,
                    'placeholder' => 'Mô tả chia sẻ mạng xã hội cho ' . $langName
                ])
                ->tab($langName);

            CRUD::field('og_image_' . $lang)
                ->label('OG Image (' . $langName . ')')
                ->type('url')
                ->default($this->getTranslationValue($lang, 'og_image'))
                ->attributes([
                    'placeholder' => 'Hình ảnh chia sẻ mạng xã hội cho ' . $langName
                ])
                ->tab($langName);
        }
    }

    protected function addMultilangColumns()
    {
        $languages = config('languages.supported');

        foreach ($languages as $lang => $langName) {
            // Refactored: Dùng model_function với parameter
            CRUD::column('title_' . $lang)
                ->label('Tiêu đề (' . $langName . ')')
                ->type('model_function')
                ->function_name('getTranslationTitle')
                ->function_parameters($lang);

            // Refactored: Dùng model_function với parameter
            CRUD::column('excerpt_' . $lang)
                ->label('Tóm tắt (' . $langName . ')')
                ->type('model_function')
                ->function_name('getTranslationExcerpt')
                ->function_parameters($lang);

            // Refactored: Dùng model_function với parameter
            CRUD::column('slug_' . $lang)
                ->label('Slug (' . $langName . ')')
                ->type('model_function')
                ->function_name('getTranslationSlug')
                ->function_parameters($lang);
        }
    }


    protected function store()
    {
        $this->crud->hasAccessOrFail('create');

        // Execute the FormRequest authorization and validation
        $request = $this->crud->validateRequest();

        // Register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // Chuẩn bị dữ liệu trước khi tạo
        $strippedRequest = $this->crud->getStrippedSaveRequest($request);

        // Xử lý document_file_id
        // Lấy từ request (có thể từ field number hoặc hidden input)
        $documentFileId = $request->input('document_file_id');
        if ($documentFileId !== null && $documentFileId !== '') {
            $strippedRequest['document_file_id'] = (int)$documentFileId ?: null;
        } else {
            $strippedRequest['document_file_id'] = null;
        }

        // Log để debug (có thể xóa sau)
        Log::info('Post store - document_file_id', [
            'request_value' => $request->input('document_file_id'),
            'stripped_value' => $strippedRequest['document_file_id'] ?? 'not set',
            'all_request' => $request->all()
        ]);

        // Insert item in the db
        $item = $this->crud->create($strippedRequest);
        $this->data['entry'] = $this->crud->entry = $item;

        // Handle translations
        if ($item) {
            $item->handleTranslations($request->all());
        }

        // Show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // Save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    protected function update()
    {
        $this->crud->hasAccessOrFail('update');

        // Execute the FormRequest authorization and validation
        $request = $this->crud->validateRequest();

        // Register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // Chuẩn bị dữ liệu trước khi update
        $strippedRequest = $this->crud->getStrippedSaveRequest($request);

        // Xử lý document_file_id
        // Lấy từ request (có thể từ field number hoặc hidden input)
        $documentFileId = $request->input('document_file_id');

        // Log để debug
        Log::info('Post update - document_file_id DEBUG', [
            'request_all' => $request->all(),
            'request_has_document_file_id' => $request->has('document_file_id'),
            'request_input_document_file_id' => $request->input('document_file_id'),
            'stripped_request_keys' => array_keys($strippedRequest),
            'stripped_request_has_document_file_id' => isset($strippedRequest['document_file_id']),
            'entry_id' => $this->crud->getCurrentEntryId(),
            'current_entry_document_file_id' => $this->crud->getCurrentEntry()->document_file_id ?? null
        ]);

        // Xử lý document_file_id
        // Luôn set giá trị vào strippedRequest, kể cả khi là null
        if ($documentFileId !== null && $documentFileId !== '' && $documentFileId !== '0') {
            $strippedRequest['document_file_id'] = (int)$documentFileId;
        } else {
            // Nếu giá trị là null hoặc rỗng, set null
            $strippedRequest['document_file_id'] = null;
        }

        // Log giá trị cuối cùng
        Log::info('Post update - document_file_id FINAL', [
            'document_file_id_value' => $strippedRequest['document_file_id'] ?? 'not set',
            'will_save' => isset($strippedRequest['document_file_id'])
        ]);

        // Update the row in the db
        $item = $this->crud->update(
            $this->crud->getCurrentEntryId(),
            $strippedRequest
        );
        $this->data['entry'] = $this->crud->entry = $item;

        // Handle translations
        if ($item) {
            $item->handleTranslations($request->all());
        }

        // Show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // Save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * Lấy giá trị translation cho field
     */
    protected function getTranslationValue($lang, $field)
    {
        $entry = $this->crud->getCurrentEntry();
        if ($entry) {
            $translation = $entry->translations()->where('language', $lang)->first();
            return $translation ? $translation->$field : '';
        }
        return '';
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if (app(HomepageLayoutService::class)->isLayoutPost($post)) {
            Alert::error('Không thể xóa block sắp xếp trang chủ. Dùng menu Sắp xếp trang chủ.')->flash();

            return redirect()->to($this->crud->route);
        }

        return $this->traitDestroy($id);
    }
}
