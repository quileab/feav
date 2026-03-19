<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    public function getColumns(): int | array
    {
        return 1;
    }
}
