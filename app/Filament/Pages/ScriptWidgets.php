<?php

namespace App\Filament\Pages;

use App\Services\SiteSettingsService;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScriptWidgets extends SeoSettings
{
    protected static ?string $title = 'Script / Widget';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Head')->description('Mã được chèn ngay trước thẻ </head> trên các trang Client.')->schema([
                F\Textarea::make('script_head')->label('Mã Head')->rows(12)->nullable()->string()
                    ->placeholder('<!-- Dán HTML, CSS, JavaScript hoặc mã nhúng vào đây -->')
                    ->extraInputAttributes(['spellcheck' => 'false', 'autocapitalize' => 'off', 'autocomplete' => 'off', 'style' => 'font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 13px; tab-size: 2;']),
            ]),
            Section::make('Body')->description('Mã được chèn ngay trước thẻ </body> trên các trang Client.')->schema([
                F\Textarea::make('script_body_end')->label('Mã Body')->rows(12)->nullable()->string()
                    ->placeholder('<!-- Dán widget chat, HTML hoặc script cuối trang vào đây -->')
                    ->extraInputAttributes(['spellcheck' => 'false', 'autocapitalize' => 'off', 'autocomplete' => 'off', 'style' => 'font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 13px; tab-size: 2;']),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $settings = app(SiteSettingsService::class);
        $settings->update(array_replace($settings->all(), $this->form->getState()));
        Notification::make()->title('Đã lưu Script / Widget')->success()->send();
    }
}
