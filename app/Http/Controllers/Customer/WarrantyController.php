<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarrantyController extends Controller
{
    public function warrantyList(Request $request)
    {
        $headerWhite = 'header-secondary';
        $user = Auth::user();
        $sidebarActive = 'warranty';
        $locale = app()->getLocale();
        $status = strtoupper((string) $request->query('status', ''));
        if (!in_array($status, ['N', 'Y', 'C'], true)) {
            $status = null;
        }

        $baseQuery = $this->warrantyBaseQuery($user->id)
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = trim($request->keyword);
                $query->where(function ($q) use ($keyword) {
                    $q->where('Invoice', 'like', '%' . $keyword . '%')
                        ->orWhere('order_number', 'like', '%' . $keyword . '%')
                        ->orWhere('QRcode', 'like', '%' . $keyword . '%')
                        ->orWhere('Fullname', 'like', '%' . $keyword . '%')
                        ->orWhere('Phone', 'like', '%' . $keyword . '%')
                        ->orWhere('Content', 'like', '%' . $keyword . '%');
                });
            });

        $countQuery = clone $baseQuery;

        $countAll = (clone $countQuery)->count();
        $countNew = (clone $countQuery)->where('Status', 'N')->count();
        $countSuccess = (clone $countQuery)->where('Status', 'Y')->count();
        $countCancel = (clone $countQuery)->where('Status', 'C')->count();

        $listQuery = (clone $baseQuery)
            ->when($status, function ($query) use ($status) {
                $query->where('Status', $status);
            })
            ->orderByDesc('Date')
            ->orderByDesc('id');

        $warranties = $listQuery
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        $ordersByNumber = $this->loadOrdersByWarranties($warranties->getCollection(), $locale);
        $this->attachOrderToCollection($warranties->getCollection(), $ordersByNumber);

        if ($request->ajax()) {
            return view('customer.layout.partials._warranty-list', compact('warranties'))->render();
        }

        return view('customer.layout.warranty', compact(
            'user',
            'headerWhite',
            'sidebarActive',
            'warranties',
            'status',
            'countAll',
            'countNew',
            'countSuccess',
            'countCancel'
        ));
    }

    public function warrantyDetail($id)
    {
        $headerWhite = 'header-secondary';
        $user = Auth::user();
        $sidebarActive = 'warranty';
        $locale = app()->getLocale();

        $warranty = (clone $this->warrantyBaseQuery($user->id))
            ->where('id', $id)
            ->firstOrFail();

        $ordersByNumber = $this->loadOrdersByWarranties(collect([$warranty]), $locale);
        $orderFromOrderNumber = $ordersByNumber->get($this->normalizeOrderKey($warranty->order_number));
        $orderFromInvoice = $ordersByNumber->get($this->normalizeOrderKey($warranty->Invoice));
        $warranty->setRelation('order', $orderFromOrderNumber ?: $orderFromInvoice);

        return view('customer.layout.warranty-detail', compact('user', 'headerWhite', 'sidebarActive', 'warranty'));
    }

    private function warrantyBaseQuery(int $userId)
    {
        $userOrderNumbers = Order::query()
            ->select('order_number')
            ->where('user_id', $userId);

        return Contact::query()
            ->where('Type', 0)
            ->where(function ($query) use ($userId, $userOrderNumbers) {
                $query->where('user_id', $userId)
                    ->orWhere(function ($subQuery) use ($userOrderNumbers) {
                        $subQuery->whereNull('user_id')
                            ->where(function ($matchOrderQuery) use ($userOrderNumbers) {
                                $matchOrderQuery
                                    ->whereIn('order_number', $userOrderNumbers)
                                    ->orWhereIn('Invoice', $userOrderNumbers);
                            });
                    });
            });
    }

    private function loadOrdersByWarranties($warranties, string $locale)
    {
        $orderNumbers = $warranties
            ->flatMap(function ($warranty) {
                return [
                    $warranty->order_number,
                    $warranty->Invoice,
                    is_string($warranty->order_number) ? trim($warranty->order_number) : null,
                    is_string($warranty->Invoice) ? trim($warranty->Invoice) : null,
                ];
            })
            ->filter()
            ->unique()
            ->values();

        if ($orderNumbers->isEmpty()) {
            return collect();
        }

        return Order::query()
            ->whereIn('order_number', $orderNumbers)
            ->with([
                'items.product' => function ($query) {
                    $query->select('id', 'image_urls', 'sku');
                },
                'items.product.translations' => function ($query) use ($locale) {
                    $query->select('product_id', 'language', 'name')
                        ->where('language', $locale);
                },
            ])
            ->get()
            ->mapWithKeys(function ($order) {
                return [$this->normalizeOrderKey($order->order_number) => $order];
            });
    }

    private function attachOrderToCollection($collection, $ordersByNumber): void
    {
        $collection->each(function ($warranty) use ($ordersByNumber) {
            $orderFromOrderNumber = $ordersByNumber->get($this->normalizeOrderKey($warranty->order_number));
            $orderFromInvoice = $ordersByNumber->get($this->normalizeOrderKey($warranty->Invoice));

            // Ưu tiên key nào thực sự khớp order để tránh lệch giữa Invoice và order_number trên contact.
            $matchedOrder = $orderFromOrderNumber ?: $orderFromInvoice;
            $warranty->setRelation('order', $matchedOrder);
        });
    }

    private function normalizeOrderKey(?string $value): string
    {
        return mb_strtoupper(trim((string) $value));
    }
}
