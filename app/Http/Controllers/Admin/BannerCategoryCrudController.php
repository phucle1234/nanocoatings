<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PostCategoryRequest;
use App\Traits\HasSlugGenerator;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Prologue\Alerts\Facades\Alert;

class BannerCategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use HasSlugGenerator;

    public function setup()
    {
        CRUD::setModel(\App\Models\PostCategory::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/banner-category');
        CRUD::setEntityNameStrings('danh mục banner', 'Danh mục banner');
    }

    protected function setupListOperation()
    {
        // Cột hiển thị
        CRUD::addColumn([
            'name' => 'row_number',
            'type' => 'row_number',
            'label' => 'STT',
            'orderable' => false,
        ]);
        CRUD::column('slug')->label('Slug')->type('closure')->function(function ($entry) {
            $translations = $entry->translations;
            if ($translations->isNotEmpty()) {
                $slugs = $translations->where('language', app()->getLocale())->pluck('slug', 'language')->toArray();
                return implode(' | ', array_filter($slugs));
            }
            return $entry->slug ?? 'N/A';
        });
        CRUD::column('name')
            ->label('Tên danh mục')
            ->type('closure')
            ->function(function ($entry) {
                $translations = $entry->translations;
                if ($translations->isNotEmpty()) {
                    $names = $translations->where('language', app()->getLocale())->pluck('name', 'language')->toArray();
                    return implode(' | ', array_filter($names));
                }
                return $entry->slug ?? 'N/A';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->whereHas('translations', function ($q) use ($searchTerm) {
                    $q->where(function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('slug', 'like', '%' . $searchTerm . '%');
                    });
                });
            });
        CRUD::column('parent')->label('Danh mục cha')->type('closure')->function(function ($entry) {
            if ($entry->parent) {
                $parentTranslations = $entry->parent->translations;
                if ($parentTranslations->isNotEmpty()) {
                    $names = $parentTranslations->where('language', app()->getLocale())->pluck('name', 'language')->toArray();
                    return implode(' | ', array_filter($names));
                }
                return $entry->parent->slug;
            }
            return '-';
        });
        CRUD::column('image')->label('Hình ảnh')->type('closure')->function(function ($entry) {
            // Lấy hình từ translations
            $translations = $entry->translations;
            if ($translations->isNotEmpty()) {
                // Lấy hình đầu tiên từ translation đầu tiên có image_urls
                foreach ($translations as $translation) {
                    if ($translation->image_urls && is_array($translation->image_urls) && !empty($translation->image_urls)) {
                        $firstImage = $translation->image_urls[0];
                        return '<img src="' . $firstImage . '" style="max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px;">';
                    }
                }
            }
            // Fallback về image field cũ nếu có
            if ($entry->image) {
                return '<img src="' . $entry->image . '" style="max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px;">';
            }
            return '<span class="text-muted">Chưa có hình</span>';
        })->escaped(false);
        CRUD::column('is_active')->label('Trạng thái')->type('boolean');
        CRUD::column('is_featured')->label('Nổi bật')->type('boolean');
        CRUD::column('sort_order')->label('Thứ tự')->type('number');
        CRUD::column('manage_posts')
            ->label('Tin tức')
            ->type('closure')
            ->function(function ($entry) {
                return $entry->getManagePostsLinkHtml();
            })
            ->escaped(false);
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        // Sắp xếp mặc định
        CRUD::orderBy('parent_id', 'asc');
        CRUD::orderBy('sort_order', 'asc');
        CRUD::orderBy('created_at', 'desc');

        // Hiển thị tất cả cột, không bật chế độ responsive ẩn cột
        CRUD::setOperationSetting('responsiveTable', false);

        CRUD::addClause('where', 'is_banner', true);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(PostCategoryRequest::class);



        // Tạo options cho danh mục cha
        $categories = \App\Models\PostCategory::with('translation')
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
            ->default(null)->tab('Chung');



        CRUD::field('icon')
            ->label('Thời gian chạy slider banner (miliseconds)')
            ->type('text')
            ->attributes(['placeholder' => '1000'])
            ->hint('Thời gian chạy (miliseconds)')->tab('Chung');

        CRUD::field('is_active')
            ->label('Trạng thái hoạt động')
            ->type('boolean')
            ->default(true)->tab('Chung');

        CRUD::field('is_featured')
            ->label('Danh mục nổi bật')
            ->type('boolean')
            ->default(false)->tab('Chung');
        CRUD::field('is_banner')
            ->label('Là danh mục banner')
            ->type('boolean')
            ->default(true)->tab('Chung');
        CRUD::field('sort_order')
            ->label('Thứ tự sắp xếp')
            ->type('number')
            ->default(0)->tab('Chung');

        // Thêm các field đa ngôn ngữ với giao diện dễ quản lý
        $this->addMultilangFieldsWithSections();

        // Thêm slug generator (đơn lẻ)
        $this->addSlugGenerator('slug', 'slug', false);

        // ✅ Thêm sync script cho Summernote
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
                'default' => $defaultValue,
                'data' => ['value' => $defaultValue]  // ✅ THÊM DÒNG NÀY!
            ]);
        }
    }

    protected function setupShowOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('slug')->label('Slug');
        CRUD::column('parent')->label('Danh mục cha')->type('closure')->function(function ($entry) {
            if ($entry->parent) {
                $translations = $entry->parent->translations;
                if ($translations->isNotEmpty()) {
                    $names = $translations->pluck('name', 'language')->toArray();
                    return implode(' | ', array_filter($names));
                }
                return $entry->parent->slug;
            }
            return 'Không có';
        });
        CRUD::column('image')->label('Hình ảnh')->type('closure')->function(function ($entry) {
            // Lấy hình đầu tiên từ image_urls
            if ($entry->image_urls && is_array($entry->image_urls) && !empty($entry->image_urls)) {
                $firstImage = $entry->image_urls[0];
                return '<img src="' . $firstImage . '" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px;">';
            }
            // Fallback về image field cũ nếu có
            if ($entry->image) {
                return '<img src="' . $entry->image . '" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px;">';
            }
            return '<span class="text-muted">Chưa có hình</span>';
        });
        CRUD::column('is_active')->label('Hoạt động')->type('boolean');
        CRUD::column('is_featured')->label('Nổi bật')->type('boolean');
        CRUD::column('sort_order')->label('Thứ tự');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        // Hiển thị translations
        $this->addMultilangColumns();
    }


    public function store()
    {
        $this->crud->hasAccessOrFail('create');
        $request = $this->crud->validateRequest();

        $strippedRequest = $request->except(['_token', '_method']);
        $item = $this->crud->create($strippedRequest);

        if ($item) {
            $item->handleTranslations($strippedRequest);
        }

        Alert::success('Danh mục banner đã được tạo thành công!')->flash();
        return $this->crud->performSaveAction($item->getKey());
    }

    public function update()
    {
        $this->crud->hasAccessOrFail('update');
        $request = $this->crud->validateRequest();

        $strippedRequest = $request->except(['_token', '_method']);
        $item = $this->crud->update($this->crud->getCurrentEntryId(), $strippedRequest);

        if ($item) {
            $item->handleTranslations($strippedRequest);
        }

        Alert::success('Danh mục banner đã được cập nhật thành công!')->flash();
        return $this->crud->performSaveAction($item->getKey());
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

            CRUD::field('image_urls_' . $lang)
                ->label('Hình ảnh (' . $langName . ')')
                ->type('view')
                ->view('components.multiple-images')
                ->default($this->getTranslationValue($lang, 'image_urls'))
                ->hint('Thêm nhiều hình ảnh cho danh mục bằng ' . $langName)
                ->tab($langName);

            // URL
            CRUD::field('url_' . $lang)
                ->label('Link URL (' . $langName . ')')
                ->type('url')
                ->default($this->getTranslationValue($lang, 'url'))
                ->attributes([
                    'placeholder' => 'https://example.com/link'
                ])
                ->hint('Nhập URL liên kết cho danh mục banner (nếu có)')
                ->tab($langName);

            // Separator
            CRUD::field('separator_' . $lang)
                ->label('')
                ->type('view')
                ->view('vendor.backpack.crud.fields.separator')
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
        }
    }

    /**
     * Thêm các cột đa ngôn ngữ cho Show operation
     */
    protected function addMultilangColumns()
    {
        $languages = config('languages.supported');

        foreach ($languages as $lang => $langName) {
            CRUD::column('name_' . $lang)
                ->label("Tên ({$langName})")
                ->type('closure')
                ->function(function ($entry) use ($lang) {
                    $translation = $entry->translations()->where('language', $lang)->first();
                    return $translation ? $translation->name : '-';
                });

            CRUD::column('description_' . $lang)
                ->label("Mô tả ({$langName})")
                ->type('closure')
                ->function(function ($entry) use ($lang) {
                    $translation = $entry->translations()->where('language', $lang)->first();
                    return $translation ? strip_tags($translation->description) : '-';
                });

            CRUD::column('slug_' . $lang)
                ->label("Slug ({$langName})")
                ->type('closure')
                ->function(function ($entry) use ($lang) {
                    $translation = $entry->translations()->where('language', $lang)->first();
                    return $translation ? $translation->slug : '-';
                });
        }
    }
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
