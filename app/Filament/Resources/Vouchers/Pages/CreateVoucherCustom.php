<?php

namespace App\Filament\Resources\Vouchers\Pages;

use App\Filament\Resources\Vouchers\VoucherResource;
use Filament\Resources\Pages\Page;

class CreateVoucherCustom extends Page
{
    protected static string $resource = VoucherResource::class;

    protected static ?string $title = '';

    protected static ?string $breadcrumb = 'Emitir Comprobante';

    protected string $view = 'filament.resources.vouchers.pages.create-voucher-custom';

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
