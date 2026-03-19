<div class="space-y-6">
    <!-- Isla 1: Tarjetas de Resumen (Carga Rápida) -->
    @livewire(\App\Livewire\Dashboard\SummaryCards::class)

    <!-- Isla 2: Estado de AFIP/ARCA (Carga Diferida/Lazy) -->
    @livewire(\App\Livewire\Dashboard\AfipStatus::class)

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Isla 3: Gráfico de Facturación -->
        @livewire(\App\Livewire\Dashboard\RevenueChart::class)

        <!-- Isla 4: Top Productos -->
        @livewire(\App\Livewire\Dashboard\TopProducts::class)
    </div>
</div>
