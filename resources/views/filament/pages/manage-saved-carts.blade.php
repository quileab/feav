<x-filament-panels::page>
    <div class="fi-ta-ctn border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="fi-ta-header-cell px-4 py-3 sm:px-6">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Fecha</span>
                    </th>
                    <th class="fi-ta-header-cell px-4 py-3 sm:px-6">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Cliente</span>
                    </th>
                    <th class="fi-ta-header-cell px-4 py-3 sm:px-6 text-center">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Items</span>
                    </th>
                    <th class="fi-ta-header-cell px-4 py-3 sm:px-6 text-right">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Total</span>
                    </th>
                    <th class="px-4 py-3 sm:px-6"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                @forelse($carts as $cart)
                    <tr class="fi-ta-row hover:bg-gray-50 dark:hover:bg-white/5 transition duration-75">
                        <td class="fi-ta-cell px-4 py-4 sm:px-6 text-sm text-gray-500 dark:text-gray-400">
                            {{ $cart['date'] }}
                        </td>
                        <td class="fi-ta-cell px-4 py-4 sm:px-6 text-sm font-medium text-gray-950 dark:text-white">
                            {{ $cart['customer'] }}
                        </td>
                        <td class="fi-ta-cell px-4 py-4 sm:px-6 text-sm text-center text-gray-500 dark:text-gray-400">
                            {{ $cart['items'] }}
                        </td>
                        <td class="fi-ta-cell px-4 py-4 sm:px-6 text-sm text-right font-bold text-gray-950 dark:text-white">
                            ${{ number_format($cart['total'], 2, ',', '.') }}
                        </td>
                        <td class="fi-ta-cell px-4 py-4 sm:px-6 text-right">
                            <div class="flex gap-4 justify-end items-center" x-data="{ confirming: false }">
                                <a href="{{ route('filament.admin.resources.vouchers.create', ['asocCustomer' => $cart['customer_id'], 'asocCart' => $cart['name']]) }}" 
                                   class="fi-link fi-link-size-sm fi-color-primary font-semibold text-sm text-primary-600 dark:text-primary-400 hover:underline"
                                   x-show="!confirming">
                                    Facturar
                                </a>

                                <template x-if="!confirming">
                                    <button @click="confirming = true" class="text-gray-400 hover:text-danger-600 dark:hover:text-danger-400 transition-colors">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </template>

                                <template x-if="confirming">
                                    <div class="flex gap-2 items-center animate-in fade-in zoom-in duration-200">
                                        <button wire:click="deleteCart('{{ addslashes($cart['path']) }}')" 
                                                class="bg-danger-600 hover:bg-danger-500 text-white px-3 py-1 rounded-lg text-xs font-bold shadow-sm">
                                            Confirmar
                                        </button>
                                        <button @click="confirming = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-xs font-bold">
                                            Cancelar
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <x-heroicon-o-shopping-cart class="w-10 h-10 text-gray-300 dark:text-gray-600" />
                                <span class="text-gray-400 dark:text-gray-500 italic">No hay carritos aparcados en este momento.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>