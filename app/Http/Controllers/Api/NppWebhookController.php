<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\NppShowroomImporter;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;

class NppWebhookController extends Controller
{
    public function __construct(
        private readonly NppShowroomImporter $nppShowroomImporter
    ) {}

    /**
     * POST /api/webhook/npp-insert — đồng bộ toàn bộ showroom/NPP từ API Casumina.
     */
    public function insertNppAll(Request $request)
    {
        $result = $this->nppShowroomImporter->importAll();

        if (!($result['success'] ?? false)) {
            $status = 500;
            if (($result['message'] ?? '') === 'Payload showroom rỗng hoặc sai định dạng') {
                $status = 422;
            }
            if (isset($result['status']) && is_int($result['status'])) {
                $status = $result['status'] >= 400 && $result['status'] < 600 ? $result['status'] : $status;
            }

            return response()->json($result, $status);
        }

        return response()->json($result, 200);
    }
    public function createAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string',
            'source_code' => 'nullable|string',
            'code' => 'required|string',
            'fullname'    => 'required|string',
            'showroom'    => 'nullable|string',
            'password'    => 'required|string',
            'email'       => 'required|email',
            'phone'       => 'required|string',
            'address'     => 'required|string',
            'city_code' => 'nullable|string',
            'city_name' => 'nullable|string',
            'category' => 'nullable|array',
        ]);
        $latitude = str_replace(',', '.', $request->latitude) ?? null;
        $longitude = str_replace(',', '.', $request->longitude) ?? null;
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        if ($request->code) {
            $user = User::where('code', $request->code)->where('role', 'dealer')->first();
            if ($user) {
                return response()->json(['success' => false, 'message' => 'code already exists'], 422);
            }
        }
        $user = User::where('user_name', $request->username)->where('role', 'dealer')->first();
        if ($user) {
            return response()->json(['success' => false, 'message' => 'user_name already exists'], 422);
        }

        if ($request->source_code != '') {
            $parentCode = $request->source_code;

            $parent = User::where('user_name', $parentCode)
                ->where('role', 'dealer')
                ->where(function ($query) {
                    $query->whereNull('parent_id')
                        ->orWhere('parent_id', 0);
                })
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'NPP cha không tồn tại hoặc không phải là cấp cao nhất'
                ], 404);
            }

            $parentId = $parent->id;
        } else {
            $parentCode = null;
            $parentId = 0;
        }



        $user = User::create([
            'user_name' => $request->username,
            'code' => $request->username,
            'parent_id' => $parentId,
            'parent_code' => $parentCode,
            'source_code' => $request->source_code,
            'role' => 'dealer',
            'name' => $request->fullname,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city_code' => $request->city_code ?? null,
            'city_name' => $request->city_name ?? null,
            'country' => $request->country ?? null,
            'latitude' => $latitude ?? null,
            'longitude' => $longitude ?? null,
            'status' => 'active',
            'is_active' => '1',
            'is_admin' => '0',
            'F1UserID' => '999999999',
            'TokenID' => '999999999',
        ]);

        $categoryData = $request->category ?? [];

        $codes = collect($categoryData)
            ->pluck('code')
            ->filter()
            ->map(fn($c) => trim((string) $c))
            ->flatMap(function ($code) {
                $out = [$code];
                if (str_contains($code, '_')) {
                    $out[] = explode('_', $code, 2)[0]; // "04_2101" -> "04"
                }
                return $out;
            })
            ->unique()
            ->values();

        $resultsCategory = [];

        foreach ($codes as $code) {
            $category = ProductCategory::where('code', $code)->first();
            if (! $category) {
                continue; // Option A: không tự tạo
            }

            $exists = DB::table('npp_product_categories')
                ->where('user_id', $user->id)
                ->where('category_id', $category->id)
                ->exists();

            if (! $exists) {
                DB::table('npp_product_categories')->insert([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $resultsCategory[] = ['code' => $category->code];
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'id' => $user->id,
                'user_name' => $user->user_name,
                'fullname' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'source_code' => $request->source_code,
                'status' => $user->status,
                'category' => $resultsCategory,
            ],
        ], 200);
    }
    public function updateAccount(Request $request)
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            return response()->json(['success' => false, 'message' => 'Empty payload'], 422);
        }

        $npp = User::where('user_name', $payload['username'])->where('role', 'dealer')->first();
        $latitude  = isset($payload['latitude'])  ? str_replace(',', '.', (string)$payload['latitude'])  : $npp->latitude;
        $longitude = isset($payload['longitude']) ? str_replace(',', '.', (string)$payload['longitude']) : $npp->longitude;
        if (!$npp) {
            return response()->json(['success' => false, 'message' => 'NPP not found'], 404);
        }
        $npp->update([
            'name' => $payload['fullname'],
            'email' => $payload['email'] ?? $npp->email,
            'phone' => $payload['phone'] ?? $npp->phone,
            'city_name' => $payload['city_name'] ?? $npp->city_name,
            'city_code' => $payload['city_code'] ?? $npp->city_code,
            'country' => $payload['country'] ?? $npp->country,
            'latitude' => $latitude ?? $npp->latitude,
            'longitude' => $longitude ?? $npp->longitude,
            'address' => $payload['address'] ?? $npp->address,
            'link_map' => $payload['link_map'] ?? $npp->link_map,
            'password' => Hash::make($payload['password'])
        ]);
        $npp->save();
        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully',
            'data'    => [
                'user_name' => $payload['username'],
                'fullname' => $payload['fullname'],
                'email' => $payload['email'] ?? $npp->email,
                'phone' => $payload['phone'] ?? $npp->phone,
                'address' => $payload['address'] ?? $npp->address,
                'city_name' => $payload['city_name'] ?? $npp->city_name,
                'city_code' => $payload['city_code'] ?? $npp->city_code,
                'country' => $payload['country'] ?? $npp->country,
                'latitude' => $latitude ?? $npp->latitude,
                'longitude' => $longitude ?? $npp->longitude,
                'link_map' => $payload['link_map'] ?? $npp->link_map,
                'password' => Hash::make($payload['password'])
            ],
        ], 200);
    }
    private function getChildCategoryCodes(array $categoryData)
    {
        return collect($categoryData)
            ->pluck('code')
            ->filter()
            ->map(fn($c) => trim((string) $c))
            ->flatMap(function ($code) {
                $codes = [$code];

                if (str_contains($code, '_')) {
                    $codes[] = explode('_', $code, 2)[0];
                }

                return $codes;
            })
            ->unique()
            ->values();
    }

    private function getParentCategoryCodes(array $categoryData)
    {
        return collect($categoryData)
            ->pluck('code')
            ->filter()
            ->map(fn($c) => trim((string) $c))
            ->map(function ($code) {
                if (str_contains($code, '_')) {
                    return explode('_', $code, 2)[0];
                }

                return $code;
            })
            ->unique()
            ->values();
    }

    private function findParentDealer(User $dealer): ?User
    {
        if (empty($dealer->parent_code)) {
            return null;
        }

        return User::where('role', 'dealer')
            ->where(function ($query) use ($dealer) {
                $query->where('code', $dealer->parent_code)
                    ->orWhere('user_name', $dealer->parent_code);
            })
            ->first();
    }

    private function insertCategoryForDealer(User $dealer, ProductCategory $category): bool
    {
        $exists = DB::table('npp_product_categories')
            ->where('user_id', $dealer->id)
            ->where('category_id', $category->id)
            ->exists();

        if ($exists) {
            return false;
        }

        DB::table('npp_product_categories')->insert([
            'user_id' => $dealer->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }
    public function insertCategoryNpp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'category' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $npp = User::where('code', $request->code)
            ->where('role', 'dealer')
            ->first();

        if (!$npp) {
            return response()->json([
                'success' => false,
                'message' => 'NPP not found',
            ], 404);
        }

        $categoryData = $request->category ?? [];

        // Con nhận: code chi tiết + code gốc
        // Ví dụ: 01_01_04 -> 01_01_04, 01
        $childCodes = $this->getChildCategoryCodes($categoryData);

        // Cha chỉ nhận: code gốc
        // Ví dụ: 01_01_04 -> 01
        $parentCodes = $this->getParentCategoryCodes($categoryData);

        $parent = $this->findParentDealer($npp);

        $insertedForChild = [];
        $insertedForParent = [];

        DB::transaction(function () use ($childCodes, $parentCodes, $npp, $parent, &$insertedForChild, &$insertedForParent) {
            foreach ($childCodes as $code) {
                $category = ProductCategory::where('code', $code)->first();

                if (!$category) {
                    continue;
                }

                $inserted = $this->insertCategoryForDealer($npp, $category);

                if ($inserted) {
                    $insertedForChild[] = [
                        'code' => $category->code,
                        'category_id' => $category->id,
                    ];
                }
            }

            if ($parent) {
                foreach ($parentCodes as $code) {
                    $category = ProductCategory::where('code', $code)->first();

                    if (!$category) {
                        continue;
                    }

                    $inserted = $this->insertCategoryForDealer($parent, $category);

                    if ($inserted) {
                        $insertedForParent[] = [
                            'code' => $category->code,
                            'category_id' => $category->id,
                        ];
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => [
                'code' => $request->code,
                'parent_code' => $npp->parent_code,
                'category' => $categoryData,
                'child_codes' => $childCodes,
                'parent_codes' => $parentCodes,
                'inserted_for_child' => $insertedForChild,
                'inserted_for_parent' => $insertedForParent,
            ],
        ], 200);
    }
    public function deleteCategoryNpp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'category' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }
        $npp = User::where('code', $request->code)->where('role', 'dealer')->first();
        if (!$npp) {
            return response()->json(['success' => false, 'message' => 'NPP not found'], 404);
        }
        $categoryData = $request->category;
        $codes = array_map(function ($item) {
            return $item['code'];
        }, $categoryData);
        foreach ($codes as $code) {
            $category = ProductCategory::where('code', $code)->first();
            $checkExist = DB::table('npp_product_categories')->where('user_id', $npp->id)->where('category_id', $category->id)->first();
            if ($category && $checkExist) {
                DB::table('npp_product_categories')->where('user_id', $npp->id)->where('category_id', $category->id)->delete();
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
            'data' => [
                'code' => $request->code,
                'category' => $categoryData,
            ],
        ], 200);
    }
}
