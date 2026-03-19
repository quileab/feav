<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Producto')
                    ->schema([
                        TextInput::make('description')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('barcode')
                            ->label('Código de Barras')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(30),
                        TextInput::make('model')
                            ->label('Modelo')
                            ->maxLength(30),
                        Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->required(),
                    ])->columns(1),

                Section::make('Precios e Impuestos')
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio de Costo')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                $price = (float) $state;
                                $profit1 = (float) $get('profit_percentage1');
                                $profit2 = (float) $get('profit_percentage2');
                                
                                if ($price > 0) {
                                    $set('sale_price1', round($price * (1 + ($profit1 / 100)), 2));
                                    $set('sale_price2', round($price * (1 + ($profit2 / 100)), 2));
                                }
                            })
                            ->prefix('$'),
                        Select::make('tax_condition_type_id')
                            ->label('Condición de IVA')
                            ->relationship('taxConditionType', 'description')
                            ->required(),
                        Select::make('unit_type_id')
                            ->label('Unidad de Medida')
                            ->relationship('unitType', 'description')
                            ->required(),
                        TextInput::make('sale_price1')
                            ->label('Precio de Venta 1')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                $price = (float) $get('price');
                                $salePrice = (float) $state;
                                if ($price > 0 && $salePrice > 0) {
                                    $set('profit_percentage1', round((($salePrice / $price) - 1) * 100, 2));
                                }
                            })
                            ->prefix('$'),
                        TextInput::make('profit_percentage1')
                            ->label('% Ganancia 1')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                $price = (float) $get('price');
                                $profit = (float) $state;
                                if ($price > 0) {
                                    $set('sale_price1', round($price * (1 + ($profit / 100)), 2));
                                }
                            })
                            ->suffix('%'),
                        TextInput::make('sale_price2')
                            ->label('Precio de Venta 2')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                $price = (float) $get('price');
                                $salePrice = (float) $state;
                                if ($price > 0 && $salePrice > 0) {
                                    $set('profit_percentage2', round((($salePrice / $price) - 1) * 100, 2));
                                }
                            })
                            ->prefix('$'),
                        TextInput::make('profit_percentage2')
                            ->label('% Ganancia 2')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                $price = (float) $get('price');
                                $profit = (float) $state;
                                if ($price > 0) {
                                    $set('sale_price2', round($price * (1 + ($profit / 100)), 2));
                                }
                            })
                            ->suffix('%'),
                    ])->columns(1),
            ]);
    }

}
