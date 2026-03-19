<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante {{ $voucher->id }}</title>
    <style>
        @page { 
            margin: 0.5cm; 
            size: A4; 
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 9pt; 
            margin: 0;
            padding: 0;
        }
        .header { 
            width: 100%;
            height: 2.5cm; 
            margin-bottom: 0.2cm;
        }
        .footer { 
            position: absolute; 
            bottom: 0cm; 
            left: 0cm; 
            right: 0cm; 
            height: 3cm; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5px; 
        }
        .border { border: 1px solid #000; }
        .p-1 { padding: 2px; }
        .p-2 { padding: 4px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .company-name { font-size: 11pt; font-weight: bold; }
        .company-data { font-size: 8pt; }
        .header-logo { height: 1.0cm; width: auto; margin-bottom: 2px; }
        
        .font-xxl { font-size: 24pt; }
        .bg-gray { background: #f2f2f2; }
        
        /* Tabla de productos a 7pt con formato Argentina */
        .items-table { font-size: 7pt !important; width: 100%; table-layout: fixed; }
        .items-table th { background-color: #eee; font-weight: bold; font-size: 7pt; }
        .items-table td { word-wrap: break-word; font-size: 7pt; }
        .items-table tfoot td { font-size: 8pt; }
    </style>
</head>
<body>
    @php 
        $data = $voucher->data; 
        $inv_letter = $voucher_type->letter ?? 'X';
    @endphp
    @foreach (['ORIGINAL', 'DUPLICADO'] as $copy)
        <div class="header">
            <table>
                <tr>
                    <td style="width:45%;" class="border p-2">
                        @if($logo_base64)
                            <img class="header-logo" src="data:image/jpeg;base64,{{ $logo_base64 }}">
                        @endif
                        <div class="company-name">{{ $config['business_name'] ?? 'Mi Empresa' }}</div>
                        <div class="company-data">{{ $config['address'] ?? '' }}</div>
                        <div class="company-data">Condición IVA: {{ $issuer_tax_cond ?? 'IVA Responsable Inscripto' }}</div>
                    </td>
                    <td style="width:10%;" class="border text-center">
                        <div class="font-xxl font-bold">{{ $inv_letter }}</div>
                        <div style="font-size: 8pt;">cod. {{ $data['CbteTipo'] }}</div>
                        <div style="font-size: 7pt; margin-top: 5px;">{{ $copy }}</div>
                    </td>
                    <td style="width:45%;" class="border p-2 text-left">
                        <div class="company-name" style="font-size: 13pt;">{{ $voucher_type->value }}</div>
                        <div class="company-data">Nº <strong style="font-size: 11pt;">{{ str_pad($data['PtoVta'], 4, '0', STR_PAD_LEFT) }}-{{ str_pad($data['CbteDesde'], 8, '0', STR_PAD_LEFT) }}</strong></div>
                        <div class="company-data">Fecha: {{ substr($data['CbteFch'], 6, 2) }}/{{ substr($data['CbteFch'], 4, 2) }}/{{ substr($data['CbteFch'], 0, 4) }}</div>
                        <div class="company-data">CUIT: {{ $config['cuit'] ?? '' }} - IIBB: {{ $config['iibb'] ?? '' }}</div>
                        <div class="company-data">Inicio de Actividades: {{ $config['start_date'] ?? '' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <!-- Datos del Cliente -->
            <table class="border">
                <tr>
                    <td class="p-2" style="width: 60%;">
                        <strong>Cliente:</strong> {{ $customer->name ?? 'Consumidor Final' }}<br>
                        <span style="font-size: 9pt;">
                            <strong>CUIT/DNI:</strong> {{ ($data['DocNro'] > 0) ? $data['DocNro'] : '---' }}
                        </span>
                    </td>
                    <td class="p-2 text-right" style="width: 40%;">
                        <strong>Cond. IVA:</strong> {{ $customer->responsibilityType->value ?? 'Consumidor Final' }}
                    </td>
                </tr>
            </table>

            <!-- Detalle de Items -->
            <table class="border items-table">
                <thead>
                    <tr>
                        @if($inv_letter == 'A')
                            <th class="border p-1" style="width: 6%;">Cód.</th>
                            <th class="border p-1" style="width: 12%;">Cant. u/m</th>
                            <th class="border p-1" style="width: 30%;">Descripción</th>
                            <th class="border p-1" style="width: 11%;">P. Unit</th>
                            <th class="border p-1" style="width: 7%;">% Desc</th>
                            <th class="border p-1" style="width: 11%;">Subt.</th>
                            <th class="border p-1" style="width: 11%;">IVA</th>
                            <th class="border p-1" style="width: 12%;">Precio</th>
                        @else
                            <th class="border p-1" style="width: 7%;">Cód.</th>
                            <th class="border p-1" style="width: 14%;">Cant. u/m</th>
                            <th class="border p-1" style="width: 46%;">Descripción</th>
                            <th class="border p-1" style="width: 12%;">P. Unit</th>
                            <th class="border p-1" style="width: 8%;">% Desc</th>
                            <th class="border p-1" style="width: 13%;">Precio</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['items'] as $item)
                        @php
                            $qty = (float)$item['qty'];
                            $priceWithTax = (float)$item['price'];
                            $taxRate = (float)($item['tax_rate'] ?? 21);
                            $discountAmount = (float)($item['discount'] ?? 0);
                            
                            // 1. Calcular el porcentaje de descuento para la visualización
                            $discountPerc = ($priceWithTax > 0) ? ($discountAmount / $priceWithTax) * 100 : 0;
                            
                            // 2. Precio Final por unidad (restando el importe de descuento)
                            $finalUnitPriceWithTax = $priceWithTax - $discountAmount;
                            
                            // 3. Totales de línea
                            $lineTotal = round($finalUnitPriceWithTax * $qty, 2);
                            $lineNet = round($lineTotal / (1 + ($taxRate / 100)), 2);
                            $lineTax = round($lineTotal - $lineNet, 2);
                            
                            // 4. Precio Unitario NETO base (sin IVA y sin descuento)
                            $unitNet = round($priceWithTax / (1 + ($taxRate / 100)), 2);
                        @endphp
                        <tr>
                            <td class="border p-1 text-center">{{ $item['id'] }}</td>
                            <td class="border p-1 text-center">{{ number_format($qty, 2, ',', '.') }} {{ $item['unit'] ?? 'un' }}</td>
                            <td class="border p-1 text-left">{{ $item['name'] }}</td>
                            <td class="border p-1 text-right">${{ number_format(($inv_letter == 'A' ? $unitNet : $priceWithTax), 2, ',', '.') }}</td>
                            <td class="border p-1 text-center">{{ number_format($discountPerc, 2, ',', '.') }}%</td>
                            @if($inv_letter == 'A')
                                <td class="border p-1 text-right">${{ number_format($lineNet, 2, ',', '.') }}</td>
                                <td class="border p-1 text-right">${{ number_format($lineTax, 2, ',', '.') }}</td>
                            @endif
                            <td class="border p-1 text-right">${{ number_format($lineTotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php 
                        $footerColspan = ($inv_letter == 'A') ? 7 : 5;
                    @endphp
                    @if($inv_letter == 'A')
                        <tr>
                            <td colspan="{{ $footerColspan }}" class="text-right p-1">Subtotal Neto</td>
                            <td class="border p-1 text-right">${{ number_format($data['ImpNeto'], 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="{{ $footerColspan }}" class="text-right p-1">IVA Total</td>
                            <td class="border p-1 text-right">${{ number_format($data['ImpIVA'], 2, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="{{ $footerColspan }}" class="text-right p-1 font-bold" style="font-size: 9pt;">Total</td>
                        <td class="border p-1 text-right font-bold" style="font-size: 9pt;">${{ number_format($data['ImpTotal'], 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="footer">
            <table style="border-top: 1px solid #000; padding-top: 5px;">
                <tr>
                    <td style="width: 3cm;">
                        @if(isset($data['res']['CAE']))
                            <img src="data:image/svg+xml;base64,{{ (new \App\Services\AfipService())->getQrBase64($voucher) }}" width="85">
                        @endif
                    </td>
                    <td style="vertical-align: top;">
                        @if(isset($data['res']['CAE']))
                            <div class="font-bold" style="font-size: 9pt;">CAE Nº: {{ $data['res']['CAE'] }}</div>
                            <div style="font-size: 9pt;">Vencimiento CAE: {{ $data['res']['CAEFchVto'] }}</div>
                            @if($logo_fiscal_base64)
                                <img style="height:0.7cm; width:auto; margin-top: 3px;" src="data:image/png;base64,{{ $logo_fiscal_base64 }}">
                            @endif
                            <div style="font-size: 7pt; margin-top: 2px;">Comprobante Autorizado</div>
                            <div style="font-size: 6pt; color: #333;">Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @if($loop->first) <div style="page-break-after: always;"></div> @endif
    @endforeach
</body>
</html>
