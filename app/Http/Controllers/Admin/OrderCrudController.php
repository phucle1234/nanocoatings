<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\OrderRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

/**
 * Class OrderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class OrderCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Order::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/order');
        CRUD::setEntityNameStrings('đơn hàng', 'đơn hàng');
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
        CRUD::column('order_number')->label('Mã đơn hàng');

        CRUD::column('user.name')->label('Khách hàng');
        CRUD::column('user.email')->label('Email khách hàng');
        CRUD::column('dealer_code')->label('Mã đại lý')->type('text');
        CRUD::column('total_amount')->label('Tổng tiền')->type('number');
        CRUD::column('notes')->label('Ghi chú')->type('textarea');
        CRUD::column('status')->label('status')->type('number');
        CRUD::column('type')->label('type')->type('text');
        CRUD::column('cancel_reason')->label('cancel_reason')->type('text');


        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        // Sắp xếp mặc định
        CRUD::orderBy('created_at', 'desc');
        CRUD::setOperationSetting('responsiveTable', false);
    }

    /**
     * Define what happens when the Show operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation()
    {
        // Dùng view tùy biến
        $this->crud->setShowView('vendor.backpack.crud.operations.detail_order_show_custom_table');

        // Chủ động định nghĩa cột hiển thị
        $this->crud->set('show.setFromDb', false);

        // --- Nhóm: Order info (hiển thị theo grid) ---
        $this->crud->addColumn(['name' => 'order_number',    'label' => 'Mã đơn hàng',       'type' => 'text']);
        $this->crud->addColumn(['name' => 'status',           'label' => 'Trạng thái',        'type' => 'text']);
        $this->crud->addColumn(['name' => 'payment_status',   'label' => 'TT Thanh toán',     'type' => 'text']);
        $this->crud->addColumn(['name' => 'currency',         'label' => 'Tiền tệ',           'type' => 'text']);
        $this->crud->addColumn(['name' => 'subtotal',         'label' => 'Subtotal',          'type' => 'number', 'suffix' => '']);
        $this->crud->addColumn(['name' => 'discount_amount',  'label' => 'Giảm giá',          'type' => 'number']);
        $this->crud->addColumn(['name' => 'tax_amount',       'label' => 'Thuế',              'type' => 'number']);
        $this->crud->addColumn(['name' => 'shipping_amount',  'label' => 'Phí vận chuyển',    'type' => 'number']);
        $this->crud->addColumn(['name' => 'grand_total',      'label' => 'Tổng thanh toán',   'type' => 'number']);
        $this->crud->addColumn(['name' => 'created_at',       'label' => 'Ngày tạo',          'type' => 'datetime']);
        $this->crud->addColumn(['name' => 'updated_at',       'label' => 'Cập nhật',          'type' => 'datetime']);

        // User (đọc bằng dot-notation trong Blade)
        $this->crud->addColumn(['name' => 'user.name',        'label' => 'Khách hàng',        'type' => 'text']);
        $this->crud->addColumn(['name' => 'user.email',       'label' => 'Email',             'type' => 'text']);
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        CRUD::setValidation(OrderRequest::class);

        // Chỉ cho phép sửa các trường này
        CRUD::field('status')->label('Trạng thái')
            ->type('select_from_array')
            ->options([
                'pending' => 'Chờ xử lý',
                'processing' => 'Đang xử lý',
                'shipped' => 'Đã giao hàng',
                'delivered' => 'Đã nhận hàng',
                'cancelled' => 'Đã hủy',
            ])
            ->default('pending');

        CRUD::field('payment_status')->label('Trạng thái thanh toán')
            ->type('select_from_array')
            ->options([
                'pending' => 'Chờ thanh toán',
                'paid' => 'Đã thanh toán',
                'failed' => 'Thanh toán thất bại',
                'refunded' => 'Đã hoàn tiền',
                'cancelled' => 'Đã hủy',
            ])
            ->default('pending');

        CRUD::field('notes')->label('Ghi chú')
            ->type('textarea')
            ->attributes(['rows' => 4]);

        CRUD::field('shipped_at')->label('Ngày giao hàng')
            ->type('datetime');

        CRUD::field('delivered_at')->label('Ngày nhận hàng')
            ->type('datetime');
    }

    /**
     * Override update method to handle custom logic
     */
    protected function update()
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

        // Show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // Save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}
