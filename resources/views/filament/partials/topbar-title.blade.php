@php
    $route = request()->route()?->getName() ?? '';
    $label = match(true) {
        str_contains($route, 'website-content') => 'Nội dung website', str_contains($route, 'footer-settings') => 'Cài đặt Footer',
        str_contains($route, 'seo-settings') => 'SEO', str_contains($route, 'script-widgets') => 'Script / Widget',
        str_contains($route, 'media-library') => 'Thư viện ảnh', str_contains($route, 'header-menu') => 'Header kéo thả',
        str_contains($route, 'service-menu') => 'Menu dịch vụ',
        str_contains($route, 'users') => 'Tài khoản Admin',
        str_contains($route, 'audit-logs') => 'Nhật ký hoạt động',
        str_contains($route, 'site-settings') => 'Cấu hình website', str_contains($route, 'profile') => 'Hồ sơ tài khoản',
        default => 'Tổng quan',
    };
@endphp
<h2 class="sample-topbar-title">{{ $label }}</h2>
