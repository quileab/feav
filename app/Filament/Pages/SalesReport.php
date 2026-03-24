<?php

namespace App\Filament\Pages;

use App\Models\Voucher;
use App\Models\VoucherType;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class SalesReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static UnitEnum|string|null $navigationGroup = 'Reportes';
    protected static ?string $navigationLabel = 'Reporte de Ventas (Contador)';
    protected static ?string $title = 'Reporte de Ventas para Contador';

    protected string $view = 'filament.pages.sales-report';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filtros de Reporte')
                    ->description('Seleccione el rango de fechas para exportar los comprobantes FISCALES.')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Fecha Desde')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('end_date')
                            ->label('Fecha Hasta')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportCsv'),
        ];
    }

    public function exportCsv()
    {
        $formData = $this->form->getState();
        $startDate = Carbon::parse($formData['start_date'])->startOfDay();
        $endDate = Carbon::parse($formData['end_date'])->endOfDay();

        $filename = "reporte-ventas-{$startDate->format('Ymd')}-{$endDate->format('Ymd')}.csv";
        
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Fecha',
            'Tipo',
            'Cod. Comprob.',
            'PtoVta',
            'Numero',
            'DocTipo',
            'DocNro',
            'Cliente',
            'Neto',
            'IVA',
            'Total',
            'CAE',
            'Vto CAE'
        ];

        $callback = function() use($startDate, $endDate, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns, ';');

            $vouchers = Voucher::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereRaw("CAST(SUBSTR(id, 1, INSTR(id, '-') - 1) AS INTEGER) < 5000")
                ->orderBy('created_at', 'asc')
                ->cursor();

            foreach ($vouchers as $voucher) {
                $data = $voucher->data;
                $voucherType = \App\Models\VoucherType::find($data['CbteTipo'] ?? 0);
                
                fputcsv($file, [
                    $voucher->created_at->format('d/m/Y H:i'),
                    ($voucherType->letter ?? '') . ' - ' . ($voucherType->value ?? 'S/D'),
                    $data['CbteTipo'] ?? '',
                    str_pad($data['PtoVta'] ?? '', 4, '0', STR_PAD_LEFT),
                    str_pad($data['CbteDesde'] ?? '', 8, '0', STR_PAD_LEFT),
                    $data['DocTipo'] ?? '',
                    $data['DocNro'] ?? '',
                    $voucher->customer_name,
                    number_format($data['ImpNeto'] ?? 0, 2, ',', ''),
                    number_format($data['ImpIVA'] ?? 0, 2, ',', ''),
                    number_format($data['ImpTotal'] ?? 0, 2, ',', ''),
                    $data['res']['CAE'] ?? '',
                    $data['res']['CAEFchVto'] ?? ''
                ], ';');
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
