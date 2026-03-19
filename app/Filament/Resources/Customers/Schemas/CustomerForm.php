<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Cliente')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre Fantasía')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('business_name')
                            ->label('Razón Social')
                            ->required()
                            ->maxLength(100),
                        Select::make('customer_id_type_id')
                            ->label('Tipo Identificación')
                            ->relationship('customerIdType', 'value')
                            ->required(),
                        TextInput::make('CUIT')
                            ->label('CUIT/DNI')
                            ->maxLength(11),
                        Select::make('responsibility_type_id')
                            ->label('Responsabilidad Fiscal')
                            ->relationship('responsibilityType', 'value')
                            ->required(),
                    ])->columns(1),

                Section::make('Contacto y Ubicación')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
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
