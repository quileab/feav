<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;

#[Lazy]
class RevenueChart extends Component
{
    public function placeholder()
    {
        return <<<'HTML'
        <flux:card class="h-80 animate-pulse bg-zinc-50 dark:bg-zinc-900/50 border-zinc-200 dark:border-zinc-800 flex items-center justify-center">
            <flux:text class="text-zinc-400">Cargando gráfico de facturación...</flux:text>
        </flux:card>
        HTML;
    }

    public function render()
    {
        $oneYearAgo = \Carbon\Carbon::now()->subYear()->toDateTimeString();
        
        $monthlyRevenue = DB::table('vouchers')
            ->where('created_at', '>=', $oneYearAgo)
            ->whereRaw("json_valid(data)")
            ->select(
                DB::raw("strftime('%Y-%m', created_at) as month"),
                DB::raw("SUM(json_extract(data, '$.ImpTotal')) as total")
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $stats = [
            'labels' => $monthlyRevenue->pluck('month')->map(fn($m) => \Carbon\Carbon::parse($m)->format('M Y'))->toArray(),
            'values' => $monthlyRevenue->pluck('total')->toArray(),
        ];

        return view('livewire.dashboard.revenue-chart', compact('stats'));
    }
}
