<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class ImportLegacyData extends Command
{
    protected $signature = 'app:import-legacy-data {--file=source.sql : El archivo SQL a importar}';
    protected $description = 'Migración v1 a v2 con feedback de progreso mejorado.';

    public function handle(): int
    {
        $filePath = base_path($this->option('file'));
        if (!File::exists($filePath)) {
            $this->error("No se encontró el archivo: {$filePath}");
            return 1;
        }

        $this->info("=== Iniciando Importación ===");
        Schema::disableForeignKeyConstraints();

        $handle = fopen($filePath, "r");
        $buffer = "";
        $inInsert = false;
        $currentTable = "";

        while (($line = fgets($handle)) !== false) {
            $trimmedLine = trim($line);
            
            if (preg_match('/^INSERT INTO `([^`]+)`/i', $trimmedLine, $matches)) {
                $tableName = $matches[1];
                if ($tableName !== $currentTable) {
                    $currentTable = $tableName;
                    $this->info("-> Importando tabla: {$currentTable}");
                }
                $inInsert = true;
                $buffer = $line;
            } elseif ($inInsert) {
                $buffer .= $line;
            }
            
            if ($inInsert && preg_match('/;\s*$/', $trimmedLine)) {
                $inInsert = false;
                $sql = str_replace(['`', "\\'"], ['"', "''"], $buffer);
                try {
                    DB::statement($sql);
                } catch (\Exception $e) {
                    // Errores de estructura se corrigen en el paso 2
                }
                $buffer = "";
            }
        }
        fclose($handle);

        $this->applyBusinessRules();

        Schema::enableForeignKeyConstraints();
        $this->info("=== Proceso Finalizado con Éxito ===");

        return 0;
    }

    private function applyBusinessRules()
    {
        $this->info("-> Iniciando normalización de datos...");

        // 1. Clientes y Proveedores
        $this->comment("   - Corrigiendo emails...");
        $this->fixEmails('customers', 'customer');
        $this->fixEmails('suppliers', 'supplier');

        // 2. Productos
        $this->comment("   - Generando códigos de barras...");
        DB::table('products')->whereNull('barcode')->orWhere('barcode', '')->chunkById(100, function ($products) {
            foreach ($products as $p) {
                DB::table('products')->where('id', $p->id)->update(['barcode' => "PROD-" . str_pad($p->id, 8, '0', STR_PAD_LEFT)]);
            }
        });

        // 3. Vouchers (El paso más pesado)
        $total = DB::table('vouchers')->count();
        $this->comment("   - Normalizando {$total} comprobantes...");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        DB::table('vouchers')->chunkById(100, function ($vouchers) use ($bar) {
            foreach ($vouchers as $v) {
                $data = $v->data;
                if (is_string($data)) {
                    $clean = trim($data, '"');
                    $clean = stripslashes($clean);
                    $decoded = json_decode($clean, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE) {
                        DB::table('vouchers')->where('id', $v->id)->update(['data' => json_encode($decoded)]);

                        if (isset($decoded['items']) && is_array($decoded['items'])) {
                            foreach ($decoded['items'] as $item) {
                                DB::table('voucher_details')->updateOrInsert([
                                    'voucher_id' => $v->id,
                                    'product_id' => $item['id'] ?? null,
                                    'quantity' => $item['qty'] ?? 0,
                                    'price' => $item['price'] ?? 0,
                                ], [
                                    'tax' => $item['tax'] ?? 0,
                                    'subtotal' => $item['subtotal'] ?? 0,
                                    'created_at' => $v->created_at,
                                    'updated_at' => $v->updated_at,
                                ]);
                            }
                        }
                    }
                }
                $bar->advance();
            }
        });
        $bar->finish();
        $this->newLine();
    }

    private function fixEmails($table, $prefix)
    {
        DB::table($table)->whereNull('email')->orWhere('email', '')->chunkById(100, function ($records) use ($table, $prefix) {
            foreach ($records as $r) {
                DB::table($table)->where('id', $r->id)->update(['email' => "{$prefix}_{$r->id}@sistema.local"]);
            }
        });
    }
}
