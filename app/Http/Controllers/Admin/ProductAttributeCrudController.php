<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductAttributeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

/**
 * Class ProductAttributeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductAttributeCrudController extends CrudController
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
        CRUD::setModel(\App\Models\ProductAttribute::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product-attribute');
        CRUD::setEntityNameStrings('product attribute', 'product attributes');
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
        CRUD::column('code')->label('Mã thuộc tính')->type('text');

        // Refactored: Dùng model_function thay vì closure
        CRUD::column('name')
            ->label('Tên thuộc tính')
            ->type('model_function')
            ->function_name('getTranslatedName');

        CRUD::column('type')->label('Loại')->type('select_from_array')->options([
            'text' => 'Text',
            'number' => 'Number',
            'select' => 'Select',
            'multiselect' => 'Multi Select',
            'boolean' => 'Boolean',
            'date' => 'Date',
            'textarea' => 'Textarea',
        ]);
        CRUD::column('is_required')->label('Bắt buộc')->type('boolean');
        CRUD::column('is_filterable')->label('Có thể lọc')->type('boolean');
        CRUD::column('is_comparable')->label('Có thể so sánh')->type('boolean');
        CRUD::column('is_active')->label('Trạng thái')->type('boolean');
        CRUD::column('sort_order')->label('Thứ tự')->type('number');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        // Sắp xếp mặc định
        CRUD::orderBy('sort_order', 'asc');
        CRUD::orderBy('created_at', 'desc');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductAttributeRequest::class);

        // Tab Chung - Thông tin cơ bản
        CRUD::field('code')
            ->label('Mã thuộc tính')
            ->type('text')
            ->attributes([
                'placeholder' => 'VD: color, size, material, brand'
            ])
            ->hint('Mã thuộc tính phải duy nhất, chỉ chứa chữ cái, số và dấu gạch dưới')
            ->tab('Chung');

        CRUD::field('type')
            ->label('Loại thuộc tính')
            ->type('select_from_array')
            ->options([
                'text' => 'Text - Văn bản ngắn',
                'number' => 'Number - Số',
                'select' => 'Select - Chọn một giá trị',
                'multiselect' => 'Multi Select - Chọn nhiều giá trị',
                'boolean' => 'Boolean - Có/Không',
                'date' => 'Date - Ngày tháng',
                'textarea' => 'Textarea - Văn bản dài',
            ])
            ->default('text')
            ->hint('Chọn loại thuộc tính phù hợp với dữ liệu')
            ->tab('Chung');

        // Cài đặt
        CRUD::field('is_required')
            ->label('Bắt buộc')
            ->type('boolean')
            ->default(false)
            ->hint('Thuộc tính này có bắt buộc phải có giá trị không?')
            ->tab('Chung');

        CRUD::field('is_filterable')
            ->label('Có thể lọc')
            ->type('boolean')
            ->default(true)
            ->hint('Khách hàng có thể lọc sản phẩm theo thuộc tính này không?')
            ->tab('Chung');

        CRUD::field('is_comparable')
            ->label('Có thể so sánh')
            ->type('boolean')
            ->default(true)
            ->hint('Thuộc tính này có thể dùng để so sánh sản phẩm không?')
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

        // Options cho select/multiselect
        CRUD::field('options')
            ->label('Tùy chọn (cho Select/Multi Select)')
            ->type('textarea')
            ->attributes([
                'rows' => 5,
                'placeholder' => 'Mỗi tùy chọn trên một dòng, VD:' . "\n" . 'Đỏ' . "\n" . 'Xanh' . "\n" . 'Vàng'
            ])
            ->hint('Chỉ cần điền khi chọn loại Select hoặc Multi Select')
            ->tab('Chung');

        // Thêm các field đa ngôn ngữ với Tab structure
        $this->addMultilangFieldsWithSections();
        CRUD::setOperationSetting('responsiveTable', false);
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

        // Xử lý options field
        $data = $this->crud->getStrippedSaveRequest($request);
        if (!empty($data['options'])) {
            $options = array_filter(array_map('trim', explode("\n", $data['options'])));
            $data['options'] = json_encode($options);
        } else {
            $data['options'] = null;
        }

        // Insert item in the db
        $item = $this->crud->create($data);
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

        // Xử lý options field
        $data = $this->crud->getStrippedSaveRequest($request);
        if (!empty($data['options'])) {
            $options = array_filter(array_map('trim', explode("\n", $data['options'])));
            $data['options'] = json_encode($options);
        } else {
            $data['options'] = null;
        }

        // Update the row in the db
        $item = $this->crud->update(
            $this->crud->getCurrentEntryId(),
            $data
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
        CRUD::column('options')
            ->label('Tùy chọn')
            ->type('model_function')
            ->function_name('getOptionsHtml')
            ->escaped(false);
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
     * Thêm các field đa ngôn ngữ với Tab structure (giống ProductCrudController)
     */
    protected function addMultilangFieldsWithSections()
    {
        $languages = config('languages.supported');

        foreach ($languages as $lang => $langName) {
            // Tên thuộc tính
            CRUD::field('name_' . $lang)
                ->label('Tên thuộc tính')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'name'))
                ->attributes([
                    'placeholder' => 'Nhập tên thuộc tính bằng ' . $langName
                ])
                ->tab($langName);

            // Mô tả thuộc tính
            CRUD::field('description_' . $lang)
                ->label('Mô tả')
                ->type('textarea')
                ->default($this->getTranslationValue($lang, 'description'))
                ->attributes([
                    'rows' => 3,
                    'placeholder' => 'Mô tả ngắn về thuộc tính này'
                ])
                ->tab($langName);
        }
    }
}
