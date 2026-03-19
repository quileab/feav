<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\Voucher;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

#[Lazy]
class SummaryCards extends Component
{
    public function placeholder()
    {
        return <<<'HTML'
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 animate-pulse">
            <div class="h-24 bg-zinc-100 dark:bg-zinc-800 rounded-xl"></div>
            <div class="h-24 bg-zinc-100 dark:bg-zinc-800 rounded-xl"></div>
            <div class="h-24 bg-zinc-100 dark:bg-zinc-800 rounded-xl"></div>
            <div class="h-24 bg-zinc-100 dark:bg-zinc-800 rounded-xl"></div>
        </div>
        HTML;
    }

    public function render()
    {
        $oneYearAgo = \Carbon\Carbon::now()->subYear()->toDateTimeString();
        
        // Usamos SQL puro con validación de JSON para máxima velocidad
        $stats = [
            'totalSales' => Voucher::where('created_at', '>=', $oneYearAgo)->count(),
            'revenue' => DB::table('vouchers')
                ->where('created_at', '>=', $oneYearAgo)
                ->whereRaw("json_valid(data)")
                ->sum(DB::raw("json_extract(data, '$.ImpTotal')")),
            'totalProducts' => Product::count(),
            'warehouses' => Warehouse::count(),
        ];

        return view('livewire.dashboard.summary-cards', compact('stats'));
    }
}
