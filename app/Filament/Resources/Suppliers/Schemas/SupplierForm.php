<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Proveedor')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre/Razón Social')
                            ->required()
                            ->maxLength(255),
                        Select::make('customer_id_type_id')
                            ->label('Tipo Identificación')
                            ->relationship('customerIdType', 'value')
                            ->required(),
                        TextInput::make('CUIT')
                            ->label('CUIT')
                            ->maxLength(11),
                        Select::make('tax_condition_type_id')
                            ->label('Condición IVA')
                            ->relationship('taxConditionType', 'description')
                            ->required(),
                    ])->columns(1),

                Section::make('Contacto y Ubicación')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(127),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->maxLength(40),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(80),
                        Select::make('province_id_type_id')
                            ->label('Provincia')
                            ->relationship('provinceIdType', 'value')
                            ->required(),
                    ])->columns(1),
            ]);
    }
}
