<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PostCategoryRequest;
use App\Models\PostCategory;
use App\Services\BlockAdminLinkResolver;
use App\Services\SectorService;
use App\Traits\HasSlugGenerator;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

class SectorCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as traitDestroy;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use HasSlugGenerator;

    public function setup()
    {
        CRUD::setModel(PostCategory::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/sector');
        CRUD::setEntityNameStrings('ngành ứng dụng', 'Ngành ứng dụng');
    }

    protected function setupListOperation()
    {
        CRUD::addClause('where', 'is_sector', true);

        CRUD::addColumn([
            'name' => 'row_number',
            'type' => 'row_number',
            'label' => 'STT',
            'orderable' => false,
        ]);

        CRUD::column('name')
            ->label('Tên ngành')
            ->type('closure')
            ->function(function ($entry) {
                $translation = $entry->translations->firstWhere('language', app()->getLocale())
                    ?? $entry->translations->first();

                return $translation->name ?? 'N/A';
            });

        CRUD::column('slug')
            ->label('Slug')
            ->type('closure')
            ->function(function ($entry) {
                $translation = $entry->translations->firstWhere('language', 'vi')
                    ?? $entry->translations->first();

                return $translation->slug ?? 'N/A';
            });

        CRUD::column('is_active')->label('Hiển thị')->type('boolean');
        CRUD::column('default_locale')->label('Ngôn ngữ mặc định')->type('text');
        CRUD::column('sort_order')->label('Thứ tự')->type('number');

        CRUD::addColumn([
            'name' => 'layout_link',
            'label' => 'Sắp xếp block',
            'type' => 'closure',
            'function' => function ($entry) {
                $url = route('admin.sector-layout.index', $entry->id);

                return '<a href="'.e($url).'" class="btn btn-sm btn-outline-primary"><i class="la la-sort"></i> Block</a>';
            },
            'escaped' => false,
        ]);

        CRUD::orderBy('sort_order', 'asc');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(PostCategoryRequest::class);

        CRUD::field('is_active')
            ->label('Hiển thị trên website')
            ->type('boolean')
            ->default(true)
            ->tab('Chung');

        CRUD::field('sort_order')
            ->label('Thứ tự')
            ->type('number')
            ->default(0)
            ->tab('Chung');

        CRUD::field('default_locale')
            ->label('Ngôn ngữ mặc định cho ngành')
            ->type('select_from_array')
            ->options(config('languages.supported'))
            ->allows_null(true)
            ->default(config('languages.default'))
            ->hint('Dùng khi khách chưa tự chọn ngôn ngữ. Nếu khách đã chọn ngôn ngữ, lựa chọn của khách được ưu tiên.')
            ->tab('Chung');

        $this->addMultilangFieldsWithSections();
        $this->addSlugGenerator('slug', 'slug', false);

        CRUD::field('summernote_sync_view')
            ->type('view')
            ->view('vendor.backpack.crud.fields.summernote-sync')
            ->onlyOn(['create', 'edit']);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        $languages = array_keys(config('languages.supported'));
        $entry = $this->crud->getCurrentEntry();

        // Ensure this sector's own contact/social banner categories exist
        // even if nobody has visited its public page yet, then surface
        // direct edit links right here instead of hunting through the
        // generic Banner Category list.
        app(SectorService::class)->syncSectorResources($entry);
        $contactSocialLinks = app(BlockAdminLinkResolver::class)->sectorContactSocialLinks($entry);

        CRUD::field('contact_social_links')
            ->type('view')
            ->view('admin.sector.contact-social-links')
            ->data(['links' => $contactSocialLinks])
            ->tab('Chung');

        foreach ($languages as $lang) {
            $translation = $entry->translations()->where('language', $lang)->first();
            $imageUrls = $translation ? $translation->image_urls : [];
            $defaultValue = is_array($imageUrls) ? implode("\n", $imageUrls) : $imageUrls;

            CRUD::modifyField('image_urls_'.$lang, [
                'default' => $defaultValue,
                'data' => ['value' => $defaultValue],
            ]);
        }
    }

    public function store()
    {
        $this->crud->hasAccessOrFail('create');
        $request = $this->crud->validateRequest();
        $strippedRequest = $request->except(['_token', '_method', 'slug']);

        $hub = app(SectorService::class)->getOrCreateHubCategory();

        $item = $this->crud->create(array_merge($strippedRequest, [
            'parent_id' => $hub->id,
            'is_sector' => true,
            'is_banner' => false,
            'is_featured' => false,
        ]));

        if ($item) {
            $item->handleTranslations($strippedRequest);
            app(SectorService::class)->provisionSector($item);
        }

        Alert::success('Đã tạo ngành ứng dụng và cấu hình block/banner mặc định.')->flash();

        return $this->crud->performSaveAction($item->getKey());
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');
        $request = $this->crud->validateRequest();
        $strippedRequest = $request->except(['_token', '_method', 'slug', 'parent_id', 'is_sector']);

        $item = $this->crud->update($this->crud->getCurrentEntryId(), $strippedRequest);

        if ($item) {
            $item->handleTranslations($strippedRequest);
        }

        Alert::success('Đã cập nhật ngành ứng dụng.')->flash();

        return $this->crud->performSaveAction($item->getKey());
    }

    protected function addMultilangFieldsWithSections()
    {
        $languages = config('languages.supported');

        foreach ($languages as $lang => $langName) {
            CRUD::field('header_'.$lang)
                ->label('')
                ->type('view')
                ->view('vendor.backpack.crud.fields.language_header')
                ->data(['language' => $langName, 'code' => $lang])
                ->tab($langName);

            CRUD::field('name_'.$lang)
                ->label('Tên ngành')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'name'))
                ->tab($langName);

            CRUD::field('image_urls_'.$lang)
                ->label('Ảnh đại diện (hub)')
                ->type('view')
                ->view('components.multiple-images')
                ->default($this->getTranslationValue($lang, 'image_urls'))
                ->tab($langName);

            CRUD::field('description_'.$lang)
                ->label('Mô tả ngắn')
                ->type('summernote')
                ->default($this->getTranslationValue($lang, 'description'))
                ->tab($langName);

            CRUD::field('meta_title_'.$lang)
                ->label('Meta title')
                ->type('text')
                ->default($this->getTranslationValue($lang, 'meta_title'))
                ->tab($langName);

            CRUD::field('meta_description_'.$lang)
                ->label('Meta description')
                ->type('textarea')
                ->default($this->getTranslationValue($lang, 'meta_description'))
                ->tab($langName);
        }
    }

    protected function getTranslationValue(string $lang, string $field)
    {
        if (! $this->crud->getCurrentEntry()) {
            return '';
        }

        $translation = $this->crud->getCurrentEntry()->translations()->where('language', $lang)->first();

        if (! $translation) {
            return '';
        }

        if ($field === 'image_urls' && is_array($translation->image_urls)) {
            return implode("\n", $translation->image_urls);
        }

        return $translation->{$field} ?? '';
    }
}
