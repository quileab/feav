<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Config;
use App\Models\Customer;
use App\Models\VoucherType;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Genera y descarga el PDF de la factura.
     */
    public function download(Voucher $voucher)
    {
        $config = Config::all()->pluck('value', 'id')->toArray();
        $data = $voucher->data;

        $customerId = $data['customerId'] ?? null;
        $docNro = $data['DocNro'] ?? null;

        $customer = null;
        if ($customerId !== null && $customerId != 0) {
            $customer = Customer::find($customerId);
        }
        if (!$customer && $docNro && $docNro != 0) {
            $customer = Customer::where('CUIT', (string)$docNro)->first();
        }
        if (!$customer) {
            $customer = Customer::find(0);
        }

        $voucherType = VoucherType::find($data['CbteTipo'] ?? 0);

        $issuerTaxCond = isset($config['tax_cond']) 
            ? \App\Models\ResponsibilityType::find($config['tax_cond'])?->value 
            : null;

        // Base64 logos
        $logoPath = public_path('img/logo.jpg');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $logoFiscalPath = public_path('img/logo_fiscal.png');
        $logoFiscalBase64 = file_exists($logoFiscalPath) ? base64_encode(file_get_contents($logoFiscalPath)) : null;

        $pdf = Pdf::loadView('pdf.invoice', [
            'voucher' => $voucher,
            'config' => $config,
            'customer' => $customer,
            'voucher_type' => $voucherType,
            'issuer_tax_cond' => $issuerTaxCond,
            'logo_base64' => $logoBase64,
            'logo_fiscal_base64' => $logoFiscalBase64
        ]);

        $filename = str_replace(' ', '-', strtolower($voucherType->value ?? 'comprobante')) . "-{$voucher->id}.pdf";

        return $pdf->download($filename);
    }
}
