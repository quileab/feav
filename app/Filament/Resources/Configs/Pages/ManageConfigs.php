<?php

namespace App\Filament\Resources\Configs\Pages;

use App\Filament\Resources\Configs\ConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageConfigs extends ManageRecords
{
    protected static string $resource = ConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
