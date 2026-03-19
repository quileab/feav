<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Config;
use App\Models\Customer;
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
                $newNum = (int) (Config::where('id', "last_{$voucherTypeId}_{$pointOfSale}")->value('value') ?? 1000) + 1;
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

                Config::updateOrCreate(['id' => "last_{$voucherTypeId}_{$pointOfSale}"], ['value' => $newNum]);
            }

            // Crear registro local
            $voucher = Voucher::create([
                'id' => "{$voucherTypeId}-{$pointOfSale}-{$newNum}",
                'data' => array_merge($voucherData, [
                    'res' => ['CAE' => $cae, 'CAEFchVto' => $caeFchVto, 'CUIT' => $config['cuit'] ?? ''],
                    'items' => $cart,
                    'customerId' => $customer->id
                ])
            ]);

            // Crear detalles y descontar stock
            foreach ($cart as $item) {
                VoucherDetail::create([
                    'voucher_id' => $voucher->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'tax' => ($item['price'] * $item['qty']) - (($item['price'] * $item['qty']) / (1 + ($item['tax_rate'] / 100))),
                    'subtotal' => $item['price'] * $item['qty']
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
            if (!isset($taxes[$taxId])) {
                $taxes[$taxId] = ['Id' => $taxId, 'BaseImp' => 0, 'Importe' => 0];
            }
            $lineTotal = ($item['price'] - ($item['discount'] ?? 0)) * $item['qty'];
            $lineNet = round($lineTotal / (1 + ($item['tax_rate'] / 100)), 2);
            
            $taxes[$taxId]['BaseImp'] += $lineNet;
            $taxes[$taxId]['Importe'] += round($lineTotal - $lineNet, 2);
        }

        $lastVoucher = $this->afipService->getLastVoucher($pointOfSale, $voucherTypeId);
        $newNum = $lastVoucher + 1;

        $data = [
            'CantReg'   => 1,
            'PtoVta'    => $pointOfSale,
            'CbteTipo'  => $voucherTypeId,
            'Concepto'  => 1,
            'DocTipo'   => $customer->customer_id_type_id ?? 99,
            'DocNro'    => (float) preg_replace('/[^0-9]/', '', $customer->CUIT ?? '0'),
            'CondicionIVAReceptorId' => $customer->responsibility_type_id ?? 5,
            'CbteDesde' => $newNum,
            'CbteHasta' => $newNum,
            'CbteFch'   => (int) date('Ymd'),
            'ImpTotal'  => round($totals['total'], 2),
            'ImpTotConc' => 0,
            'ImpNeto'   => round($totals['net'], 2),
            'ImpOpEx'   => 0,
            'ImpIVA'    => round($totals['tax'], 2),
            'ImpTrib'   => 0,
            'MonId'     => 'PES',
            'MonCotiz'  => 1,
            'Iva'       => array_values($taxes),
        ];

        // Asociados para NC/ND
        if (in_array($voucherTypeId, [2, 3, 7, 8, 12, 13])) {
            $asocType = match ($voucherTypeId) { 2, 3 => 1, 7, 8 => 6, 12, 13 => 11, default => 1 };
            $data['CbtesAsoc'] = [[
                'Tipo' => (int)$asocType, 
                'PtoVta' => (int)$input['originalPtoVta'], 
                'Nro' => (int)$input['originalCbteNro']
            ]];
        }

        return $data;
    }

    protected function getAfipTaxId($rate): int
    {
        return match ((float)$rate) {
            0.0 => 3,
            10.5 => 4,
            21.0 => 5,
            27.0 => 6,
            default => 5,
        };
    }
}