<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Schemas\Schema;

class VoucherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Comprobante')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID Interno')
                            ->color('gray'),
                        TextEntry::make('numero')
                            ->label('Número')
                            ->weight('bold')
                            ->state(fn ($record) => $record->data['CbteDesde'] ?? 'N/A'),
                        TextEntry::make('punto_vta')
                            ->label('Punto de Venta')
                            ->state(fn ($record) => $record->data['PtoVta'] ?? 'N/A'),
                        TextEntry::make('fecha')
                            ->label('Fecha Emisión')
                            ->state(fn ($record) => isset($record->data['CbteFch']) ? \Carbon\Carbon::createFromFormat('Ymd', $record->data['CbteFch'])->format('d/m/Y') : 'N/A'),
                        TextEntry::make('customer_name')
                            ->label('Receptor')
                            ->weight('bold'),
                        TextEntry::make('total')
                            ->label('Total Comprobante')
                            ->weight('bold')
                            ->state(fn ($record) => '$' . number_format($record->data['ImpTotal'] ?? 0, 2, ',', '.')),
                        TextEntry::make('cae')
                            ->label('CAE AFIP')
                            ->copyable()
                            ->state(fn ($record) => $record->data['res']['CAE'] ?? 'N/A'),
                        TextEntry::make('vto_cae')
                            ->label('Vencimiento CAE')
                            ->state(fn ($record) => $record->data['res']['CAEFchVto'] ?? 'N/A'),
                    ]),

                Section::make('Artículos Facturados')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label('')
                            ->table([
                                TableColumn::make('product.description', 'Producto'),
                                TableColumn::make('quantity', 'Cant.')->alignment('center'),
                                TableColumn::make('price', 'Precio Unit.')->alignment('right'),
                                TableColumn::make('subtotal', 'Subtotal')->alignment('right'),
                            ])
                            ->schema([
                                TextEntry::make('product.description')
                                    ->placeholder('Sin descripción'),
                                TextEntry::make('quantity'),
                                TextEntry::make('price'),
                                TextEntry::make('subtotal')
                                    ->weight('bold'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
