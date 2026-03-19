<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Comprobante')
                    ->description('Los datos se cargan automáticamente al procesar con AFIP.')
                    ->columns(1)
                    ->schema([
                        KeyValue::make('data')
                            ->label('Datos del Comprobante')
                            ->disabled()
                            ->formatStateUsing(function ($state) {
                                if (!is_array($state)) return $state;
                                return array_map(function($value) {
                                    return is_array($value) || is_object($value) 
                                        ? json_encode($value, JSON_UNESCAPED_UNICODE) 
                                        : $value;
                                }, $state);
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
