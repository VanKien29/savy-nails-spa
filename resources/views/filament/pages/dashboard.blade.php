<x-filament-panels::page>
    @php
        $contentAccess = \App\Services\CmsAccess::allows('content');
        $adminAccess = \App\Services\CmsAccess::allows('users');
        $cards = $contentAccess ? [
            ['label'=>'Nội dung website','value'=>\App\Models\Page::publiclyAccessible()->count(),'note'=>'Trang nội dung đang hiển thị','icon'=>'heroicon-o-pencil-square','color'=>'amber','url'=>\App\Filament\Pages\WebsiteContent::getUrl()],
            ['label'=>'Dịch vụ','value'=>\App\Models\SalonService::where('active', true)->count(),'note'=>'Dịch vụ trong menu salon','icon'=>'heroicon-o-sparkles','color'=>'blue','url'=>\App\Filament\Pages\ServiceMenu::getUrl()],
            ['label'=>'Thư viện ảnh','value'=>\App\Models\Media::count(),'note'=>'Ảnh và tệp đã tải lên','icon'=>'heroicon-o-photo','color'=>'violet','url'=>\App\Filament\Pages\MediaLibrary::getUrl()],
        ] : [];
        if($adminAccess) $cards[]=['label'=>'Tài khoản Admin','value'=>\App\Models\User::where('is_admin',true)->count(),'note'=>'Tài khoản có quyền quản trị','icon'=>'heroicon-o-shield-check','color'=>'green','url'=>\App\Filament\Resources\UserResource::getUrl()];
    @endphp
    <div class="sample-dashboard">
        <div class="sample-dashboard-hero"><p>{{ app(\App\Services\SiteSettingsService::class)->get('site_name') }}</p><h1>Quản lý toàn bộ nội dung website tại một nơi</h1><div>Sửa chữ, thay ảnh, quản lý dịch vụ, SEO và tài khoản Admin. Các thay đổi nội dung giữ nguyên bố cục Client đã chốt.</div>@if($contentAccess)<a href="{{ \App\Filament\Pages\WebsiteContent::getUrl() }}"><x-filament::icon icon="heroicon-o-pencil-square"/> Chỉnh nội dung website</a>@endif</div>
        <div class="sample-dashboard-cards">@foreach($cards as $card)<a href="{{ $card['url'] }}"><div><div><p>{{ $card['label'] }}</p><strong>{{ number_format($card['value']) }}</strong></div><span class="sample-stat-icon is-{{ $card['color'] }}"><x-filament::icon :icon="$card['icon']"/></span></div><small>{{ $card['note'] }}</small></a>@endforeach</div>
        <div class="sample-quick-panel"><h2>Truy cập nhanh</h2><div>
            @if($contentAccess)
            <a href="{{ \App\Filament\Pages\WebsiteContent::getUrl() }}"><x-filament::icon icon="heroicon-o-pencil-square"/>Chỉnh nội dung website</a>
            <a href="{{ \App\Filament\Pages\ServiceMenu::getUrl() }}"><x-filament::icon icon="heroicon-o-plus"/>Chỉnh menu dịch vụ</a>
            <a href="{{ \App\Filament\Pages\MediaLibrary::getUrl() }}"><x-filament::icon icon="heroicon-o-cloud-arrow-up"/>Tải ảnh lên</a>
            @endif
            @if($adminAccess)<a href="{{ \App\Filament\Pages\FooterSettings::getUrl() }}"><x-filament::icon icon="heroicon-o-window"/>Cài đặt Footer</a><a href="{{ \App\Filament\Pages\SeoSettings::getUrl() }}"><x-filament::icon icon="heroicon-o-magnifying-glass"/>Cài đặt SEO</a>@endif
            <a href="{{ filament()->getProfileUrl() }}"><x-filament::icon icon="heroicon-o-lock-closed"/>Mật khẩu & bảo mật</a>
        </div></div>
    </div>
</x-filament-panels::page>
