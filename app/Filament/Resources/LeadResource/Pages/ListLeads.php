<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use App\Services\LeadCsvExporter;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('export')->label('Xuất CSV')->action(fn () => app(LeadCsvExporter::class)->download($this->tableFilters['status']['value'] ?? null))];
    }
}
