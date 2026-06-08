<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductVehicleFitmentRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ProductVehicleFitmentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductVehicleFitmentCrudController extends CrudController
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
        CRUD::setModel(\App\Models\ProductVehicleFitment::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product-vehicle-fitment');
        CRUD::setEntityNameStrings('vehicle fitment', 'vehicle fitments');

        // ✅ Filter theo product_id từ query string
        if (request()->has('product_id')) {
            $this->crud->addClause('where', 'product_id', request()->get('product_id'));
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // Eager load product
        $this->crud->addClause('with', 'product');

        // Columns
        CRUD::column('id')->label('ID')->type('number');

        CRUD::column('product_sku')
            ->label('Sản phẩm (SKU)')
            ->type('closure')
            ->function(function ($entry) {
                return $entry->product ? $entry->product->sku : 'N/A';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas('product', function ($q) use ($searchTerm) {
                    $q->where('sku', 'like', '%' . $searchTerm . '%');
                });
            });

        CRUD::column('manufacturer')
            ->label('Hãng xe')
            ->type('text')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('manufacturer', 'like', '%' . $searchTerm . '%');
            });

        CRUD::column('model')
            ->label('Mẫu xe')
            ->type('text')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('model', 'like', '%' . $searchTerm . '%');
            });

        CRUD::column('year')
            ->label('Năm')
            ->type('text');

        CRUD::column('trim')
            ->label('Phiên bản')
            ->type('text');

        CRUD::column('is_verified')
            ->label('Đã xác nhận')
            ->type('select_from_array')
            ->options([0 => 'Chưa', 1 => 'Đã xác nhận']);

        CRUD::column('fitment_display')
            ->label('Vehicle Info')
            ->type('model_function')
            ->function_name('getFitmentDisplayAttribute');

        CRUD::column('created_at')
            ->label('Ngày tạo')
            ->type('closure')
            ->function(function ($entry) {
                return $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : 'N/A';
            });

        // ❌ KHÔNG dùng filters vì ProductCrudController không có
        // User có thể dùng search box để tìm theo manufacturer/model

        // Default order
        CRUD::orderBy('manufacturer', 'asc');
        CRUD::orderBy('model', 'asc');
        CRUD::orderBy('year', 'desc');
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
        CRUD::setValidation(ProductVehicleFitmentRequest::class);


        // Product selection
        $products = \App\Models\Product::orderBy('sku', 'ASC')
            ->pluck('sku', 'id')
            ->toArray();

        // ✅ CHECK: Nếu có product_id trong URL
        $productIdFromUrl = request()->get('product_id');

        if ($productIdFromUrl && isset($products[$productIdFromUrl])) {
            // Có product_id trong URL → Hiển thị readonly text
            CRUD::field('product_sku_display')
                ->label('Sản phẩm')
                ->type('text')
                ->default($products[$productIdFromUrl])
                ->attributes(['readonly' => 'readonly', 'disabled' => 'disabled'])
                ->hint('Sản phẩm được chọn từ danh sách');

            // Hidden field chứa product_id thực
            CRUD::field('product_id')
                ->type('hidden')
                ->value($productIdFromUrl)
                ->default($productIdFromUrl);
        } else {
            // Không có product_id → Cho chọn dropdown
            CRUD::field('product_id')
                ->label('Sản phẩm (SKU)')
                ->type('select_from_array')
                ->options($products)
                ->allows_null(false)
                ->hint('Chọn sản phẩm theo SKU');
        }

        // ✅ LẤY MANUFACTURERS từ product_attribute_values
        $manufacturers = \DB::table('product_attribute_values as pav')
            ->join('product_attributes as pa', 'pav.attribute_id', '=', 'pa.id')
            ->join('product_attribute_value_translations as pavt', 'pav.id', '=', 'pavt.attribute_value_id')
            ->where('pa.code', 'manufacturer')
            ->where('pavt.language', 'vi')
            ->where('pav.is_active', true)
            ->orderBy('pavt.value')
            ->pluck('pavt.value', 'pavt.value')
            ->toArray();

        CRUD::field('manufacturer')
            ->label('Hãng xe')
            ->type('select_from_array')
            ->options(array_merge(['' => '- Chọn hãng xe -'], $manufacturers))
            ->allows_null(true)
            ->hint('Chọn hãng xe từ danh sách có sẵn.');


        // ✅ LẤY MODELS từ product_attribute_values
        $models = \DB::table('product_attribute_values as pav')
            ->join('product_attributes as pa', 'pav.attribute_id', '=', 'pa.id')
            ->join('product_attribute_value_translations as pavt', 'pav.id', '=', 'pavt.attribute_value_id')
            ->where('pa.code', 'model')
            ->where('pavt.language', 'vi')
            ->where('pav.is_active', true)
            ->orderBy('pavt.value')
            ->pluck('pavt.value', 'pavt.value')
            ->toArray();

        CRUD::field('model')
            ->label('Mẫu xe')
            ->type('select_from_array')
            ->options(array_merge(['' => '- Chọn mẫu xe -'], $models))
            ->allows_null(true)
            ->hint('Chọn mẫu xe từ danh sách có sẵn. Để trống nếu fit tất cả models.');

        // ✅ LẤY YEARS từ product_attribute_values
        $years = \DB::table('product_attribute_values as pav')
            ->join('product_attributes as pa', 'pav.attribute_id', '=', 'pa.id')
            ->join('product_attribute_value_translations as pavt', 'pav.id', '=', 'pavt.attribute_value_id')
            ->where('pa.code', 'production_year')
            ->where('pavt.language', 'vi')
            ->where('pav.is_active', true)
            ->orderByDesc('pavt.value')
            ->pluck('pavt.value', 'pavt.value')
            ->toArray();

        CRUD::field('year')
            ->label('Năm sản xuất')
            ->type('select_from_array')
            ->options(array_merge(['' => '- Chọn năm -'], $years))
            ->allows_null(true)
            ->hint('Chọn năm sản xuất từ danh sách. Để trống nếu fit tất cả năm.');

        // // Trim - text field
        // CRUD::field('trim')
        //     ->label('Phiên bản')
        //     ->type('text')
        //     ->hint('Ví dụ: LX, EX, Sport. Tùy chọn.');


        CRUD::field('is_verified')
            ->label('Đã xác nhận')
            ->type('boolean')
            ->default(true)
            ->hint('Đánh dấu nếu fitment này đã được xác nhận chính xác.');

        CRUD::field('notes')
            ->label('Ghi chú')
            ->type('textarea')
            ->attributes(['rows' => 3])
            ->hint('Ghi chú thêm về fitment này.');
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
}
