<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(80),
                TextInput::make('location')
                    ->label('Ubicación')
                    ->maxLength(120),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->maxLength(20),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(80),
                TextInput::make('contact_person')
                    ->label('Persona de Contacto')
                    ->maxLength(80),
            ]);
    }
}
