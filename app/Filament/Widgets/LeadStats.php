<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $counts = Lead::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [Stat::make('Tổng yêu cầu', $counts->sum()), Stat::make('Yêu cầu mới', $counts->get('new', 0))->color('info'), Stat::make('Tiềm năng', $counts->get('qualified', 0))->color('success'), Stat::make('Đã hoàn thành', $counts->get('completed', 0))];
    }
}
