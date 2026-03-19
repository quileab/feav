<div class="space-y-4 max-w-5xl mx-auto">
    <!-- Selección de Datos de Cabecera (Compacto) -->
    <flux:card class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <flux:field>
                <flux:label class="text-xs">Comprobante</flux:label>
                <select wire:model="voucherTypeId" {{ $isAssociated ? 'disabled' : '' }} class="w-full h-9 px-2 py-1 text-sm border border-zinc-200 rounded-md bg-white dark:bg-zinc-800 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-500 outline-none disabled:bg-zinc-100">
                    @foreach($voucherTypes as $type)
                        <option value="{{ $type['id'] }}">{{ $type['value'] }}</option>
                    @endforeach
                </select>
            </flux:field>

            <flux:field>
                <flux:label class="text-xs">Cliente</flux:label>
                <select wire:model.live="customerId" {{ $isAssociated ? 'disabled' : '' }}
                    class="w-full h-9 px-2 py-1 text-sm border border-zinc-200 rounded-md bg-white dark:bg-zinc-800 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-500 outline-none disabled:bg-zinc-100">
                    <option value="">Seleccione...</option>
                    @foreach(App\Models\Customer::all() as $c)
                        <option value="{{ (string)$c->id }}">{{ $c->name }} ({{ $c->CUIT ?: 'S/C' }})</option>
                    @endforeach
                </select>
            </flux:field>

            <flux:field>
                <flux:label class="text-xs">Depósito</flux:label>
                <select wire:model="warehouseId" class="w-full h-9 px-2 py-1 text-sm border border-zinc-200 rounded-md bg-white dark:bg-zinc-800 dark:border-zinc-700 text-zinc-900 dark:text-white focus:ring-2 focus:ring-zinc-500 outline-none">
                    @foreach(App\Models\Warehouse::all() as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </flux:field>

            <flux:field>
                <flux:label class="text-xs">Punto Vta</flux:label>
                <flux:input wire:model="pointOfSale" type="number" readonly size="sm" />
            </flux:field>

            @if(in_array((int)$voucherTypeId, [2, 3, 7, 8, 12, 13]))
                <div class="col-span-full grid grid-cols-2 gap-3 mt-1 p-2 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded">
                    <flux:field>
                        <flux:label class="text-xs">Pto Vta Original</flux:label>
                        <flux:input wire:model="originalPtoVta" type="number" size="sm" :disabled="$isAssociated" />
                    </flux:field>
                    <flux:field>
                        <flux:label class="text-xs">Nro Original</flux:label>
                        <flux:input wire:model="originalCbteNro" type="number" size="sm" :disabled="$isAssociated" />
                    </flux:field>
                </div>
            @endif
        </div>
    </flux:card>

    <!-- Búsqueda y Carrito -->
    <flux:card class="p-4 space-y-4">
        <div class="relative">
            <flux:input wire:model.live="searchProduct" wire:keydown.arrow-up.prevent="decrementHighlight"
                wire:keydown.arrow-down.prevent="incrementHighlight"
                wire:keydown.enter.prevent="selectHighlightedProduct"
                placeholder="Buscar por descripción o código..." icon="magnifying-glass" size="sm" />

                    @if(!empty($searchResults))
                        <div class="absolute z-10 w-full mt-1 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl divide-y bg-white dark:bg-zinc-800 divide-zinc-100 dark:divide-zinc-700 overflow-hidden">
                            @foreach($searchResults as $index => $p)
                                <button wire:click="addProduct({{ $p['id'] }})"
                                    class="w-full text-left px-4 py-2 flex justify-between items-center {{ $highlightIndex === $index ? 'bg-zinc-100 dark:bg-zinc-700' : 'hover:bg-zinc-50 dark:hover:bg-zinc-700/50' }}">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $p['description'] }}</span>
                                        <span class="text-xs text-zinc-500">Stock: {{ number_format($p['stock'], 2) }}</span>
                                    </div>
                                    <span class="font-bold text-sm text-zinc-900 dark:text-white">${{ number_format($p['sale_price1'], 2) }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Producto</flux:table.column>
                <flux:table.column align="center" class="w-20">Cant.</flux:table.column>
                <flux:table.column align="center" class="w-20">Precio</flux:table.column>
                <flux:table.column align="end" class="w-32">P. Unit</flux:table.column>
                <flux:table.column align="center" class="w-24">Desc.</flux:table.column>
                <flux:table.column align="end" class="w-32">Total</flux:table.column>
                <flux:table.column class="w-10"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($cart as $id => $item)
                    <flux:table.row :key="$id">
                        <flux:table.cell class="text-sm">{{ $item['name'] }}</flux:table.cell>
                        <flux:table.cell>
                            <input type="number" wire:model.live="cart.{{ $id }}.qty"
                                class="w-14 h-8 text-center border rounded text-sm dark:bg-zinc-800 dark:border-zinc-700" />
                        </flux:table.cell>
                        <flux:table.cell align="center">
                            <flux:button wire:click="togglePriceType({{ $id }})" variant="ghost" size="xs" class="{{ $item['price_type'] == 1 ? 'text-blue-600 font-bold' : 'text-purple-600 font-bold' }}">
                                P{{ $item['price_type'] }}
                            </flux:button>
                        </flux:table.cell>
                        <flux:table.cell align="end" class="text-sm">${{ number_format($item['price'], 2) }}</flux:table.cell>
                        <flux:table.cell>
                            <input type="number" step="0.01" wire:model.live="cart.{{ $id }}.discount"
                                class="w-20 h-8 text-center border rounded text-sm dark:bg-zinc-800 dark:border-zinc-700" />
                        </flux:table.cell>
                        <flux:table.cell align="end" class="font-bold text-sm">
                            ${{ number_format(((float)$item['price'] - (float)$item['discount']) * (float)$item['qty'], 2) }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button wire:click="removeProduct({{ $id }})" variant="ghost" icon="trash" size="sm" />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-6 text-zinc-400 italic text-sm">Carrito vacío</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <!-- Resumen de Totales Integrado (Horizontal) -->
        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-700 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="w-full md:w-auto order-last md:order-first flex flex-wrap gap-2">
                <flux:button wire:click="save" variant="primary" size="base" class="px-8" :disabled="empty($cart) || is_null($customerId)">
                    Emitir Comprobante
                </flux:button>
                
                <div class="flex gap-2">
                    <flux:button wire:click="saveCart" variant="ghost" icon="document-arrow-down" size="base" :disabled="empty($cart) || is_null($customerId)">
                        Guardar
                    </flux:button>
                    
                    {{ $this->openSavedCartsAction }}
                </div>
            </div>

            <div class="flex gap-6 text-sm order-first md:order-last">
                <div class="flex gap-2">
                    <span class="text-zinc-500">Neto:</span>
                    <span class="font-bold">${{ number_format($this->totals['net'], 2) }}</span>
                </div>
                <div class="flex gap-2">
                    <span class="text-zinc-500">IVA:</span>
                    <span class="font-bold">${{ number_format($this->totals['tax'], 2) }}</span>
                </div>
                <div class="flex gap-2 text-lg">
                    <span class="font-bold">TOTAL:</span>
                    <span class="font-bold text-zinc-900 dark:text-white">${{ number_format($this->totals['total'], 2) }}</span>
                </div>
            </div>
        </div>

        @if($errorMessage)
            <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <flux:text size="sm" class="text-red-700 dark:text-red-400">
                    <strong>Error AFIP:</strong> {{ $errorMessage }}
                </flux:text>
            </div>
        @endif
    </flux:card>

    <x-filament-actions::modals />
</div>