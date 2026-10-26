<?php

namespace App\Filament\Pages;

use App\Models\NavigationItem;
use App\Models\SalonProfile;
use App\Services\CmsAccess;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class HeaderMenu extends Page
{
    protected static ?string $title = 'Header kéo thả';

    protected string $view = 'filament.pages.sample-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return CmsAccess::allows('content');
    }

    public function mount(): void
    {
        $settings = collect(app(SiteSettingsService::class)->all())->only(['logo_id', 'mobile_logo_id', 'favicon_id'])->all();
        $this->form->fill(['settings' => $settings, 'profile' => ['copy' => ['global' => app(SalonContent::class)->all()['profile']['copy']['global']]], 'links' => NavigationItem::where('location', 'header')->orderBy('sort_order')->get(['label', 'url', 'enabled'])->toArray()]);
    }

    public function form(Schema $schema): Schema
    {
        $fields = [];
        if (CmsAccess::allows('settings')) {
            $fields[] = Section::make('Logo & nhận diện')->schema([
                WebsiteContent::mediaPicker('settings.logo_id', 'Logo desktop'), WebsiteContent::mediaPicker('settings.mobile_logo_id', 'Logo mobile'), WebsiteContent::mediaPicker('settings.favicon_id', 'Favicon'),
            ])->collapsible();
        }
        $fields[] = Section::make('Thương hiệu & hỗ trợ truy cập')->description('Chữ thương hiệu dùng khi chưa có logo.')->schema(WebsiteContent::copyFields('global', ['brand_monogram', 'brand_title', 'brand_subtitle', 'skip_content']))->columns(2)->collapsed();
        $fields[] = Section::make('Menu điều hướng')->description('Kéo thả các mục để đổi thứ tự. Tắt hiển thị để ẩn một mục trên cả desktop và mobile.')->schema([
            F\Repeater::make('links')->label('Danh sách menu')->schema([
                F\TextInput::make('label')->label('Tên hiển thị')->required()->maxLength(80),
                F\TextInput::make('url')->label('Đường dẫn')->required()->maxLength(500)->rules(['regex:~^(?:/(?!/)[^\s]*|#[a-zA-Z][\w-]*|https://[^\s]+|mailto:[^\s]+|tel:\+?[0-9 -]+)$~'])->helperText('Ví dụ: /services hoặc https://...'),
                F\Toggle::make('enabled')->label('Hiển thị')->default(true),
            ])->itemLabel(fn (array $state) => $state['label'] ?? 'Mục menu mới')->columns(2)->reorderable()->collapsible()->maxItems(24)->defaultItems(0)->addActionLabel('Thêm mục menu')->deleteAction(fn ($action) => $action->requiresConfirmation()),
        ]);
        $fields[] = Section::make('Nút đặt lịch trên Header')->schema(WebsiteContent::copyFields('global', ['booking_label', 'booking_url']))->columns(2);

        return $schema->components($fields)->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $state = $this->form->getState();
        DB::transaction(function () use ($state) {
            if (CmsAccess::allows('settings') && isset($state['settings'])) {
                app(SiteSettingsService::class)->update(array_replace(app(SiteSettingsService::class)->all(), $state['settings']));
            }
            $profile = SalonProfile::whereKey(1)->lockForUpdate()->firstOrFail();
            $data = $profile->data;
            $data['copy']['global'] = array_replace($data['copy']['global'] ?? [], $state['profile']['copy']['global']);
            $profile->update(['data' => $data]);
            foreach (NavigationItem::where('location', 'header')->get() as $item) {
                $item->delete();
            }
            foreach ($state['links'] ?? [] as $order => $link) {
                NavigationItem::create($link + ['location' => 'header', 'sort_order' => $order]);
            }
        });
        Notification::make()->title('Đã lưu Header & menu')->success()->send();
    }
}
