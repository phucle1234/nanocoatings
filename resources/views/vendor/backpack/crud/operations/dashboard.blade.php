@extends(backpack_view('blank'))

@section('content')
<style>
    .dashboard-stats {
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        transition: transform 0.2s ease;
        height: 120px;
        display: flex;
        align-items: center;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.8;
        margin-right: 15px;
    }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.8;
        margin: 5px 0 0 0;
    }
    
    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }
    
    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .info-box {
        background: white;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
    }
    
    .info-box-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .info-box-content {
        flex: 1;
    }
    
    .info-box-number {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0;
        line-height: 1;
    }
    
    .info-box-text {
        font-size: 0.85rem;
        color: #666;
        margin: 3px 0 0 0;
    }
    
    /* Color classes */
    .bg-primary-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-success-gradient {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .bg-warning-gradient {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .bg-info-gradient {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .bg-danger-gradient {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .text-primary-gradient {
        color: #667eea;
    }
    
    .text-success-gradient {
        color: #11998e;
    }
    
    .text-warning-gradient {
        color: #f093fb;
    }
    
    .text-info-gradient {
        color: #4facfe;
    }
    
    .text-danger-gradient {
        color: #fa709a;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stat-card {
            height: auto;
            padding: 15px;
        }
        
        .stat-number {
            font-size: 1.5rem;
        }
        
        .stat-icon {
            font-size: 2rem;
        }
        
        .chart-wrapper {
            height: 250px;
        }
    }
</style>

<div class="dashboard-stats">
    <!-- Thống kê tổng quan -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card bg-primary-gradient text-white">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($stats['total_products']) }}</div>
                    <div class="stat-label">Tổng sản phẩm</div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card bg-success-gradient text-white">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($stats['total_orders']) }}</div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card bg-warning-gradient text-white">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($stats['total_users']) }}</div>
                    <div class="stat-label">Tổng người dùng</div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card bg-danger-gradient text-white">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($stats['total_revenue'], 0, ',', '.') }}₫</div>
                    <div class="stat-label">Tổng doanh thu</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Biểu đồ đơn hàng -->
    <div class="col-lg-8 mb-4">
        <div class="chart-container">
            <div class="chart-title">
                <i class="fas fa-chart-line text-primary-gradient"></i> Thống kê đơn hàng
            </div>
            <div class="chart-wrapper">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Thống kê đơn hàng theo trạng thái -->
    <div class="col-lg-4 mb-4">
        <div class="chart-container">
            <div class="chart-title">
                <i class="fas fa-list text-success-gradient"></i> Đơn hàng theo trạng thái
            </div>
            <div class="row">
                <div class="col-6 mb-2">
                    <div class="info-box">
                        <div class="info-box-icon bg-warning-gradient">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-box-content">
                            <div class="info-box-number">{{ $stats['pending_orders'] }}</div>
                            <div class="info-box-text">Chờ xử lý</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="info-box">
                        <div class="info-box-icon bg-info-gradient">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="info-box-content">
                            <div class="info-box-number">{{ $stats['processing_orders'] }}</div>
                            <div class="info-box-text">Đang xử lý</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="info-box">
                        <div class="info-box-icon bg-success-gradient">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="info-box-content">
                            <div class="info-box-number">{{ $stats['shipped_orders'] }}</div>
                            <div class="info-box-text">Đã giao</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="info-box">
                        <div class="info-box-icon bg-danger-gradient">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="info-box-content">
                            <div class="info-box-number">{{ $stats['cancelled_orders'] }}</div>
                            <div class="info-box-text">Đã hủy</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Biểu đồ doanh thu -->
    <div class="col-lg-6 mb-4">
        <div class="chart-container">
            <div class="chart-title">
                <i class="fas fa-chart-bar text-info-gradient"></i> Doanh thu theo thời gian
            </div>
            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Thống kê sản phẩm -->
    <div class="col-lg-6 mb-4">
        <div class="chart-container">
            <div class="chart-title">
                <i class="fas fa-box text-warning-gradient"></i> Thống kê sản phẩm
            </div>
            <div class="row">
                <div class="col-6 mb-2">
                    <div class="info-box">
                        <div class="info-box-icon bg-success-gradient">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="info-box-content">
                            <div class="info-box-number">{{ $stats['featured_products'] }}</div>
                            <div class="info-box-text">Nổi bật</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="info-box">
                        <div class="info-box-icon bg-info-gradient">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="info-box-content">
                            <div class="info-box-number">{{ $stats['new_products'] }}</div>
                            <div class="info-box-text">Mới</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="info-box">
                        <div class="info-box-icon bg-danger-gradient">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="info-box-content">
                            <div class="info-box-number">{{ $stats['bestseller_products'] }}</div>
                            <div class="info-box-text">Bán chạy</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="info-box">
                        <div class="info-box-icon bg-warning-gradient">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="info-box-content">
                            <div class="info-box-number">{{ $stats['low_stock_products'] }}</div>
                            <div class="info-box-text">Sắp hết hàng</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Thống kê theo thời gian -->
    <div class="col-lg-4 mb-4">
        <div class="chart-container">
            <div class="chart-title">
                <i class="fas fa-calendar text-primary-gradient"></i> Đơn hàng theo thời gian
            </div>
            <div class="info-box">
                <div class="info-box-icon bg-info-gradient">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="info-box-content">
                    <div class="info-box-number">{{ $stats['orders_today'] }}</div>
                    <div class="info-box-text">Hôm nay</div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-icon bg-success-gradient">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="info-box-content">
                    <div class="info-box-number">{{ $stats['orders_this_week'] }}</div>
                    <div class="info-box-text">Tuần này</div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-icon bg-warning-gradient">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="info-box-content">
                    <div class="info-box-number">{{ $stats['orders_this_month'] }}</div>
                    <div class="info-box-text">Tháng này</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê người dùng -->
    <div class="col-lg-4 mb-4">
        <div class="chart-container">
            <div class="chart-title">
                <i class="fas fa-users text-success-gradient"></i> Thống kê người dùng
            </div>
            <div class="info-box">
                <div class="info-box-icon bg-success-gradient">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="info-box-content">
                    <div class="info-box-number">{{ $stats['active_users'] }}</div>
                    <div class="info-box-text">Hoạt động</div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-icon bg-danger-gradient">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="info-box-content">
                    <div class="info-box-number">{{ $stats['admin_users'] }}</div>
                    <div class="info-box-text">Quản trị viên</div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-icon bg-info-gradient">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="info-box-content">
                    <div class="info-box-number">{{ $stats['new_users_today'] }}</div>
                    <div class="info-box-text">Mới hôm nay</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê doanh thu -->
    <div class="col-lg-4 mb-4">
        <div class="chart-container">
            <div class="chart-title">
                <i class="fas fa-money-bill-wave text-danger-gradient"></i> Thống kê doanh thu
            </div>
            <div class="info-box">
                <div class="info-box-icon bg-success-gradient">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="info-box-content">
                    <div class="info-box-number">{{ number_format($stats['total_revenue'], 0, ',', '.') }}₫</div>
                    <div class="info-box-text">Tổng doanh thu</div>
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-icon bg-info-gradient">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="info-box-content">
                    <div class="info-box-number">{{ number_format($stats['monthly_revenue'], 0, ',', '.') }}₫</div>
                    <div class="info-box-text">Tháng này</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Biểu đồ đơn hàng
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Đơn hàng',
                data: [],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Biểu đồ doanh thu
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Doanh thu (₫)',
                data: [],
                backgroundColor: 'rgba(79, 172, 254, 0.8)',
                borderColor: '#4facfe',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Load dữ liệu biểu đồ
    function loadChartData(chart, type, period) {
        fetch(`{{ backpack_url('dashboard/chart-data') }}?type=${type}&period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (data && Array.isArray(data)) {
                    chart.data.labels = data.map(item => item.label);
                    chart.data.datasets[0].data = data.map(item => item.value);
                    chart.update();
                }
            })
            .catch(error => {
                console.error('Error loading chart data:', error);
                // Fallback data nếu API lỗi
                chart.data.labels = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
                chart.data.datasets[0].data = [0, 0, 0, 0, 0, 0, 0];
                chart.update();
            });
    }

    // Load dữ liệu ban đầu
    document.addEventListener('DOMContentLoaded', function() {
        loadChartData(ordersChart, 'orders', 'week');
        loadChartData(revenueChart, 'revenue', 'week');
    });

    // Cập nhật biểu đồ mỗi 5 phút
    setInterval(function() {
        loadChartData(ordersChart, 'orders', 'week');
        loadChartData(revenueChart, 'revenue', 'week');
    }, 300000);
</script>
@endsection