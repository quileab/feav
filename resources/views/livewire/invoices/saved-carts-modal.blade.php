<div class="space-y-4">
    @if(empty($savedCarts))
        <div class="text-center py-10 text-zinc-400 italic">
            No hay carritos guardados para este cliente.
        </div>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Fecha</flux:table.column>
                <flux:table.column align="end">Acciones</flux:table.column>
            </flux:table.columns>
            
            <flux:table.rows>
                @foreach($savedCarts as $c)
                    <flux:table.row :key="$c['path']">
                        <flux:table.cell class="text-sm">{{ $c['date'] }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex gap-2 justify-end items-center" x-data="{ confirming: false }">
                                <flux:button wire:click="selectSavedCart('{{ addslashes($c['path']) }}')" variant="primary" size="xs" x-show="!confirming">Cargar</flux:button>
                                
                                <!-- Botón Eliminar con confirmación integrada (sin dropdown) -->
                                <template x-if="!confirming">
                                    <flux:button @click="confirming = true" variant="ghost" icon="trash" size="xs" color="danger"></flux:button>
                                </template>

                                <template x-if="confirming">
                                    <div class="flex gap-1 items-center animate-in fade-in zoom-in duration-200">
                                        <flux:button wire:click="deleteSavedCart('{{ addslashes($c['path']) }}')" variant="filled" size="xs" color="danger">Borrar</flux:button>
                                        <flux:button @click="confirming = false" variant="ghost" size="xs">X</flux:button>
                                    </div>
                                </template>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>