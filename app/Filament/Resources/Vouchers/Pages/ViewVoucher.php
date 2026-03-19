<?php

namespace App\Filament\Resources\Vouchers\Pages;

use App\Filament\Resources\Vouchers\VoucherResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

use Filament\Actions\Action;

class ViewVoucher extends ViewRecord
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn ($record) => route('invoices.download', $record))
                ->openUrlInNewTab(),
        ];
    }
}
