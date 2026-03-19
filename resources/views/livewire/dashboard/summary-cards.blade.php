<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <flux:card class="flex items-center gap-4">
        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
            <flux:icon icon="document-text" class="text-blue-600 dark:text-blue-400" />
        </div>
        <div>
            <flux:text size="sm" class="text-zinc-500">Ventas (Año)</flux:text>
            <flux:heading size="lg">{{ number_format($stats['totalSales']) }}</flux:heading>
        </div>
    </flux:card>

    <flux:card class="flex items-center gap-4">
        <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
            <flux:icon icon="banknotes" class="text-green-600 dark:text-green-400" />
        </div>
        <div>
            <flux:text size="sm" class="text-zinc-500">Recaudación (Año)</flux:text>
            <flux:heading size="lg">${{ number_format($stats['revenue'], 0, ',', '.') }}</flux:heading>
        </div>
    </flux:card>

    <flux:card class="flex items-center gap-4">
        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
            <flux:icon icon="cube" class="text-purple-600 dark:text-purple-400" />
        </div>
        <div>
            <flux:text size="sm" class="text-zinc-500">Catálogo</flux:text>
            <flux:heading size="lg">{{ number_format($stats['totalProducts']) }} Prod.</flux:heading>
        </div>
    </flux:card>

    <flux:card class="flex items-center gap-4">
        <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
            <flux:icon icon="users" class="text-orange-600 dark:text-orange-400" />
        </div>
        <div>
            <flux:text size="sm" class="text-zinc-500">Depósitos</flux:text>
            <flux:heading size="lg">{{ $stats['warehouses'] }} activos</flux:heading>
        </div>
    </flux:card>
</div>
