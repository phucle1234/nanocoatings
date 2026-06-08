<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Traits\CarSearch;
use App\Services\ProductService;
use App\Services\UploadService;
use App\Models\Product;
use App\Http\Controllers\Api\ProductApiController as ProductApiController;
use App\Http\Controllers\Api\CategoryApiController as CategoryApiController;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use CarSearch;

    protected ProductService $productService;
    protected UploadService $uploadService;
    protected ProductApiController $productApiController;
    protected CategoryApiController $categoryApiController;

    public function __construct(
        ProductService $productService,
        UploadService $uploadService,
        ProductApiController $productApiController,
        CategoryApiController $categoryApiController
    ) {
        $this->productService = $productService;
        $this->uploadService = $uploadService;
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

    public function document(Request $request, string $slug)
    {
        $locale = app()->getLocale();

        $query = Product::query()
            ->where('is_active', true)
            ->with('documentFile');

        if (is_numeric($slug)) {
            $product = $query->find($slug);
        } else {
            $product = $query->whereHas('translations', function ($q) use ($slug, $locale) {
                $q->where('slug', $slug)->where('language', $locale);
            })->first();
        }

        if (!$product || !$product->document_file_id || !$product->documentFile) {
            abort(404, 'Tài liệu không tồn tại');
        }

        $uploadedFile = $product->documentFile;

        if (!$this->uploadService->fileExists($uploadedFile)) {
            abort(404, 'File không tồn tại');
        }

        $fileContent = $this->uploadService->getFileContent($uploadedFile);
        if (!$fileContent) {
            abort(404, 'Không thể đọc file');
        }

        return response($fileContent, 200, [
            'Content-Type' => $uploadedFile->mime_type,
            'Content-Disposition' => 'inline; filename="' . $uploadedFile->original_name . '"',
            'Content-Length' => $uploadedFile->size,
        ]);
    }
}
