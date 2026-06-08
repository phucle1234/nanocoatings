<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#tab_basic" data-toggle="tab">
                <i class="fas fa-info-circle"></i> Thông tin cơ bản
            </a>
        </li>
        <li>
            <a href="#tab_pricing" data-toggle="tab">
                <i class="fas fa-dollar-sign"></i> Giá & Kho
            </a>
        </li>
        <li>
            <a href="#tab_images" data-toggle="tab">
                <i class="fas fa-images"></i> Hình ảnh
            </a>
        </li>
        <li>
            <a href="#tab_attributes" data-toggle="tab">
                <i class="fas fa-tags"></i> Thuộc tính
            </a>
        </li>
        <li>
            <a href="#tab_status" data-toggle="tab">
                <i class="fas fa-toggle-on"></i> Trạng thái
            </a>
        </li>
        <li>
            <a href="#tab_translations" data-toggle="tab">
                <i class="fas fa-globe"></i> Đa ngôn ngữ
            </a>
        </li>
        <li>
            <a href="#tab_seo" data-toggle="tab">
                <i class="fas fa-search"></i> SEO
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <!-- Tabs content will be populated by the controller -->
    </div>
</div>

<style>
    .nav-tabs-custom {
        margin-bottom: 20px;
    }

    .nav-tabs-custom>.nav-tabs {
        border-bottom: 2px solid #d2d6de;
    }

    .nav-tabs-custom>.nav-tabs>li {
        border-top: 3px solid transparent;
        margin-bottom: -2px;
    }

    .nav-tabs-custom>.nav-tabs>li>a {
        border-radius: 0;
        color: #666;
        font-weight: 500;
    }

    .nav-tabs-custom>.nav-tabs>li>a:hover {
        border-color: transparent;
        color: #333;
    }

    .nav-tabs-custom>.nav-tabs>li.active {
        border-top-color: #3c8dbc;
    }

    .nav-tabs-custom>.nav-tabs>li.active>a {
        border-top-color: transparent;
        border-left-color: #d2d6de;
        border-right-color: #d2d6de;
        color: #333;
        font-weight: 600;
    }

    .nav-tabs-custom>.nav-tabs>li.active>a:hover {
        border-top-color: transparent;
        border-left-color: #d2d6de;
        border-right-color: #d2d6de;
    }

    .nav-tabs-custom>.tab-content {
        background: #fff;
        padding: 20px;
        border: 1px solid #d2d6de;
        border-top: none;
        border-radius: 0 0 3px 3px;
    }

    .nav-tabs-custom>.nav-tabs>li>a i {
        margin-right: 5px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }
</style>

<script>
    $(document).ready(function() {
        // Tab switching functionality
        $('.nav-tabs a').click(function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Show first tab by default
        $('.nav-tabs a:first').tab('show');
    });
</script>