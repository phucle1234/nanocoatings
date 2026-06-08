<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Traits\CarSearch;
use App\Services\ProductService;
use App\Http\Controllers\Api\ProductApiController as ProductApiController;
use App\Http\Controllers\Api\CategoryApiController as CategoryApiController;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use CarSearch;

    protected ProductService $productService;
    protected ProductApiController $productApiController;
    protected CategoryApiController $categoryApiController;

    public function __construct(ProductService $productService, ProductApiController $productApiController, CategoryApiController $categoryApiController)
    {
        $this->productService = $productService;
        $this->productApiController = $productApiController;
        $this->categoryApiController = $categoryApiController;
    }

    public function show(Request $request, $slug)
    {
        try {
            // call api để lấy chi tiết sản phẩm và sản phẩm liên quan
            $productApiController = $this->productApiController->getProductDetail($slug);
            $productData = json_decode($productApiController->getContent(), true);

            if (!$productData['success']) {
                return redirect()
                    ->route('category')
                    ->with('error', 'Sản phẩm không tồn tại');
            }
            $product = $productData['data']['product'] ?? [];
            $relatedProducts = $productData['data']['related_products'] ?? [];

            $carSearchData = $this->getCarSearchData();

            // call api để lấy chi tiết danh mục sản phẩm
            $categoryApiController = $this->categoryApiController->show($request, $product['category_id']);
            $categoryData = json_decode($categoryApiController->getContent(), true);
            $category = $categoryData['data'] ?? [];

            // ✅ Nếu là danh mục con, lấy các sibling categories (anh em cùng parent)
            $siblingCategories = [];
            if (!empty($category['parent_id'])) {
                $siblingResponse = $this->categoryApiController->children($request, $category['parent_id']);
                $siblingData = json_decode($siblingResponse->getContent(), true);

                if ($siblingData['success']) {
                    $allSiblings = $siblingData['data']['children'] ?? [];
                    $siblingCategories = $allSiblings;
                }
            }

            return view('langding.product', compact('product', 'relatedProducts', 'carSearchData', 'category', 'siblingCategories'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()
                ->route('category')
                ->with('error', 'Có lỗi xảy ra khi tải sản phẩm');
        }
    }
}
