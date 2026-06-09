<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Services\PostService;
use App\Services\PostCategoryService;
use App\Models\PostCategory;
use App\Traits\HasImage;
use App\Traits\CarSearch;
use App\Traits\HasBanners;
use App\Services\HomepageLayoutService;

class HomeController extends Controller
{
	use HasImage;
	use CarSearch;
	use HasBanners;

	protected ProductService $productService;
	protected CategoryService $categoryService;
	protected PostService $postService;
	protected PostCategoryService $postCategoryService;
	protected HomepageLayoutService $homepageLayoutService;

	public function __construct(
		ProductService $productService,
		CategoryService $categoryService,
		PostService $postService,
		PostCategoryService $postCategoryService,
		HomepageLayoutService $homepageLayoutService
	) {
		$this->productService = $productService;
		$this->categoryService = $categoryService;
		$this->postService = $postService;
		$this->postCategoryService = $postCategoryService;
		$this->homepageLayoutService = $homepageLayoutService;
	}

	public function index()
	{
		$currentLocale = app()->getLocale();

		// Lấy sản phẩm bán chạy
		$bestsellerProducts = $this->productService->getBestsellerProducts(10, $currentLocale);

		$categories = $this->categoryService->getFeaturedCategories($currentLocale);

		// ✅ Lấy tất cả danh mục gốc (parent = 0) cho tab "Tất cả"
		$rootCategories = $this->categoryService->getRootCategories($currentLocale);

		// ✅ Xử lý hình ảnh cho root categories sử dụng HasImage trait
		foreach ($rootCategories as $category) {
			$category->category_image = $this->getImageJson($category->category_image_urls);
		}

		// Nhóm danh mục gốc thành các hàng (mỗi hàng 6 danh mục)
		$allCategoryRows = $this->groupCategoriesInRows($rootCategories);

		foreach ($categories as $category) {
			$category->category_image = $this->getImageJson($category->category_image_urls);
		}

		// Lấy danh mục tin tức (children của "truyen-thong")
		$tinTucCategory = $this->postCategoryService->getCategoryBySlugOrId('truyen-thong', $currentLocale);
		$newsCategories = collect();

		if ($tinTucCategory) {
			// Lấy children categories và format cho frontend
			$children = $tinTucCategory->children()
				->where('is_active', true)
				->withCount('posts as posts_count')
				->with(['translations' => function ($q) use ($currentLocale) {
					$q->where('language', $currentLocale);
				}])
				->orderBy('sort_order')
				->get();

			$newsCategories = $children->filter(function ($category) use ($currentLocale) {
				// Loại bỏ các danh mục banner
				$translation = $category->translations->firstWhere('language', $currentLocale);
				$excludedSlugs = [
					'banners-home',
					'home-slider',
					'home-promotion',
					'video-introduction',
					'partner-banner'
				];
				return !in_array($translation->slug ?? null, $excludedSlugs);
			})->map(function ($category) use ($currentLocale) {
				// Format cho frontend tương tự formatCategoryForFrontend
				$translation = $category->translations->firstWhere('language', $currentLocale);
				$category->category_name = $translation->name ?? null;
				$category->category_description = $translation->description ?? null;
				return $category;
			});
		}

		// Posts theo trang (4 / category) + meta phân trang cho AJAX tab-feed
		foreach ($newsCategories as $category) {
			if (! $category instanceof PostCategory) {
				continue;
			}
			$result = $this->postService->getPostsByCategory($category, [], 'sort_order', 4, $currentLocale, 1);
			$p = $result['pagination'];
			$category->feed_posts = $result['posts']->values()->all();
			$category->feed_pagination = [
				'current_page' => $p->currentPage(),
				'last_page' => max(1, $p->lastPage()),
				'per_page' => $p->perPage(),
				'total' => $p->total(),
			];
		}
		// ✅ Lấy banner home slider từ danh mục 'home-slider'
		$homeSliderBanners = $this->getBannersBySlug('home-slider');
		$homeSliderBanners2 = $this->getBannersBySlug('home-slider-2');

		$homepageBlocks = $this->homepageLayoutService->getActiveLayoutBlocks();

		// $carSearchData = $this->getCarSearchData();
		// $tireSizeData = $this->getTireSizeData();

		return view('langding.home', compact(
			'bestsellerProducts',
			'categories',
			'rootCategories',
			'allCategoryRows',
			'newsCategories',
			// 'tireSizeData',
			// 'carSearchData',
			'homeSliderBanners',
			'homeSliderBanners2',
			'homepageBlocks',
		));
	}

	private function groupCategoriesInRows($categories)
	{
		// Số danh mục mỗi hàng
		$categoriesPerRow = 6;

		// Nhóm danh mục thành các hàng
		$rows = [];
		$currentRow = [];
		$index = 0;
		$total = is_countable($categories) ? count($categories) : $categories->count();

		foreach ($categories as $category) {
			$currentRow[] = $category;
			$index++;

			// Khi đạt đủ 6 danh mục hoặc đã xử lý danh mục cuối cùng
			if ($index % $categoriesPerRow == 0 || $index == $total) {
				$rows[] = $currentRow;
				$currentRow = [];
			}
		}

		return $rows;
	}
}
