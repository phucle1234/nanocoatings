<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductAttributeValueRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

/**
 * Class ProductAttributeValueCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductAttributeValueCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ProductAttributeValue::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product-attribute-value');
        CRUD::setEntityNameStrings('product attribute value', 'product attribute values');
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
        CRUD::column('id')->label('ID')->type('number');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('attribute')
            ->label('Thuộc tính')
            ->type('model_function')
            ->function_name('getAttributeNameMultilang');

        CRUD::column('value')->label('Giá trị gốc')->type('text');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('localized_value')
            ->label('Giá trị hiện tại')
            ->type('model_function')
            ->function_name('getLocalizedValueDisplay');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('color_code')
            ->label('Mã màu')
            ->type('model_function')
            ->function_name('getColorCodeHtml')
            ->escaped(false);

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('image_url')
            ->label('Hình ảnh')
            ->type('model_function')
            ->function_name('getImageUrlHtml')
            ->escaped(false);

        CRUD::column('is_active')->label('Trạng thái')->type('boolean');
        CRUD::column('sort_order')->label('Thứ tự')->type('number');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        // Sắp xếp mặc định
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
        CRUD::setValidation(ProductAttributeValueRequest::class);

        // Tab Chung - Lấy danh sách attributes
        $attributes = \App\Models\ProductAttribute::with('translation')
            ->where('is_active', true)
            ->get()
            ->pluck('translation.name', 'id')
            ->toArray();

        CRUD::field('attribute_id')
            ->label('Thuộc tính')
            ->type('select_from_array')
            ->options($attributes)
            ->hint('Chọn thuộc tính mà giá trị này thuộc về')
            ->tab('Chung');
        CRUD::field('vehicle_type')
            ->label('Loại xe')
            ->type('select_from_array')
            ->options([
                'all' => 'Dùng chung',
                'oto' => 'Ô tô',
                'xe-may' => 'Xe máy',
                'xe-tai' => 'Xe tải',

            ])
            ->default('all')
            ->hint('Giá trị này chỉ áp dụng cho loại xe đã chọn')
            ->tab('Chung');
        CRUD::field('value')
            ->label('Giá trị gốc')
            ->type('text')
            ->attributes([
                'placeholder' => 'VD: Đỏ, XL, Cotton, 2024'
            ])
            ->hint('Giá trị cơ bản, không phụ thuộc ngôn ngữ')
            ->tab('Chung');


        CRUD::field('is_active')
            ->label('Trạng thái hoạt động')
            ->type('boolean')
            ->default(true)
            ->tab('Chung');

        CRUD::field('sort_order')
            ->label('Thứ tự sắp xếp')
            ->type('number')
            ->default(0)
            ->attributes([
                'min' => 0
            ])
            ->hint('Số càng nhỏ càng hiển thị trước')
            ->tab('Chung');

        // Thêm các field đa ngôn ngữ với Tab structure
        $this->addMultilangFieldsWithSections();
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
     * Thêm các field đa ngôn ngữ với Tab structure (giống ProductAttributeCrudController)
     */
    protected function addMultilangFieldsWithSections()
    {
        $languages = config('languages.supported');

        foreach ($languages as $lang => $langName) {
            // Giá trị theo ngôn ngữ
            CRUD::field('value_' . $lang)
                ->label('Giá trị')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'value'))
                ->attributes([
                    'placeholder' => 'Nhập giá trị bằng ' . $langName
                ])
                ->tab($langName);
        }
    }
}
