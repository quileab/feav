<?php

namespace App\Services;

use App\Models\Config;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        protected AfipService $afipService,
        protected StockService $stockService
    ) {}

    /**
     * Create and process a complete voucher (Local + AFIP).
     */
    public function processVoucher(array $input): Voucher
    {
        return DB::transaction(function () use ($input) {
            $customer = $input['customer'];
            $cart = $input['cart'];
            $totals = $input['totals'];
            $voucherTypeId = $input['voucherTypeId'];
            $pointOfSale = $input['pointOfSale'];
            $warehouseId = $input['warehouseId'];
            $isFiscal = $input['isFiscal'] ?? false;

            $config = DB::table('configs')->pluck('value', 'id');

            if ($isFiscal) {
                $voucherData = $this->prepareAfipData($customer, $cart, $totals, $voucherTypeId, $pointOfSale, $input);
                $res = $this->afipService->createVoucher($voucherData);

                $cae = $res['CAE'];
                $caeFchVto = $res['CAEFchVto'];
                $newNum = $voucherData['CbteDesde'];
            } else {
                $configKey = "{$voucherTypeId}-{$pointOfSale}";
                $configValue = Config::where('id', $configKey)->value('value');

                if ($configValue === null) {
                    // Fallback: check the vouchers table for existing IDs with this prefix
                    $lastVoucherId = Voucher::where('id', 'like', "{$configKey}-%")
                        ->orderByRaw('LENGTH(id) DESC, id DESC')
                        ->value('id');

                    if ($lastVoucherId) {
                        $parts = explode('-', $lastVoucherId);
                        $newNum = (int) end($parts) + 1;
                    } else {
                        $newNum = 1;
                    }
                } else {
                    $newNum = (int) $configValue + 1;
                }

                $cae = 'INTERNAL-' . now()->timestamp;
                $caeFchVto = now()->format('Y-m-d');

                $voucherData = [
                    'CbteDesde' => $newNum,
                    'PtoVta' => $pointOfSale,
                    'CbteTipo' => $voucherTypeId,
                    'DocTipo' => $customer->customer_id_type_id ?? 99,
                    'DocNro' => (float) preg_replace('/[^0-9]/', '', $customer->CUIT ?? '0'),
                    'ImpTotal' => $totals['total'],
                    'ImpNeto' => $totals['net'],
                    'ImpIVA' => $totals['tax'],
                ];

                Config::updateOrCreate(
                    ['id' => $configKey],
                    [
                        'value' => (string) $newNum,
                        'type' => null,
                        'description' => 'z9) Non Fiscal',
                    ]
                );
            }

            // Crear registro local
            $voucher = Voucher::create([
                'id' => "{$voucherTypeId}-{$pointOfSale}-{$newNum}",
                'data' => array_merge($voucherData, [
                    'res' => ['CAE' => $cae, 'CAEFchVto' => $caeFchVto, 'CUIT' => $config['cuit'] ?? ''],
                    'items' => $cart,
                    'customerId' => $customer->id,
                ]),
            ]);

            // Crear detalles y descontar stock
            foreach ($cart as $item) {
                $effectivePrice = round((float) $item['price'] - (float) ($item['discount'] ?? 0), 2);
                $lineTotal = round($effectivePrice * (float) $item['qty'], 2);
                $lineNet = round($lineTotal / (1 + ($item['tax_rate'] / 100)), 2);
                $lineTax = round($lineTotal - $lineNet, 2);

                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $effectivePrice,
                    'tax' => $lineTax,
                    'subtotal' => $lineTotal,
                ]);

                $this->stockService->decrementStock($item['id'], $warehouseId, $item['qty']);
            }

            return $voucher;
        });
    }

    protected function prepareAfipData($customer, $cart, $totals, $voucherTypeId, $pointOfSale, $input): array
    {
        $taxes = [];
        foreach ($cart as $item) {
            $taxId = $this->getAfipTaxId($item['tax_rate']);
            if (! isset($taxes[$taxId])) {
                $taxes[$taxId] = ['Id' => $taxId, 'BaseImp' => 0, 'Importe' => 0];
            }

            $effectivePrice = round((float) $item['price'] - (float) ($item['discount'] ?? 0), 2);
            $lineTotal = round($effectivePrice * (float) $item['qty'], 2);
            $lineNet = round($lineTotal / (1 + ($item['tax_rate'] / 100)), 2);
            $lineTax = round($lineTotal - $lineNet, 2);

            $taxes[$taxId]['BaseImp'] = round($taxes[$taxId]['BaseImp'] + $lineNet, 2);
            $taxes[$taxId]['Importe'] = round($taxes[$taxId]['Importe'] + $lineTax, 2);
        }

        $lastVoucher = $this->afipService->getLastVoucher($pointOfSale, $voucherTypeId);
        $newNum = $lastVoucher + 1;

        $data = [
            'CantReg' => 1,
            'PtoVta' => $pointOfSale,
            'CbteTipo' => $voucherTypeId,
            'Concepto' => 1,
            'DocTipo' => $customer->customer_id_type_id ?? 99,
            'DocNro' => (float) preg_replace('/[^0-9]/', '', $customer->CUIT ?? '0'),
            'CondicionIVAReceptorId' => $customer->responsibility_type_id ?? 5,
            'CbteDesde' => $newNum,
            'CbteHasta' => $newNum,
            'CbteFch' => (int) date('Ymd'),
            'ImpTotal' => round($totals['total'], 2),
            'ImpTotConc' => 0,
            'ImpNeto' => round($totals['net'], 2),
            'ImpOpEx' => 0,
            'ImpIVA' => round($totals['tax'], 2),
            'ImpTrib' => 0,
            'MonId' => 'PES',
            'MonCotiz' => 1,
            'Iva' => array_values($taxes),
        ];

        // Asociados para NC/ND
        if (in_array($voucherTypeId, [2, 3, 7, 8, 12, 13])) {
            $asocType = match ($voucherTypeId) {
                2, 3 => 1, 7, 8 => 6, 12, 13 => 11, default => 1
            };
            $data['CbtesAsoc'] = [[
                'Tipo' => (int) $asocType,
                'PtoVta' => (int) $input['originalPtoVta'],
                'Nro' => (int) $input['originalCbteNro'],
            ]];
        }

        return $data;
    }

    protected function getAfipTaxId($rate): int
    {
        return match ((float) $rate) {
            0.0 => 3,
            10.5 => 4,
            21.0 => 5,
            27.0 => 6,
            default => 5,
        };
    }
}
