<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use App\Traits\HasSlugGenerator;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product');
        CRUD::setEntityNameStrings('sản phẩm', 'sản phẩm');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // Eager load bản dịch tiếng Việt để hiển thị tên
        $this->crud->addClause('with', ['translations' => function ($query) {
            $query->where('language', 'vi');
        }]);

        // Cột hiển thị với đa ngôn ngữ
        CRUD::addColumn([
            'name' => 'row_number',
            'type' => 'row_number',
            'label' => 'STT',
            'orderable' => false,
        ]);
        CRUD::column('sku')->label('Mã SP')->type('text')->searchLogic(false);

        // Hiển thị tên sản phẩm tiếng Việt
        CRUD::column('name_vi')
            ->label('Tên sản phẩm')
            ->type('model_function')
            ->function_name('getNameVi')
            ->searchLogic(function ($query, $column, $searchTerm) {
                // ✅ Tìm trong cột text_search
                $query->whereHas('translations', function ($q) use ($searchTerm) {
                    $q->where('text_search', 'like', '%' . $searchTerm . '%');
                });
            });



        CRUD::column('category_names')
            ->label('Danh mục')
            ->type('closure')
            ->function(function ($entry) {
                return $entry->getCategoryNamesVi();
            })
            ->priority(1);  // ✅ GIỮ PRIORITY!

        CRUD::column('price')->label('Giá bán')->type('number')->decimals(0)->prefix('₫')->priority(2);
        CRUD::column('sale_price')->label('Giá KM')->type('number')->decimals(0)->prefix('₫')->priority(3);
        CRUD::column('stock_quantity')->label('Tồn kho')->type('number')->priority(4);
        CRUD::column('is_bestseller')->label('Bán chạy')->type('select_from_array')
            ->options([0 => 'Không', 1 => 'Có'])
            ->priority(5);

        // Thêm cột số lượng fitments
        CRUD::column('fitments_count')
            ->label('Fitments')
            ->type('closure')
            ->function(function ($entry) {
                $count = \App\Models\ProductVehicleFitment::where('product_id', $entry->id)->count();
                return $count > 0 ? "<span class='badge badge-success'>{$count}</span>" : "<span class='badge badge-secondary'>0</span>";
            })
            ->escaped(false)
            ->priority(6);

        CRUD::setOperationSetting('responsiveTable', false);

        CRUD::orderBy('category_id', 'asc');
        CRUD::button('preview')->remove();
        CRUD::addButtonFromView('line', 'custom_link_detail_product_admin', 'custom_link_detail_product_admin', 'beginning');

        // ✅ Button để manage vehicle fitments
        CRUD::addButtonFromView('line', 'manage_fitments', 'manage_fitments', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductRequest::class);

        // Thông tin cơ bản - Multiple Categories (Pivot Table)
        CRUD::field('categories')->label('Danh mục sản phẩm')->type('select_multiple')
            ->entity('categories')
            ->attribute('name')
            ->model('App\Models\ProductCategory')
            ->pivot(true)
            ->tab('Chung');

        // Hidden field để đảm bảo category_id có giá trị mặc định
        CRUD::field('category_id')
            ->type('hidden')
            ->default(null)
            ->tab('Chung');



        CRUD::field('sku')->label('Mã sản phẩm')->type('text')
            ->hint('Mã sản phẩm duy nhất')
            ->tab('Chung');

        CRUD::field('code')->label('Mã sản phẩm')->type('text')
            ->hint('Mã sản phẩm ')
            ->tab('Chung');

        CRUD::field('price')->label('Giá bán')->type('number')
            ->attributes(['step' => '0.01', 'min' => '0'])
            ->prefix('₫')
            ->tab('Chung');

        CRUD::field('sale_price')->label('Giá khuyến mãi')->type('number')
            ->attributes(['step' => '0.01', 'min' => '0'])
            ->prefix('₫')
            ->hint('Để trống nếu không có khuyến mãi')
            ->tab('Chung');
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
                'context' => 'product'
            ])
            ->hint('Tải lên tài liệu PDF hỗ trợ sản phẩm (tối đa 30MB)')
            ->tab('Chung');
        CRUD::field('stock_quantity')->label('Số lượng tồn kho')->type('number')
            ->attributes(['min' => '0'])
            ->tab('Chung');

        CRUD::field('min_stock_quantity')->label('Số lượng tồn kho tối thiểu')->type('number')
            ->attributes(['min' => '0'])
            ->default(0)
            ->hint('Cảnh báo khi tồn kho thấp hơn số này')
            ->tab('Chung');

        // Trạng thái sản phẩm
        CRUD::field('is_active')->label('Hoạt động')->type('boolean')
            ->default(true)
            ->tab('Chung');
        CRUD::field('is_featured')->label('Sản phẩm nổi bật')->type('boolean')
            ->default(false)
            ->tab('Chung');
        CRUD::field('is_new')->label('Sản phẩm mới')->type('boolean')
            ->default(false)
            ->tab('Chung');
        CRUD::field('is_bestseller')->label('Sản phẩm bán chạy')->type('boolean')
            ->default(false)
            ->tab('Chung');
        CRUD::field('sort_order')->label('Thứ tự sắp xếp')->type('number')
            ->attributes(['min' => '0'])
            ->default(0)
            ->tab('Chung');

        // Hình ảnh sản phẩm
        CRUD::field('image_urls')
            ->label('Hình ảnh sản phẩm')
            ->type('view')
            ->view('components.multiple-images')
            ->hint('Upload nhiều hình ảnh cho sản phẩm')
            ->tab('Chung');

        // Thuộc tính sản phẩm (tạm thời comment để tránh lỗi)
        CRUD::field('product_attributes')
            ->label('Thuộc tính sản phẩm')
            ->type('view')
            ->view('vendor.backpack.crud.fields.product_attributes')
            ->hint('Quản lý các thuộc tính của sản phẩm')
            ->tab('Chung');

        // Thông tin đa ngôn ngữ
        $this->addMultilangFieldsWithSections();
        $this->addSlugGenerator('name_vi', 'slug', false);

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


        // Đảm bảo field image_urls có giá trị mặc định
        CRUD::modifyField('image_urls', [
            'default' => $this->crud->getCurrentEntry()->image_urls ?? []
        ]);

        // Đảm bảo field categories có giá trị mặc định từ pivot table
        $currentEntry = $this->crud->getCurrentEntry();
        $currentCategoryIds = $currentEntry->getCategoryIdsFromPivot();
        if (empty($currentCategoryIds) && $currentEntry->category_id) {
            $currentCategoryIds = [$currentEntry->category_id];
        }

        CRUD::modifyField('categories', [
            'default' => $currentCategoryIds
        ]);

        // Đảm bảo field document_file_id có giá trị mặc định từ entry
        CRUD::modifyField('document_file_id', [
            'default' => $currentEntry->document_file_id ?? null
        ]);

        $languages = array_keys(config('languages.supported'));
        foreach ($languages as $lang) {
            $translation = $currentEntry->translations()->where('language', $lang)->first();
            $imageUrls = $translation ? $translation->image_urls : [];
            $defaultValue = is_array($imageUrls) ? implode("\n", $imageUrls) : (string) ($imageUrls ?? '');

            CRUD::modifyField('image_urls_' . $lang, [
                'default' => $defaultValue,
                'data' => ['value' => $defaultValue],
            ]);
        }
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
        CRUD::column('image_urls')
            ->label('Hình ảnh')
            ->type('model_function')
            ->function_name('getImageUrlsHtml')
            ->escaped(false);

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('description')
            ->label('Mô tả')
            ->type('model_function')
            ->function_name('getDescriptionDisplay');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('short_description')
            ->label('Mô tả ngắn')
            ->type('model_function')
            ->function_name('getShortDescriptionDisplay');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('outstanding_features')
            ->label('Tính năng nổi bật')
            ->type('model_function')
            ->function_name('getOutstandingFeaturesDisplay');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('meta_title')
            ->label('Meta Title')
            ->type('model_function')
            ->function_name('getMetaTitleDisplay');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('meta_description')
            ->label('Meta Description')
            ->type('model_function')
            ->function_name('getMetaDescriptionDisplay');
    }



    /**
     * Thêm các field đa ngôn ngữ với giao diện dễ quản lý
     */
    protected function addMultilangFieldsWithSections()
    {
        $languages = config('languages.supported');

        foreach ($languages as $lang => $langName) {
            // Tên sản phẩm
            CRUD::field('name_' . $lang)
                ->label('Tên sản phẩm')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'name'))
                ->attributes([
                    'placeholder' => 'Nhập tên sản phẩm bằng ' . $langName,
                    'data-slug-target' => 'slug_' . $lang
                ])
                ->tab($langName);

            // Slug cho từng ngôn ngữ
            CRUD::field('slug_' . $lang)
                ->label('Slug')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'slug'))
                ->attributes([
                    'placeholder' => 'Sẽ được tạo tự động từ tên',
                    'data-slug-field' => 'true'
                ])
                ->tab($langName);

            $entry = $this->crud->getCurrentEntry();
            $translation = $entry ? $entry->translations()->where('language', $lang)->first() : null;
            $translationImageUrls = $translation ? $translation->image_urls : [];
            $translationImageDefault = is_array($translationImageUrls)
                ? implode("\n", $translationImageUrls)
                : (string) ($translationImageUrls ?? '');

            CRUD::field('image_urls_' . $lang)
                ->label('Hình ảnh theo ngôn ngữ (' . $langName . ')')
                ->type('view')
                ->view('components.multiple-images')
                ->default($translationImageDefault)
                ->data(['value' => $translationImageDefault])
                ->hint('Tuỳ chọn: ảnh riêng cho ' . $langName . '. Để trống thì vẫn dùng hình ở tab Chung (products.image_urls).')
                ->tab($langName);

            // Mô tả ngắn
            CRUD::field('short_description_' . $lang)
                ->label('Mô tả ngắn')
                ->type('textarea')
                ->default($this->getTranslationValue($lang, 'short_description'))
                ->attributes([
                    'rows' => 3,
                    'placeholder' => 'Mô tả ngắn về sản phẩm'
                ])
                ->tab($langName);

            CRUD::field('features_' . $lang)
                ->label('Tính năng sản phẩm')
                ->type('summernote')
                ->default($this->getTranslationValue($lang, 'features'))
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

            // Mô tả chi tiết với Summernote
            CRUD::field('description_' . $lang)
                ->label('Mô tả chi tiết')
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

            // Meta keywords
            CRUD::field('meta_keywords_' . $lang)
                ->label('Meta Keywords')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'meta_keywords'))
                ->attributes([
                    'placeholder' => 'Các từ khóa cách nhau bởi dấu phẩy'
                ])
                ->tab($langName);

            // Canonical URL
            CRUD::field('canonical_url_' . $lang)
                ->label('Canonical URL')
                ->type('url')
                ->default($this->getTranslationValue($lang, 'canonical_url'))
                ->attributes([
                    'placeholder' => 'URL chuẩn cho ' . $langName
                ])
                ->tab($langName);

            // Open Graph Fields
            CRUD::field('og_title_' . $lang)
                ->label('OG Title')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'og_title'))
                ->attributes([
                    'placeholder' => 'Tiêu đề chia sẻ mạng xã hội cho ' . $langName
                ])
                ->tab($langName);

            CRUD::field('og_description_' . $lang)
                ->label('OG Description')
                ->type('textarea')
                ->default($this->getTranslationValue($lang, 'og_description'))
                ->attributes([
                    'rows' => 2,
                    'placeholder' => 'Mô tả chia sẻ mạng xã hội cho ' . $langName
                ])
                ->tab($langName);

            CRUD::field('og_image_' . $lang)
                ->label('OG Image')
                ->type('url')
                ->default($this->getTranslationValue($lang, 'og_image'))
                ->attributes([
                    'placeholder' => 'Hình ảnh chia sẻ mạng xã hội cho ' . $langName
                ])
                ->tab($langName);

            // Thêm JavaScript để auto generate slug (sử dụng addSingleSlugGenerator)
            $this->addSlugGenerator('name_' . $lang, 'slug_' . $lang, false);
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
            if ($translation) {
                $value = $translation->{$field};
                // Đảm bảo luôn trả về string, không phải array
                if (is_array($value)) {
                    return json_encode($value);
                }
                return $value ?: '';
            }
        }
        return '';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // Execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // Register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // Chuẩn bị dữ liệu trước khi tạo
        $strippedRequest = $this->crud->getStrippedSaveRequest($request);
        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = is_array($request->categories) ? $request->categories : [$request->categories];
            $strippedRequest['category_id'] = $categoryIds[0]; // Set category_id từ categories đầu tiên
        } else {
            // Nếu không có categories, set mặc định là 1 (hoặc null nếu cho phép)
            $strippedRequest['category_id'] = $strippedRequest['category_id'] ?? 1;
        }
        // Xử lý image_urls từ textarea
        if ($request->has('image_urls')) {
            $imageUrls = $request->image_urls;
            if (is_string($imageUrls)) {
                $urls = array_filter(array_map('trim', explode("\n", $imageUrls)));
                $strippedRequest['image_urls'] = $urls;
            }
        }
        // Xử lý document_file_id (từ hidden input trong view field)
        if ($request->has('document_file_id')) {
            $strippedRequest['document_file_id'] = $request->input('document_file_id') ?: null;
        }
        // Insert item in the db
        $item = $this->crud->create($strippedRequest);
        $this->data['entry'] = $this->crud->entry = $item;

        // Xử lý translations
        $item->handleTranslations($request->all());

        // Xử lý multiple categories với pivot table
        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = is_array($request->categories) ? $request->categories : [$request->categories];
            $primaryCategoryId = $categoryIds[0]; // Danh mục đầu tiên là chính

            $item->syncCategories($categoryIds, $primaryCategoryId);
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

        // Execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // Register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // Chuẩn bị dữ liệu trước khi update
        $strippedRequest = $this->crud->getStrippedSaveRequest($request);
        // Xử lý image_urls từ textarea
        if ($request->has('image_urls')) {
            $imageUrls = $request->image_urls;
            if (is_string($imageUrls)) {
                $urls = array_filter(array_map('trim', explode("\n", $imageUrls)));
                $strippedRequest['image_urls'] = $urls;
            }
        }
        // Xử lý document_file_id (từ hidden input trong view field)
        if ($request->has('document_file_id')) {
            $strippedRequest['document_file_id'] = $request->input('document_file_id') ?: null;
        }

        // Update the row in the db
        $item = $this->crud->update(
            $this->crud->getCurrentEntryId(),
            $strippedRequest
        );
        $this->data['entry'] = $this->crud->entry = $item;

        // Xử lý translations
        $item->handleTranslations($request->all());

        // Xử lý multiple categories với pivot table
        if ($request->has('categories')) {
            if (!empty($request->categories)) {
                $categoryIds = is_array($request->categories) ? $request->categories : [$request->categories];
                $primaryCategoryId = $categoryIds[0]; // Danh mục đầu tiên là chính

                $item->syncCategories($categoryIds, $primaryCategoryId);
            } else {
                // Xóa tất cả categories
                $item->syncCategories([]);
            }
        }

        // Show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // Save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * API: Lấy danh sách giá trị thuộc tính
     */
    public function getAttributeValues($attributeId)
    {
        try {
            $attribute = \App\Models\ProductAttribute::findOrFail($attributeId);
            $values = $attribute->activeValues()
                ->with(['translations' => function ($query) {
                    $query->where('language', 'vi');
                }])
                ->get();

            $result = $values->map(function ($value) {
                // Lấy translation tiếng Việt
                $translation = $value->translations->first();
                $displayName = $translation ? $translation->value : $value->value;
                // Hiển thị tên đặc điểm, không cần hiển thị mã trong ngoặc
                $displayText = $displayName;

                return [
                    'id' => $value->id,
                    'name' => $displayName,
                    'text' => $displayText,
                    'value' => $value->value
                ];
            })->values();

            return response()->json($result->all());
        } catch (\Exception $e) {
            Log::error('Error getting attribute values: ' . $e->getMessage(), [
                'attribute_id' => $attributeId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Không thể lấy danh sách giá trị: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Thêm thuộc tính cho sản phẩm
     */
    public function addProductAttribute(\Illuminate\Http\Request $request)
    {
        try {
            // Xử lý values nếu là JSON string
            $values = $request->values;
            if (is_string($values)) {
                $values = json_decode($values, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dữ liệu values không hợp lệ'
                    ], 400);
                }
            }

            // Validate
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'attribute_id' => 'required|exists:product_attributes,id',
            ]);

            // Validate values array riêng
            if (!is_array($values) || empty($values)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một giá trị thuộc tính'
                ], 400);
            }

            // Validate từng value ID
            foreach ($values as $valueId) {
                if (!\App\Models\ProductAttributeValue::where('id', $valueId)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Giá trị thuộc tính không tồn tại'
                    ], 400);
                }
            }

            $productId = $request->product_id;
            $attributeId = $request->attribute_id;
            $valueIds = $values;
            $showDetail = $request->get('show_detail', 'N'); // ✅ Lấy show_detail (mặc định 'N')

            // Validate show_detail
            if (!in_array($showDetail, ['Y', 'N'])) {
                $showDetail = 'N';
            }

            // Lấy sản phẩm
            $product = \App\Models\Product::findOrFail($productId);

            // ✅ Gán các giá trị thuộc tính với show_detail
            foreach ($valueIds as $valueId) {
                // Kiểm tra xem đã tồn tại chưa
                $exists = DB::table('product_attribute_product')
                    ->where('product_id', $productId)
                    ->where('attribute_value_id', $valueId)
                    ->exists();

                if ($exists) {
                    // Cập nhật show_detail nếu đã tồn tại
                    DB::table('product_attribute_product')
                        ->where('product_id', $productId)
                        ->where('attribute_value_id', $valueId)
                        ->update(['show_detail' => $showDetail]);
                } else {
                    // Thêm mới với show_detail
                    $product->attributeValues()->attach($valueId, [
                        'show_detail' => $showDetail,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm thuộc tính thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Xóa thuộc tính khỏi sản phẩm
     */
    public function removeProductAttribute(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'attribute_id' => 'required|exists:product_attributes,id'
            ]);

            $productId = $request->product_id;
            $attributeId = $request->attribute_id;

            // Lấy sản phẩm
            $product = \App\Models\Product::findOrFail($productId);

            // Lấy tất cả giá trị thuộc tính của thuộc tính này
            $valueIds = \App\Models\ProductAttributeValue::where('attribute_id', $attributeId)
                ->pluck('id')
                ->toArray();

            // Xóa tất cả giá trị thuộc tính khỏi sản phẩm
            $product->attributeValues()->detach($valueIds);

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa thuộc tính thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
