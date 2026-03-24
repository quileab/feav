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
            ->defaultSort('created_at', 'desc')
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
                    ->alignment('right')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_fiscal')
                    ->label('Tipo de Emisión')
                    ->placeholder('Todos')
                    ->trueLabel('Fiscales')
                    ->falseLabel('No Fiscales')
                    ->queries(
                        true: fn ($query) => $query->whereRaw("CAST(SUBSTR(id, 1, INSTR(id, '-') - 1) AS INTEGER) < 5000"),
                        false: fn ($query) => $query->whereRaw("CAST(SUBSTR(id, 1, INSTR(id, '-') - 1) AS INTEGER) >= 5000"),
                    ),
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
                    ->visible(fn ($record) => (int) explode('-', $record->id)[0] < 5000)
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
                    ->visible(fn ($record) => (int) explode('-', $record->id)[0] < 5000)
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
