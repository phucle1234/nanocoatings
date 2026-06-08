<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title>@yield('title', 'Dashboard') - Casumina</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon giống landing -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('langding/imgs/logo3.svg') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('langding/imgs/logo3.svg') }}">
    <link rel="shortcut icon" href="{{ asset('langding/imgs/logo3.svg') }}" type="image/x-icon">


    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=DM+Sans:wght@500&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Slick Carousel CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('langding/libs/slick/slick.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="{{ asset('langding/css/style.css?v=' . time()) }}" rel="stylesheet">
    <link href="{{ asset('customer/css/customer.css?v=' . time()) }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    @include('langding.components.header')
    <div id="page-customer">
        @yield('customer_content')
    </div>
    @include('langding.components.footer')

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="{{ asset('langding/libs/slick/slick.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.0/dist/fancybox/fancybox.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('langding/js/main.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('customer/js/customer.js') }}?v={{ time() }}"></script>

    <!-- toastr Scripts -->
    <meta name="toast-error" content="{{ session('toast_error') }}">
    <meta name="toast-success" content="{{ session('toast_success') }}">
    <meta name="toast-info" content="{{ session('toast_info') }}">
    <meta name="toast-errors-json" content='@json($errors->any() ? $errors->all() : [])'>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('office/js/toastr.js') }}"></script>
    <!-- toastr Scripts -->
    @stack('scripts')
</body>

</html>
