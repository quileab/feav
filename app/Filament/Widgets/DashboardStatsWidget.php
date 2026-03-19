<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-stats-widget';
    
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }
}
