<flux:card class="space-y-4">
    <flux:heading>Top 5 Productos más Vendidos (Año)</flux:heading>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Producto</flux:table.column>
            <flux:table.column align="end">Cant. Vendida</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($topProducts as $item)
                <flux:table.row>
                    <flux:table.cell class="font-medium">{{ $item->product?->description ?? 'Producto Eliminado' }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:badge color="zinc" size="sm">{{ number_format($item->total_qty, 0) }}</flux:badge>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</flux:card>
