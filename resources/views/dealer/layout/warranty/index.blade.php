@extends('dealer.index')
@section('title', 'Dashboard')
@section('dealer_content')
    <div class="page-warranty container-xl container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="" class="fs-15 text-black">Đại lý</a>
                </li>
                <li class="breadcrumb-item active fs-15 text-black" aria-current="page">Quản lý hàng hóa</li>
            </ol>
        </nav>
        <h1 class="font-hanzel fs-42 fw-400 mt-5">CHỨNG NHẬN BẢO HÀNH</h1>
        <p>Nơi kiểm tra và theo dõi đơn hàng, lịch sử mua hàng cũng như chứng nhận cho từng sản phẩm đã mua.</p>
        <div class="mt-5 row">
            <div class="col-xl-3">
                @include('dealer.components.sidebar')
            </div>
            <div class="col-xl-9">
                <div id="warranty-search" data-url="{{ route('dealer.warranty-search') }}">
                    <div class="warranty-option-1">
                        <div class="warranty-option-item d-flex align-items-center gap-2">
                            <div
                                class="warranty-option-number fw-600 text-white d-flex align-items-center justify-content-center">
                                1
                            </div>
                            <div class="warranty-option-name fw-600 text-red">Tìm kiếm bằng nhập mã Qrcode</div>
                        </div>
                        <div class="mt-5 text-center">
                            <label for="qrcode" class="form-label fw-600 fs-14">Nhập mã QRcode sản phẩm <span
                                    class="text-muted fw-400">hoặc</span> Đơn hàng</label>
                            <div class="row">
                                <div class="d-flex justify-content-center">
                                    <div class="col-md-9">
                                        <input type="text" class="form-control bg-body-secondary fs-14 py-3"
                                            id="qrcode" name="qrcode" placeholder="Ex: ABC123">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button class="btn btn-danger text-white rounded-1 px-4 py-3 fw-500 btn-search">KIỂM
                                    TRA</button>
                            </div>
                        </div>
                    </div>
                    <div class="warranty-option-2 mt-5">
                        <div class="warranty-option-item d-flex align-items-center gap-2">
                            <div
                                class="warranty-option-number fw-600 text-white d-flex align-items-center justify-content-center">
                                2
                            </div>
                            <div class="warranty-option-name fw-600 text-red">Tìm kiếm bằng quét mã QR</div>
                        </div>
                        <div class="text-center mt-5">
                            {{-- Vùng render camera --}}
                            <div id="qr-reader" class="d-none w-100"></div>
                            <img src="{{ asset('dealer/imgs/qr-scan.jpg') }}" class="img-fluid" width="200">
                        </div>
                        <div class="row">
                            <div class="d-flex justify-content-center">
                                <div class="col-md-6">
                                    <div class="mt-3">Để bắt đầu tra cứu bằng mã QR, vui lòng sử dụng thiết bị được trang
                                        bị
                                        bởi hãng của bạn để quét mã trên sản phẩm</div>
                                    <button id="btn-open-camera"
                                        class="btn w-100 btn-outline-danger text-red rounded-pill px-2 py-1 fw-500 mt-3">
                                        <i class="bi bi-camera me-2 fs-20"></i>Mở quyền truy cập thiết bị
                                    </button>
                                    <button id="btn-stop-camera"
                                        class="btn w-100 btn-danger text-white rounded-pill px-2 py-1 fw-500 mt-3 d-none">
                                        <i class="bi bi-x-circle me-2 fs-20"></i>Đóng camera
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('dealer/js/warranty/index.js') }}"></script>
@endpush
