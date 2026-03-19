<flux:card class="bg-zinc-50 dark:bg-zinc-900/50 border-zinc-200 dark:border-zinc-800">
    <div class="flex flex-col space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <flux:icon icon="cloud" class="text-zinc-400" />
                    <flux:heading size="sm">Estado Servicios ARCA/AFIP</flux:heading>
                </div>

                @if(isset($afipStatus['cuit']))
                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                        <flux:text size="xs" class="font-medium {{ isset($afipStatus['error']) ? 'text-red-500' : 'text-green-600 dark:text-green-400' }}">
                            {{ $afipStatus['cuit'] }}
                        </flux:text>
                        <flux:icon :icon="isset($afipStatus['error']) ? 'x-mark' : 'check'" size="xs" variant="micro" class="{{ isset($afipStatus['error']) ? 'text-red-500' : 'text-green-600 dark:text-green-400' }}" />
                    </div>
                @endif
            </div>
            
            <div class="flex flex-wrap gap-3">
                @if(isset($afipStatus['error']))
                    <div class="flex flex-col items-end">
                        <flux:badge color="danger" variant="outline">{{ $afipStatus['error'] }}</flux:badge>
                        @if(isset($afipStatus['message']))
                            <flux:text size="xs" class="text-red-500 mt-1 text-right" title="{{ $afipStatus['message'] }}">
                                Detalle: {{ $afipStatus['message'] }}
                            </flux:text>
                        @endif
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <flux:text size="xs" class="text-zinc-500">App:</flux:text>
                        <flux:badge :color="$afipStatus['AppServer'] === 'OK' ? 'green' : 'red'" size="sm">{{ $afipStatus['AppServer'] }}</flux:badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:text size="xs" class="text-zinc-500">DB:</flux:text>
                        <flux:badge :color="$afipStatus['DbServer'] === 'OK' ? 'green' : 'red'" size="sm">{{ $afipStatus['DbServer'] }}</flux:badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:text size="xs" class="text-zinc-500">Auth:</flux:text>
                        <flux:badge :color="$afipStatus['AuthServer'] === 'OK' ? 'green' : 'red'" size="sm">{{ $afipStatus['AuthServer'] }}</flux:badge>
                    </div>

                    @if(isset($afipStatus['lastA']))
                        <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-800 mx-1 hidden md:block"></div>
                        <div class="flex items-center gap-2">
                            <flux:text size="xs" class="text-zinc-500">PV{{ $afipStatus['pos'] }} Fact.A:</flux:text>
                            <flux:badge color="zinc" size="sm" variant="outline">{{ $afipStatus['lastA'] }}</flux:badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:text size="xs" class="text-zinc-500">Fact.B:</flux:text>
                            <flux:badge color="zinc" size="sm" variant="outline">{{ $afipStatus['lastB'] }}</flux:badge>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
                <flux:icon icon="document" size="sm" class="{{ $diagnostics['cert_file'] && !$diagnostics['is_expired'] ? 'text-green-500' : 'text-red-500' }}" />
                <flux:text size="xs">
                    Certificado (.crt): 
                    <span class="{{ $diagnostics['is_expired'] ? 'font-bold underline' : '' }}">
                        {{ $diagnostics['cert_info'] }}
                    </span>
                </flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:icon icon="lock-closed" size="sm" class="{{ $diagnostics['key_file'] ? 'text-green-500' : 'text-red-500' }}" />
                <flux:text size="xs">Llave Privada (.key): {{ $diagnostics['key_file'] ? 'Encontrada' : 'No encontrada' }}</flux:text>
            </div>
        </div>
    </div>
</flux:card>
