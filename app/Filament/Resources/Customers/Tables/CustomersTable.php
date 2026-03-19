<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->color(fn ($record) => $record->responsibility_type_id == 1 ? 'info' : null)
                    ->weight(fn ($record) => $record->responsibility_type_id == 1 ? 'bold' : null),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->color(fn ($record) => $record->responsibility_type_id == 1 ? 'info' : null)
                    ->weight(fn ($record) => $record->responsibility_type_id == 1 ? 'bold' : null),
                TextColumn::make('business_name')
                    ->label('Razón Social')
                    ->searchable()
                    ->sortable()
                    ->color(fn ($record) => $record->responsibility_type_id == 1 ? 'info' : null)
                    ->weight(fn ($record) => $record->responsibility_type_id == 1 ? 'bold' : null),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('createInvoice')
                    ->label('Factura')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->url(fn ($record) => route('filament.admin.resources.vouchers.create', [
                        'asocCustomer' => $record->id
                    ])),
                EditAction::make(),
            ]);
    }
}
