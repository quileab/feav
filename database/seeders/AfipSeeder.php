<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AfipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = base_path('fea/database/seeders/factafip-types-plus-Data.sql');
        $sql = File::get($path);
        
        $neededTables = [
            'currency_types', 'customer_id_types', 'unit_types', 
            'province_id_types', 'responsibility_types', 
            'tax_condition_types', 'voucher_types', 
            'included_concept_types', 'other_tributes_types', 'categories', 'configs'
        ];

        foreach ($neededTables as $tableName) {
            $this->command->info("Seeding table: $tableName");
            
            // Extract the INSERT statement
            if (preg_match("/INSERT INTO `$tableName` .* VALUES\s*(.*);/sU", $sql, $matches)) {
                $values = $matches[1];
                
                // SQLite uses double quotes for identifiers and single quotes for strings
                // The SQL file already uses single quotes for strings, but backticks for table names
                $query = "INSERT INTO $tableName VALUES $values";
                
                try {
                    DB::statement($query);
                } catch (\Exception $e) {
                    $this->command->error("Error seeding $tableName: " . $e->getMessage());
                    
                    // Fallback: try row by row if bulk fails
                    $rows = preg_split("/\),\s*\(/", trim($values, "() "));
                    foreach ($rows as $row) {
                        try {
                            DB::statement("INSERT INTO $tableName VALUES ($row)");
                        } catch (\Exception $e2) {
                            // Last resort skip
                        }
                    }
                }
            }
        }
    }
}
