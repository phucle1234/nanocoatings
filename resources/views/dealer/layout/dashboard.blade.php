@extends('dealer.index')
@section('title', 'Dashboard')
@section('dealer_content')
    <div class="page-dashboard container-xl container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Đại lý</a>
                </li>
                <li class="breadcrumb-item active fs-15 text-black" aria-current="page">Dashboard tổng quan</li>
            </ol>
        </nav>
        <h1 class="font-hanzel fs-32 fw-400">DASHBOARD QUẢN LÝ</h1>
        <p>Để quản lý hoạt động của bạn, hãy sử dụng các liên kết nhanh bên dưới để truy cập nhanh các chức năng.</p>
        <div class="mt-5 row">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div class="statistics row">
                    <div class="col-md-4">
                        <div class="statistics-item p-4 shadow-sm">
                            <div class="statistics-title mt-3 d-flex align-items-center gap-3">
                                <div
                                    class="icon d-flex align-items-center justify-content-center text-center text-white rounded-circle">
                                    <i class="bi bi-graph-up-arrow fs-20"></i>
                                </div>
                                <div class="title fs-18 fw-500">Tổng doanh thu</div>
                            </div>
                            <div class="fs-30 font-hanzel mt-4">{{ number_format($totalRevenue, 0, ',', '.') }}đ</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <b>{{ $revenueGrowth >= 0 ? '+' : '-' }}{{ number_format($revenueGrowth, 2, ',', '.') }}%</b>
                                <span class="text-secondary fs-14">{{ $revenueGrowthAmount }} Tháng trước</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="statistics-item p-4 shadow-sm">
                            <div class="statistics-title mt-3 d-flex align-items-center gap-3">
                                <div
                                    class="icon d-flex align-items-center justify-content-center text-center text-white rounded-circle">
                                    <i class="bi bi-bag-check fs-20"></i>
                                </div>
                                <div class="title fs-18 fw-500">Sản phẩm đã bán</div>
                            </div>
                            <div class="fs-30 font-hanzel mt-4">{{ number_format($totalProductsSold, 0, ',', '.') }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <b>{{ $productsSoldGrowth >= 0 ? '+' : '' }}{{ number_format($productsSoldGrowth, 2, ',', '.') }}%</b>
                                <span class="text-secondary fs-14">
                                    {{ $productsSoldGrowthAmount >= 0 ? '+' : '' }}{{ number_format($productsSoldGrowthAmount, 0, ',', '.') }}
                                    Hôm nay
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="statistics-item p-4 shadow-sm">
                            <div class="statistics-title mt-3 d-flex align-items-center gap-3">
                                <div
                                    class="icon d-flex align-items-center justify-content-center text-center text-white rounded-circle">
                                    <i class="bi bi-people fs-20"></i>
                                </div>
                                <div class="title fs-18 fw-500">Số khách hàng</div>
                            </div>
                            <div class="fs-30 font-hanzel mt-4">{{ number_format($totalCustomers, 0, ',', '.') }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <b>{{ $customersGrowth >= 0 ? '+' : '' }}{{ number_format($customersGrowth, 2, ',', '.') }}%</b>
                                <span class="text-secondary fs-14">
                                    {{ $customersGrowthAmount >= 0 ? '+' : '' }}{{ number_format($customersGrowthAmount, 0, ',', '.') }}
                                    Hôm nay
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
