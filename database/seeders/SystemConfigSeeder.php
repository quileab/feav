<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            // Identidad del Emisor
            ['id' => 'business_name', 'value' => 'Don Titi Distribuciones', 'type' => 'str', 'description' => 'Razón Social'],
            ['id' => 'address', 'value' => 'Zona Urbana 0, Moussy (Santa Fe)', 'type' => 'str', 'description' => 'Domicilio Comercial'],
            ['id' => 'cuit', 'value' => '20-23526260-7', 'type' => 'str', 'description' => 'CUIT del Sistema'],
            ['id' => 'iibb', 'value' => '141-020834-5', 'type' => 'str', 'description' => 'Ingresos Brutos'],
            ['id' => 'start_date', 'value' => '01/11/2010', 'type' => 'date', 'description' => 'Fecha de Inicio de Actividades'],
            ['id' => 'tax_cond', 'value' => '1', 'type' => 'int', 'description' => 'Código Condición Frente al IVA (1=Resp Inscripto)'],

            // Configuración AFIP
            ['id' => 'fiscal', 'value' => '1', 'type' => 'int', 'description' => 'Habilitado para comprobantes Fiscales?'],
            ['id' => 'production', 'value' => '0', 'type' => 'int', 'description' => '¿Ambiente de Producción? (0=Homologación, 1=Producción)'],
            ['id' => 'environment', 'value' => 'homologation', 'type' => 'str', 'description' => 'Tipo de ambiente actual'],
            ['id' => 'afip_cert', 'value' => 'qb/qbCert.crt', 'type' => 'str', 'description' => 'Ruta relativa al certificado (.crt)'],
            ['id' => 'afip_key', 'value' => 'qb/qbPrivate.key', 'type' => 'str', 'description' => 'Ruta relativa a la clave privada (.key)'],

            // Parámetros Operativos
            ['id' => 'point_of_sale', 'value' => '2', 'type' => 'int', 'description' => 'Punto de Venta por defecto'],
            ['id' => 'warehouse_id', 'value' => '1', 'type' => 'int', 'description' => 'ID del depósito de salida por defecto'],
            ['id' => 'default_category', 'value' => '1', 'type' => 'int', 'description' => 'ID de la categoría de productos por defecto'],
        ];

        foreach ($configs as $config) {
            DB::table('configs')->updateOrInsert(
                ['id' => $config['id']],
                array_merge($config, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}