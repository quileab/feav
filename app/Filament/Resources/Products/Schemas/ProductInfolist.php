<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('barcode')
                    ->placeholder('-'),
                TextEntry::make('origin_code')
                    ->placeholder('-'),
                TextEntry::make('category.name')
                    ->label('Category'),
                TextEntry::make('brand')
                    ->placeholder('-'),
                TextEntry::make('model')
                    ->placeholder('-'),
                TextEntry::make('description'),
                TextEntry::make('quantity_min')
                    ->numeric(),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('taxConditionType.id')
                    ->label('Tax condition type'),
                TextEntry::make('unitType.id')
                    ->label('Unit type'),
                TextEntry::make('sale_price1')
                    ->numeric(),
                TextEntry::make('profit_percentage1')
                    ->numeric(),
                TextEntry::make('sale_price2')
                    ->numeric(),
                TextEntry::make('profit_percentage2')
                    ->numeric(),
                TextEntry::make('discount_max')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
