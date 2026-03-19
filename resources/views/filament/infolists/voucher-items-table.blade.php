<div class="mt-4 w-full overflow-hidden border border-gray-300 shadow-md rounded-lg">
    <table class="w-full border-collapse bg-white text-left text-sm text-gray-700">
        <thead class="bg-gray-800 text-white font-bold uppercase tracking-wider text-xs">
            <tr>
                <th scope="col" class="px-6 py-4 border-b border-gray-300">Producto</th>
                <th scope="col" class="px-6 py-4 border-b border-gray-300 text-center">Cant.</th>
                <th scope="col" class="px-6 py-4 border-b border-gray-300 text-right">Precio Unit.</th>
                <th scope="col" class="px-6 py-4 border-b border-gray-300 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($getState() as $item)
                <tr class="odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition-colors duration-150">
                    <td class="px-6 py-4 font-medium text-gray-900">
                        {{ $item->product?->description ?? 'Sin descripción' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        {{ number_format($item->quantity, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        ${{ number_format($item->price, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-gray-900">
                        ${{ number_format($item->subtotal, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic bg-white">
                        No se han registrado artículos en este comprobante.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
