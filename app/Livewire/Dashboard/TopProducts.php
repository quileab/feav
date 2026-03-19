<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\VoucherDetail;
use Illuminate\Support\Facades\DB;

#[Lazy]
class TopProducts extends Component
{
    public function placeholder()
    {
        return <<<'HTML'
        <flux:card class="h-80 animate-pulse bg-zinc-50 dark:bg-zinc-900/50 border-zinc-200 dark:border-zinc-800 flex items-center justify-center">
            <flux:text class="text-zinc-400">Analizando productos más vendidos...</flux:text>
        </flux:card>
        HTML;
    }

    public function render()
    {
        $oneYearAgo = \Carbon\Carbon::now()->subYear()->toDateTimeString();
        
        $topProducts = VoucherDetail::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->where('created_at', '>=', $oneYearAgo)
            ->with('product:id,description')
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.dashboard.top-products', compact('topProducts'));
    }
}
