<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title>@yield('title', __('auth.page_title')) - Casumina</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon giống landing -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('langding/imgs/logo3.svg') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('langding/imgs/logo3.svg') }}">
    <link rel="shortcut icon" href="{{ asset('langding/imgs/logo3.svg') }}" type="image/x-icon">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=DM+Sans:wght@500&family=Poppins:wght@600;700&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5 giống landing -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <!-- Custom CSS -->
    <link href="{{ asset('langding/css/login.css?v=' . time()) }}" rel="stylesheet">
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Auth Container -->
    <div class="container">
        <div class="auth-container">
            @yield('content')
        </div>
    </div>
    @if (session('toast_success'))
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white border-0">
                <strong class="me-auto">{{ __('auth.toast_notification') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-white border-0">
                {{ session('toast_success') }}
            </div>
        </div>
    </div>
    @endif
    @if (session('toast_error'))
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastNotification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white border-0">
                <strong class="me-auto">{{ __('auth.toast_error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-white border-0">
                {{ session('toast_error') }}
            </div>
        </div>
    </div>
    @endif
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // Form submit với loading
            $('.auth-form').on('submit', function(e) {
                const $form = $(this);
                const $submitBtn = $form.find('button[type="submit"]');

                // Prevent double submit
                if ($submitBtn.prop('disabled')) {
                    e.preventDefault();
                    return false;
                }

                // Show loading
                $submitBtn.prop('disabled', true);
                const originalText = $submitBtn.html();
                $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __('
                    auth.processing ') }}');
                $('#loadingOverlay').addClass('active');

                // Enable lại sau 3 giây để tránh treo
                setTimeout(function() {
                    if ($submitBtn.prop('disabled')) {
                        $submitBtn.prop('disabled', false).html(originalText);
                        $('#loadingOverlay').removeClass('active');
                    }
                }, 3000);
            });

            const toastElement = document.getElementById('toastNotification');
            const toastOptions = {
                autohide: true,
                delay: 3000
            };
            const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastElement, toastOptions);
            toastBootstrap.show();
        });
    </script>

    @stack('scripts')
</body>

</html>