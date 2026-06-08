<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AdminUserRequest;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;

/**
 * CRUD quản lý user (bảng users).
 */
class UserCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation {
        store as traitStore;
    }
    use UpdateOperation {
        update as traitUpdate;
    }
    use DeleteOperation {
        destroy as traitDestroy;
    }
    use ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('người dùng', 'người dùng');
    }

    protected function setupListOperation(): void
    {
        $this->crud->addClause('with', ['productCategories']);

        // Filter Vai trò (MIỄN PHÍ): dùng query param ?role=... + button view (không dùng addFilter/PRO).
        $role = request()->query('role');
        if (is_string($role) && in_array($role, ['admin', 'customer', 'dealer'], true)) {
            $this->crud->addClause('where', 'role', $role);
        }

        $this->crud->addButtonFromView('top', 'user_role_quick_filter', 'user_role_quick_filter', 'beginning');

        CRUD::addColumn([
            'name' => 'row_number',
            'type' => 'row_number',
            'label' => 'STT',
            'orderable' => false,
        ]);
        CRUD::column('code')->label('Mã')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('code', 'like', '%' . $searchTerm . '%');
        });
        CRUD::column('parent_code')->label('Mã cha')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('parent_code', 'like', '%' . $searchTerm . '%');
        });
        CRUD::column('name')->label('Tên')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('name', 'like', '%' . $searchTerm . '%');
        });
        CRUD::column('user_name')->label('Tên đăng nhập')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('user_name', 'like', '%' . $searchTerm . '%');
        });
        // Giống ProductCrud: dùng text + searchLogic (không dùng addFilter — cần Backpack PRO).
        CRUD::column('email')->label('Email')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('email', 'like', '%' . $searchTerm . '%');
        });
        CRUD::column('role')->label('Vai trò')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('role', 'like', '%' . $searchTerm . '%');
        });

        CRUD::column('npp_product_categories')
            ->label('Danh mục NPP (được bán)')
            ->type('closure')
            ->function(function ($entry) {
                if ($entry->role !== 'dealer') {
                    return '—';
                }
                if ($entry->productCategories->isEmpty()) {
                    return '<span class="text-muted">Chưa gán</span>';
                }

                return $entry->productCategories->map(function ($cat) {
                    $label = $cat->name ?: $cat->code;

                    return e($label);
                })->implode(', ');
            })
            ->escaped(false);

        CRUD::column('address')->label('Địa chỉ')->type('text')->limit(40);
        CRUD::column('phone')->label('Điện thoại')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('phone', 'like', '%' . $searchTerm . '%');
        });
        CRUD::column('city_display')
            ->label('Tỉnh/TP')
            ->type('closure')
            ->function(function ($entry) {
                return $this->formatCityDisplayForList($entry);
            })
            ->escaped(false)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $term = '%' . $searchTerm . '%';
                $query->orWhere('users.city_code', 'like', $term)
                    ->orWhereExists(function ($sub) use ($term) {
                        $sub->select(DB::raw(1))
                            ->from('npp_provinces as p')
                            ->whereColumn('p.code', 'users.city_code')
                            ->where(function ($q) use ($term) {
                                $q->where('p.name_vi', 'like', $term)
                                    ->orWhere('p.name_en', 'like', $term)
                                    ->orWhere('p.code', 'like', $term);
                            });
                    });
            });
        CRUD::column('type')->label('Loại')->type('text')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('type', 'like', '%' . $searchTerm . '%');
        });

        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        CRUD::orderBy('id', 'desc');
        CRUD::setOperationSetting('responsiveTable', false);
    }

    protected function setupShowOperation(): void
    {
        CRUD::column('id')->label('ID');
        CRUD::column('code')->label('Mã');
        CRUD::column('parent_code')->label('Mã cha');
        CRUD::column('name')->label('Tên');
        CRUD::column('user_name')->label('Tên đăng nhập');
        CRUD::column('email')->label('Email');
        CRUD::column('role')->label('Vai trò');
        CRUD::column('npp_product_categories')
            ->label('Danh mục NPP (được bán)')
            ->type('closure')
            ->function(function ($entry) {
                $entry->loadMissing('productCategories');
                if ($entry->role !== 'dealer') {
                    return '—';
                }
                if ($entry->productCategories->isEmpty()) {
                    return 'Chưa gán';
                }

                return $entry->productCategories->map(fn($c) => $c->name ?: $c->code)->implode(', ');
            });
        CRUD::column('address')->label('Địa chỉ');
        CRUD::column('latitude')->label('Vĩ độ');
        CRUD::column('longitude')->label('Kinh độ');
        CRUD::column('phone')->label('Điện thoại');
        CRUD::column('city_display')
            ->label('Tỉnh/TP')
            ->type('closure')
            ->function(function ($entry) {
                return $this->formatCityDisplayPlain($entry);
            });
        CRUD::column('type')->label('Loại');
        CRUD::column('status')->label('Trạng thái');
        CRUD::column('is_active')->label('Kích hoạt');
        CRUD::column('is_admin')->label('Admin');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');
        CRUD::column('updated_at')->label('Cập nhật')->type('datetime');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(AdminUserRequest::class);
        $this->addUserFields(false);
    }

    protected function setupUpdateOperation(): void
    {
        CRUD::setValidation(AdminUserRequest::class);
        $this->addUserFields(true);

        $current = $this->crud->getCurrentEntry();
        if ($current && $current->role === 'dealer') {
            $current->loadMissing('productCategories');
            $ids = $current->productCategories->pluck('id')->all();
            CRUD::modifyField('productCategories', [
                'value'   => $ids,
                'default' => $ids,
            ]);
        }
    }

    private function addUserFields(bool $isUpdate): void
    {
        CRUD::field('code')->label('Mã')->type('text')->tab('Thông tin');
        CRUD::field('parent_code')->label('Mã cha')->type('text')->tab('Thông tin');
        CRUD::field('name')->label('Tên')->type('text')->tab('Thông tin')->attributes(['required' => true]);
        CRUD::field('user_name')->label('Tên đăng nhập')->type('text')->tab('Thông tin')->attributes(['required' => true]);
        CRUD::field('email')->label('Email')->type('email')->tab('Thông tin')->attributes(['required' => true]);
        CRUD::field('role')->label('Vai trò')->type('select_from_array')
            ->options([
                'admin'    => 'Admin',
                'customer' => 'Khách hàng',
                'dealer'   => 'Đại lý / NPP',
            ])
            ->allows_null(false)
            ->default('customer')
            ->tab('Thông tin');

        CRUD::field('password')->label('Mật khẩu')->type('password')
            ->hint($isUpdate ? 'Để trống nếu không đổi mật khẩu' : 'Tối thiểu 6 ký tự')
            ->tab('Thông tin');

        CRUD::field('address')->label('Địa chỉ')->type('textarea')->tab('Thông tin');
        CRUD::field('latitude')->label('Vĩ độ')->type('number')->attributes(['step' => 'any'])->tab('Thông tin');
        CRUD::field('longitude')->label('Kinh độ')->type('number')->attributes(['step' => 'any'])->tab('Thông tin');
        CRUD::field('phone')->label('Điện thoại')->type('text')->tab('Thông tin');
        CRUD::field('city_code')->label('Mã thành phố')->type('text')->tab('Thông tin');
        CRUD::field('type')->label('Loại')->type('select_from_array')
            ->options([
                'customer_account' => 'Tài khoản khách',
                'customer_info'    => 'Thông tin khách',
            ])
            ->allows_null(true)
            ->tab('Thông tin');

        // Giống ProductCrud: select_many + pivot npp_product_categories (BelongsToMany productCategories).
        // Tên field phải trùng method quan hệ productCategories() — snake_case product_categories khiến Backpack gọi product_categories().
        CRUD::field('productCategories')
            ->label('Danh mục sản phẩm được bán (NPP)')
            ->type('select_multiple')
            ->entity('productCategories')
            ->attribute('name')
            ->model(\App\Models\ProductCategory::class)
            ->pivot(true)
            ->hint('Chỉ dùng cho vai trò Đại lý / NPP. Với admin hoặc khách hàng, danh mục sẽ được xóa khi lưu.')
            ->wrapper([
                'id'    => 'user-npp-product-categories-wrap',
                'class' => 'form-group col-md-12 mb-3',
            ])
            ->tab('Danh mục NPP');

        CRUD::field('user_dealer_categories_script')
            ->label('')
            ->type('view')
            ->fake(true)
            ->view('vendor.backpack.crud.fields.user_dealer_categories_script')
            ->onlyOn(['create', 'edit']);
    }

    /**
     * Lấy tỉnh từ npp_provinces theo users.city_code + users.country (mã hoặc id npp_countries).
     * Không dùng quan hệ Eloquent trên User.
     */
    private function resolveProvinceRowForUser(User $user): ?object
    {
        $cityCode = trim((string) ($user->city_code ?? ''));
        if ($cityCode === '') {
            return null;
        }

        $q = DB::table('npp_provinces as p')
            ->join('npp_countries as c', 'c.id', '=', 'p.country_id')
            ->where('p.code', $cityCode);

        $country = trim((string) ($user->country ?? ''));
        if ($country !== '') {
            if (ctype_digit($country)) {
                $q->where('c.id', (int) $country);
            } else {
                $q->where('c.code', $country);
            }
        }

        return $q->select('p.name_vi', 'p.name_en', 'p.code')->orderBy('p.id')->first();
    }

    private function formatCityDisplayForList(User $entry): string
    {
        $code = trim((string) ($entry->city_code ?? ''));
        if ($code === '') {
            return '—';
        }
        $p = $this->resolveProvinceRowForUser($entry);
        if (! $p) {
            $hint = trim((string) ($entry->country ?? '')) !== ''
                ? ' (chưa khớp theo mã tỉnh + quốc gia)'
                : ' (chưa khớp npp_provinces — nên có users.country)';

            return e($code) . ' <span class="text-muted">' . e($hint) . '</span>';
        }
        $name = app()->getLocale() === 'en'
            ? ($p->name_en ?: $p->name_vi)
            : ($p->name_vi ?: $p->name_en);

        return e($name) . ' <small class="text-muted">(' . e($code) . ')</small>';
    }

    private function formatCityDisplayPlain(User $entry): string
    {
        $code = trim((string) ($entry->city_code ?? ''));
        if ($code === '') {
            return '—';
        }
        $p = $this->resolveProvinceRowForUser($entry);
        if (! $p) {
            return $code . ' (chưa khớp npp_provinces / quốc gia)';
        }
        $name = app()->getLocale() === 'en'
            ? ($p->name_en ?: $p->name_vi)
            : ($p->name_vi ?: $p->name_en);

        return $name . ' (' . $code . ')';
    }

    /**
     * Nếu không phải dealer: xóa hết liên kết npp_product_categories (tránh giữ pivot khi đổi role).
     */
    private function syncNppProductCategoriesForRole(User $user): void
    {
        if ($user->role !== 'dealer') {
            $user->productCategories()->sync([]);
        }
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        $request = $this->crud->validateRequest();
        $this->crud->registerFieldEvents();

        $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
        $this->data['entry'] = $this->crud->entry = $item;

        $this->syncNppProductCategoriesForRole($item);

        \Alert::success(trans('backpack::crud.insert_success'))->flash();
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        $request = $this->crud->validateRequest();
        $this->crud->registerFieldEvents();

        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $this->crud->getStrippedSaveRequest($request)
        );
        $this->data['entry'] = $this->crud->entry = $item;

        $this->syncNppProductCategoriesForRole($item);

        \Alert::success(trans('backpack::crud.update_success'))->flash();
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Model User cast 'password' => 'hashed' — gửi plain text, không Hash::make ở đây.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['is_admin'] = ($data['role'] ?? '') === 'admin' ? '1' : '0';

        if (empty($data['TokenID'])) {
            $data['TokenID'] = $this->generateUniqueTokenId();
        }

        if (empty($data['status'])) {
            $data['status'] = 'active';
        }
        if (! isset($data['is_active']) || $data['is_active'] === null || $data['is_active'] === '') {
            $data['is_active'] = '1';
        }

        return $data;
    }

    private function generateUniqueTokenId(): string
    {
        do {
            $token = str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        } while (User::where('TokenID', $token)->exists());

        return $token;
    }

    public function destroy($id)
    {
        if ((int) $id === (int) backpack_user()->id) {
            Alert::error('Không thể xóa chính tài khoản đang đăng nhập.')->flash();

            return redirect()->to($this->crud->route);
        }

        return $this->traitDestroy($id);
    }
}
