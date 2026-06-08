<?php

namespace App\Helpers;

use Symfony\Component\Routing\Route;

class DealerHelper
{

    public static function saleStatus($status)
    {
        return [
            0 => 'Mới',
            1 => 'Xác nhận',
            2 => 'Xuất kho',
            3 => 'Xuất hóa đơn',
            4 => 'Đang giao hàng',
            5 => 'Hoàn tất',
        ];
    }
    public static function saleStatusWithRoute($type = 'ecommerce')
    {
        if ($type == 'ecommerce') {
            return [
                route('dealer.ecommerce') => 'Tất cả',
                route('dealer.ecommerce-new') => 'Mới',
                route('dealer.ecommerce-pending') => 'Xác nhận',
                route('dealer.ecommerce-warehouse') => 'Xuất kho',
                route('dealer.ecommerce-invoice') => 'Xuất hóa đơn',
                route('dealer.ecommerce-delivery') => 'Đang giao hàng',
                route('dealer.ecommerce-completed') => 'Hoàn tất',
                route('dealer.ecommerce-cancelled') => 'Hủy',
            ];
        } else {
            return [
                route('dealer.sale-order-history') => 'Tất cả',
                route('dealer.sale-order-history-new') => 'Mới',
                route('dealer.sale-order-history-pending') => 'Xác nhận',
                route('dealer.sale-order-history-warehouse') => 'Xuất kho',
                route('dealer.sale-order-history-invoice') => 'Xuất hóa đơn',
                route('dealer.sale-order-history-delivery') => 'Đang giao hàng',
                route('dealer.sale-order-history-completed') => 'Hoàn tất',
                route('dealer.sale-order-history-cancelled') => 'Hủy',
            ];
        }
    }
    public static function saleStatusHtml($status)
    {
        switch ($status) {
            case 0:
                return '<span class="badge bg-secondary text-white fs-12 fw-400">Mới</span>';
            case 1:
                return '<span class="badge bg-warning text-white fs-12 fw-400">Xác nhận</span>';
            case 2:
                return '<span class="badge bg-warning text-white fs-12 fw-400">Xuất kho</span>';
            case 3:
                return '<span class="badge bg-warning text-white fs-12 fw-400">Xuất hóa đơn</span>';
            case 4:
                return '<span class="badge bg-warning text-white fs-12 fw-400">Đang giao hàng</span>';
            case 5:
                return '<span class="badge bg-success text-white fs-12 fw-400">Hoàn tất</span>';
            case -1:
                return '<span class="badge bg-danger text-white fs-12 fw-400">Hủy</span>';
            default:
                return '';
        }
    }

    public static function buyStatusWithRoute()
    {
        return [
            route('dealer.order-history') => 'Tất cả',
            route('dealer.order-history-new') => 'Mới',
            route('dealer.order-history-pending') => 'Xác nhận',
            route('dealer.order-history-responded') => 'CSM đã phản hồi',
            route('dealer.order-history-created') => 'CSM tạo đơn hàng',
            route('dealer.order-history-invoiced') => 'CSM xuất hóa đơn',
            route('dealer.order-history-completed') => 'Hoàn tất',
            route('dealer.order-history-cancelled') => 'Hủy',
        ];
    }
    public static function buyStatusHtml($status)
    {
        switch ($status) {
            case 0:
                return '<span class="badge bg-secondary text-white fs-12 fw-400">Mới</span>';
            case 1:
                return '<span class="badge bg-warning text-white fs-12 fw-400">Xác nhận</span>';
            case 2:
                return '<span class="badge bg-warning text-white fs-12 fw-400">CSM đã phản hồi</span>';
            case 3:
                return '<span class="badge bg-warning text-white fs-12 fw-400">CSM tạo đơn hàng</span>';
            case 4:
                return '<span class="badge bg-warning text-white fs-12 fw-400">CSM xuất hóa đơn</span>';
            case 5:
                return '<span class="badge bg-success text-white fs-12 fw-400">Hoàn tất</span>';
            case -1:
                return '<span class="badge bg-danger text-white fs-12 fw-400">Hủy</span>';
            default:
                return '';
        }
    }
}
