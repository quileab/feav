<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Nro. Comprobante')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('data.DocNro')
                    ->label('Doc. Cliente')
                    ->searchable(),
                TextColumn::make('data.ImpTotal')
                    ->label('Total')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('CbteTipo')
                    ->label('Tipo Comprobante')
                    ->options(fn () => \App\Models\VoucherType::where('enabled', true)->pluck('value', 'id'))
                    ->query(fn (array $data, \Illuminate\Database\Eloquent\Builder $query) => $query->when(
                        $data['value'],
                        fn ($query, $value) => $query->where('id', 'like', $value . '-%')
                    )),
            ])
            ->actions([
                Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => route('invoices.download', $record))
                    ->openUrlInNewTab(),
                Action::make('createNC')
                    ->label('N/C')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->url(fn ($record) => route('filament.admin.resources.vouchers.create', [
                        'asocPtoVta' => $record->data['PtoVta'] ?? null,
                        'asocNro' => $record->data['CbteDesde'] ?? null,
                        'asocTipo' => $record->data['CbteTipo'] ?? null,
                        'asocCustomer' => $record->data['customerId'] ?? null,
                        'asocDocNro' => $record->data['DocNro'] ?? null,
                        'targetMode' => 'NC'
                    ])),
                Action::make('createND')
                    ->label('N/D')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.vouchers.create', [
                        'asocPtoVta' => $record->data['PtoVta'] ?? null,
                        'asocNro' => $record->data['CbteDesde'] ?? null,
                        'asocTipo' => $record->data['CbteTipo'] ?? null,
                        'asocCustomer' => $record->data['customerId'] ?? null,
                        'asocDocNro' => $record->data['DocNro'] ?? null,
                        'targetMode' => 'ND'
                    ])),
                EditAction::make()
                    ->modalWidth('7xl'),
            ]);
    }
}
