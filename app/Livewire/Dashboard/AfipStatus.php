<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Facades\DB;

#[Lazy]
class AfipStatus extends Component
{
    public function placeholder()
    {
        return <<<'HTML'
        <flux:card class="animate-pulse bg-zinc-50 dark:bg-zinc-900/50 border-zinc-200 dark:border-zinc-800 h-24 flex items-center justify-center">
            <flux:text class="text-zinc-400">Consultando estado de ARCA/AFIP...</flux:text>
        </flux:card>
        HTML;
    }

    public function getStatus()
    {
        $config = DB::table('configs')->pluck('value', 'id');
        if (!isset($config['afip_cert']) || !isset($config['afip_key'])) {
            return ['error' => 'Configuración incompleta'];
        }

        $taFolder = storage_path('app/afip/');
        if (!file_exists($taFolder)) { mkdir($taFolder, 0777, true); }

        $afipParams = [
            'CUIT' => (int) preg_replace('/[^0-9]/', '', $config['cuit'] ?? '0'),
            'production' => ($config['production'] ?? '0') == '1',
            'cert' => $config['afip_cert'],
            'key' => $config['afip_key'],
            'res_folder' => storage_path('app/'),
            'ta_folder' => $taFolder,
            'environment' => $config['environment'] ?? 'dev',
            'exceptions' => true,
            'soap_options' => ['cache_wsdl' => WSDL_CACHE_NONE, 'connection_timeout' => 15]
        ];

        try {
            $afip = new \Afip($afipParams);
            $status = (array) $afip->ElectronicBilling->GetServerStatus();
            $pos = (int) ($config['point_of_sale'] ?? 2);
            $lastA = $afip->ElectronicBilling->GetLastVoucher($pos, 1);
            $lastB = $afip->ElectronicBilling->GetLastVoucher($pos, 6);

            return array_merge($status, [
                'lastA' => $lastA,
                'lastB' => $lastB,
                'pos' => $pos,
                'cuit' => $config['cuit'] ?? 'N/A'
            ]);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'cms') || str_contains($e->getMessage(), 'TA')) {
                foreach (glob($taFolder . '*') as $file) { @unlink($file); }
            }
            return [
                'error' => 'Error de conexión',
                'message' => $e->getMessage(),
                'cuit' => $config['cuit'] ?? 'N/A'
            ];
        }
    }

    public function getDiagnostics()
    {
        $config = DB::table('configs')->pluck('value', 'id');
        $basePath = storage_path('app/');
        
        $certPath = isset($config['afip_cert']) ? $basePath . $config['afip_cert'] : null;
        $keyPath = isset($config['afip_key']) ? $basePath . $config['afip_key'] : null;
        
        $certInfo = 'No configurado';
        $isExpired = false;
        
        if ($certPath && file_exists($certPath)) {
            $certData = openssl_x509_parse(file_get_contents($certPath));
            if ($certData) {
                $validTo = \Carbon\Carbon::createFromTimestamp($certData['validTo_time_t']);
                $certInfo = 'Vence el: ' . $validTo->format('d/m/Y');
                $isExpired = $validTo->isPast();
            } else {
                $certInfo = 'Certificado inválido';
            }
        } elseif ($certPath) {
            $certInfo = 'Archivo no encontrado';
        }

        return [
            'env_cuit' => isset($config['cuit']),
            'cert_file' => file_exists($certPath),
            'key_file' => file_exists($keyPath),
            'cert_info' => $certInfo,
            'is_expired' => $isExpired
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.afip-status', [
            'afipStatus' => $this->getStatus(),
            'diagnostics' => $this->getDiagnostics()
        ]);
    }
}
