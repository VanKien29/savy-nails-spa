<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'Tổng quan';

    protected string $view = 'filament.pages.dashboard';

    public function getSubheading(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('website')->label('Mở website')->icon('heroicon-o-arrow-top-right-on-square')->url('/')->openUrlInNewTab()];
    }
}
