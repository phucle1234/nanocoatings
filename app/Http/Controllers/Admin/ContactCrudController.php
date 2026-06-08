<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminContactRequest;
use App\Models\Contact;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * CRUD quản lý contact (bảng contacts).
 */
class ContactCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(Contact::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/contact');
        CRUD::setEntityNameStrings('liên hệ', 'liên hệ');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id')->label('ID')->type('number');

        CRUD::column('Type')
            ->label('Loại')
            ->type('closure')
            ->function(function ($entry) {
                return $this->typeLabel((int) ($entry->Type ?? 0));
            });

        CRUD::column('Fullname')->label('Họ tên')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('Fullname', 'like', '%' . $searchTerm . '%');
        });
        CRUD::column('Phone')->label('Điện thoại')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('Phone', 'like', '%' . $searchTerm . '%');
        });
        CRUD::column('Email')->label('Email')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('Email', 'like', '%' . $searchTerm . '%');
        });

        CRUD::column('Title')->label('Tiêu đề')->type('text')->limit(40)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('Title', 'like', '%' . $searchTerm . '%');
        });

        CRUD::column('Status')->label('Trạng thái')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('Status', 'like', '%' . $searchTerm . '%');
        });

        CRUD::column('order_number')->label('Mã đơn')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('order_number', 'like', '%' . $searchTerm . '%');
        });

        CRUD::column('user_id')->label('User ID')->type('number');
        CRUD::column('Date')->label('Ngày')->type('datetime');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        CRUD::orderBy('id', 'desc');
    }

    protected function setupShowOperation(): void
    {
        CRUD::column('id')->label('ID');
        CRUD::column('Type')
            ->label('Loại')
            ->type('closure')
            ->function(function ($entry) {
                return $this->typeLabel((int) ($entry->Type ?? 0));
            });
        CRUD::column('Status')->label('Trạng thái');
        CRUD::column('order_number')->label('Mã đơn');
        CRUD::column('user_id')->label('User ID');
        CRUD::column('Title')->label('Tiêu đề');
        CRUD::column('Fullname')->label('Họ tên');
        CRUD::column('Phone')->label('Điện thoại');
        CRUD::column('Email')->label('Email');
        CRUD::column('Content')->label('Nội dung');
        CRUD::column('Invoice')->label('Hóa đơn');
        CRUD::column('QRcode')->label('QR code');
        CRUD::column('Date')->label('Ngày')->type('datetime');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');
        CRUD::column('updated_at')->label('Cập nhật')->type('datetime');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(AdminContactRequest::class);
        $this->addContactFields();
    }

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(AdminContactRequest::class);
        $this->addContactFields();
    }

    private function addContactFields(): void
    {
        CRUD::field('Type')
            ->label('Loại')
            ->type('select_from_array')
            ->options([
                0 => 'Bảo hành',
                1 => 'Liên hệ',
                2 => 'Đăng ký thông tin',
            ])
            ->allows_null(false)
            ->default(1)
            ->tab('Thông tin');

        CRUD::field('Status')
            ->label('Trạng thái')
            ->type('text')
            ->hint('Ví dụ: pending / processed')
            ->tab('Thông tin');

        CRUD::field('Date')
            ->label('Ngày')
            ->type('datetime_picker')
            ->datetime_picker_options([
                'format' => 'DD/MM/YYYY HH:mm',
                'language' => 'vi',
            ])
            ->tab('Thông tin');

        CRUD::field('user_id')
            ->label('Người dùng')
            ->type('number')
            ->attributes(['step' => 1, 'min' => 1])
            ->hint('Nhập User ID (nếu có).')
            ->tab('Thông tin');

        CRUD::field('order_number')->label('Mã đơn')->type('text')->tab('Thông tin');
        CRUD::field('Title')->label('Tiêu đề')->type('text')->tab('Thông tin');
        CRUD::field('Fullname')->label('Họ tên')->type('text')->attributes(['required' => true])->tab('Thông tin');
        CRUD::field('Phone')->label('Điện thoại')->type('text')->attributes(['required' => true])->tab('Thông tin');
        CRUD::field('Email')->label('Email')->type('email')->tab('Thông tin');
        CRUD::field('Content')->label('Nội dung')->type('textarea')->tab('Thông tin');

        CRUD::field('Invoice')->label('Hóa đơn')->type('text')->tab('Thông tin');
        CRUD::field('QRcode')->label('QR code')->type('text')->tab('Thông tin');
    }

    private function typeLabel(int $type): string
    {
        return match ($type) {
            0 => 'Bảo hành',
            1 => 'Liên hệ',
            2 => 'Đăng ký thông tin',
            default => (string) $type,
        };
    }
}

