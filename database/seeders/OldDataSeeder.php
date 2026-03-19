<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OldDataSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas para evitar duplicados
        DB::statement('DELETE FROM products');
        DB::statement('DELETE FROM customers');
        DB::statement('DELETE FROM categories');
        DB::statement('DELETE FROM warehouses');

        $sqlPath = database_path('seeders/import_old_data.sql');
        
        if (File::exists($sqlPath)) {
            $sql = File::get($sqlPath);
            // SQLite no usa backticks, pero los acepta en la mayoría de los casos. 
            // Si hay errores, se pueden reemplazar por comillas dobles o nada.
            DB::unprepared($sql);
        }
    }
}
