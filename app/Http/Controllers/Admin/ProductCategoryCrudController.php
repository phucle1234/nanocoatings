<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductCategoryRequest;
use App\Traits\HasSlugGenerator;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

/**
 * Class ProductCategoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use HasSlugGenerator;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ProductCategory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product-category');
        CRUD::setEntityNameStrings('product category', 'product categories');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // Cột hiển thị
        CRUD::addColumn([
            'name' => 'row_number',
            'type' => 'row_number',
            'label' => 'STT',
            'orderable' => false,
        ]);
        CRUD::column('code')->label('Mã danh mục')->type('text');
        // Thành:
        CRUD::column('name')
            ->label('Tên danh mục')
            ->type('closure') // ✅ Đổi từ model_function sang closure
            ->function(function ($entry) {
                // ✅ Chỉ lấy tiếng Việt trong admin
                $translation = $entry->translations()->where('language', app()->getLocale())->first();
                return $translation ? $translation->name : $entry->code;
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                // ✅ Search theo code (không cần join)
                $query->where('code', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('translations', function ($q) use ($searchTerm) {
                        $q->where('language', 'vi') // ✅ Chỉ tìm trong tiếng Việt
                            ->where(function ($subQuery) use ($searchTerm) {
                                $subQuery->where('name', 'like', '%' . $searchTerm . '%')
                                    ->orWhere('slug', 'like', '%' . $searchTerm . '%');
                            });
                    });
            });

        CRUD::column('parent')
            ->label('Danh mục cha')
            ->type('closure') // ✅ Đổi từ model_function sang closure
            ->function(function ($entry) {
                if (!$entry->parent) {
                    return '-';
                }
                // ✅ Chỉ lấy tiếng Việt trong admin
                $translation = $entry->parent->translations()->where('language', app()->getLocale())->first();
                return $translation ? $translation->name : ($entry->parent->code ?? '-');
            });

        CRUD::column('post_category_link')
            ->label('Danh mục bài viết')
            ->type('closure')
            ->function(function ($entry) {
                $postCategoryId = null;
                $currentLocale = app()->getLocale();

                // ✅ Thử lấy slug của ProductCategory
                $productCategorySlug = null;

                // Cách 1: Thử qua accessor
                try {
                    $productCategorySlug = $entry->slug;
                } catch (\Exception $e) {
                    // Ignore
                }

                // Cách 2: Lấy trực tiếp từ translation nếu accessor không hoạt động
                if (!$productCategorySlug) {
                    $translation = $entry->translations()->where('language', $currentLocale)->first();
                    $productCategorySlug = $translation ? $translation->slug : null;
                }

                // ✅ Tìm PostCategory theo slug
                if ($productCategorySlug) {
                    $postCategoryTranslation = \App\Models\PostCategoryTranslation::where('slug', $productCategorySlug)
                        ->where('language', $currentLocale)
                        ->first();

                    if ($postCategoryTranslation) {
                        $postCategoryId = $postCategoryTranslation->postcategory_id;
                    }
                }

                // ✅ Tạo link nếu có ID
                if ($postCategoryId) {
                    $url = backpack_url('post?category_id=' . $postCategoryId);
                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-primary">
                <i class="la la-newspaper"></i> Xem bài trang sản phẩm
            </a>';
                }

                return '<span class="text-muted">-</span>';
            })
            ->escaped(false);

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('image')
            ->label('Hình ảnh')
            ->type('model_function')
            ->function_name('getImageThumbnailHtml')
            ->escaped(false);
        CRUD::column('slug')
            ->label('Slug')
            ->type('closure')
            ->function(function ($entry) {
                $translation = $entry->translations()->where('language', app()->getLocale())->first();
                return $translation ? $translation->slug : $entry->slug;
            });
        CRUD::column('is_active')->label('Trạng thái')->type('boolean');
        CRUD::column('is_featured')->label('Nổi bật')->type('boolean');
        CRUD::column('sort_order')->label('Thứ tự')->type('number');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        // Sắp xếp mặc định
        CRUD::orderBy('parent_id', 'asc');
        CRUD::orderBy('sort_order', 'asc');
        CRUD::orderBy('created_at', 'desc');
        CRUD::setOperationSetting('responsiveTable', false);
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductCategoryRequest::class);

        // Tab thông tin cơ bản
        CRUD::field('code')
            ->label('Mã danh mục')
            ->type('text')
            ->attributes([
                'placeholder' => 'VD: electronics, clothing, books'
            ])
            ->hint('Mã danh mục sẽ được sử dụng để tạo slug tự động')
            ->tab('Chung');

        // Tạo options cho danh mục cha
        $categories = \App\Models\ProductCategory::with('translation')
            ->where('is_active', true)
            ->get()
            ->pluck('translation.name', 'id')
            ->toArray();
        $options = ['' => 'Không có danh mục cha'] + $categories;

        CRUD::field('parent_id')
            ->label('Danh mục cha')
            ->type('select_from_array')
            ->options($options)
            ->allows_null(true)
            ->default(null)
            ->tab('Chung');

        // Slug chính được tạo tự động từ code, không cần field riêng

        // Tab cài đặt
        CRUD::field('is_active')
            ->label('Trạng thái hoạt động')
            ->type('boolean')
            ->default(true)
            ->tab('Chung');

        CRUD::field('is_featured')
            ->label('Danh mục nổi bật')
            ->type('boolean')
            ->default(false)
            ->tab('Chung');

        CRUD::field('sort_order')
            ->label('Thứ tự sắp xếp')
            ->type('number')
            ->default(0)
            ->attributes([
                'min' => 0
            ])
            ->tab('Chung');

        // Thêm các field đa ngôn ngữ với giao diện dễ quản lý
        $this->addMultilangFieldsWithSections();

        // Thêm slug generator (đơn lẻ)
        $this->addSlugGenerator('code', 'slug', false);

        // ✅ Thêm sync script cho Summernote
        CRUD::field('summernote_sync_view')
            ->type('view')
            ->view('vendor.backpack.crud.fields.summernote-sync')
            ->onlyOn(['create', 'edit']);
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
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
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // Execute the FormRequest authorization and validation
        $request = $this->crud->validateRequest();

        // Register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // Insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
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

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // Execute the FormRequest authorization and validation
        $request = $this->crud->validateRequest();

        // Register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // Update the row in the db
        $item = $this->crud->update(
            $this->crud->getCurrentEntryId(),
            $this->crud->getStrippedSaveRequest($request)
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
     * Define what happens when the Show operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->setupListOperation();

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('image')
            ->label('Hình ảnh')
            ->type('model_function')
            ->function_name('getImageLargeHtml')
            ->escaped(false);
    }


    /**
     * Thêm các field đa ngôn ngữ với giao diện dễ quản lý
     */
    protected function addMultilangFieldsWithSections()
    {
        $languages = config('languages.supported');

        foreach ($languages as $lang => $langName) {
            // Header cho từng ngôn ngữ
            CRUD::field('header_' . $lang)
                ->label('')
                ->type('view')
                ->view('vendor.backpack.crud.fields.language_header')
                ->data(['language' => $langName, 'code' => $lang])
                ->tab($langName);

            // Tên danh mục
            CRUD::field('name_' . $lang)
                ->label('Tên danh mục')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'name'))
                ->attributes([
                    'placeholder' => 'Nhập tên danh mục bằng ' . $langName
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
                ->hint('Thêm nhiều hình ảnh cho danh mục bằng ' . $langName . '. Hình đầu tiên dùng làm ảnh card trên website.')
                ->tab($langName);

            CRUD::field('link_type_' . $lang)
                ->label('Hành động khi click ảnh / Detail (' . $langName . ')')
                ->type('select_from_array')
                ->options([
                    'detail' => 'Mở trang chi tiết danh mục',
                    'youtube' => 'Mở video YouTube (xem ngay)',
                ])
                ->default($this->getTranslationValue($lang, 'link_type') ?: 'detail')
                ->tab($langName);

            CRUD::field('youtube_url_' . $lang)
                ->label('Link YouTube (' . $langName . ')')
                ->type('url')
                ->default($this->getTranslationValue($lang, 'youtube_url'))
                ->attributes([
                    'placeholder' => 'https://www.youtube.com/watch?v=...',
                ])
                ->hint('Chỉ dùng khi chọn "Mở video YouTube". Hỗ trợ link watch hoặc youtu.be.')
                ->tab($langName);

            // Mô tả với Summernote
            CRUD::field('description_' . $lang)
                ->label('Mô tả')
                ->type('summernote')
                ->default($this->getTranslationValue($lang, 'description'))
                ->options([
                    'height' => 200,
                    'toolbar' => [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'italic']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture']],
                        ['view', ['fullscreen', 'codeview']],
                    ]
                ])
                ->tab($langName);

            // Meta title
            CRUD::field('meta_title_' . $lang)
                ->label('Meta Title')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'meta_title'))
                ->attributes([
                    'placeholder' => 'Meta title cho SEO'
                ])
                ->tab($langName);

            // Meta description
            CRUD::field('meta_description_' . $lang)
                ->label('Meta Description')
                ->type('textarea')
                ->default($this->getTranslationValue($lang, 'meta_description'))
                ->attributes([
                    'rows' => 2,
                    'placeholder' => 'Meta description cho SEO'
                ])
                ->tab($langName);

            // Slug cho từng ngôn ngữ
            CRUD::field('slug_' . $lang)
                ->label('Slug')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'slug'))
                ->attributes([
                    'placeholder' => 'Sẽ được tạo tự động từ tên'
                ])
                ->tab($langName);

            // Separator
            CRUD::field('separator_' . $lang)
                ->label('')
                ->type('view')
                ->view('vendor.backpack.crud.fields.separator')
                ->tab($langName);
        }
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
}
