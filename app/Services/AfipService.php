<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\Config;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

class AfipService
{
    /**
     * Genera la URL para el código QR de AFIP según RG 4892/2020.
     */
    public function getQrUrl(Voucher $voucher): string
    {
        $data = $voucher->data;
        
        // El CUIT del emisor debe ser puramente numérico
        $cuitRaw = $data['res']['CUIT'] ?? Config::where('id', 'cuit')->value('value') ?? '0';
        $cuit = (float) preg_replace('/[^0-9]/', '', $cuitRaw);

        // Extraer tipo y número si no están en el array principal (comprobantes internos)
        $tipoCmp = (int) ($data['CbteTipo'] ?? explode('-', $voucher->id)[0] ?? 0);
        $nroCmp = (int) ($data['CbteDesde'] ?? explode('-', $voucher->id)[2] ?? 0);
        $ptoVta = (int) ($data['PtoVta'] ?? explode('-', $voucher->id)[1] ?? 0);

        $payload = [
            "ver" => 1,
            "fecha" => $this->formatDate($data['CbteFch'] ?? null),
            "cuit" => $cuit,
            "ptoVta" => $ptoVta,
            "tipoCmp" => $tipoCmp,
            "nroCmp" => $nroCmp,
            "importe" => (float) ($data['ImpTotal'] ?? 0),
            "moneda" => $data['MonId'] ?? 'PES',
            "ctz" => (float) ($data['MonCotiz'] ?? 1),
            "tipoDocRec" => (int) ($data['DocTipo'] ?? 99),
            "nroDocRec" => (float) ($data['DocNro'] ?? 0),
            "tipoCodAut" => "E",
            "codAut" => (float) ($data['res']['CAE'] ?? 0)
        ];

        return "https://www.afip.gob.ar/fe/qr/?p=" . base64_encode(json_encode($payload));
    }

    /**
     * Genera el QR en formato Base64 (SVG) para embeber en el PDF.
     */
    public function getQrBase64(Voucher $voucher): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(100),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        
        return base64_encode($writer->writeString($this->getQrUrl($voucher)));
    }

    protected function formatDate(?string $date): string
    {
        if (!$date) return date('Y-m-d');
        if (strlen($date) === 8) {
            return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        }
        return date('Y-m-d', strtotime($date));
    }
}
