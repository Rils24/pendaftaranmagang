<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Universitas;

class UniversitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Membaca file SQL data universitas
        $sqlFile = file_get_contents(database_path('seeders/sql/data_universitas.sql'));
        
        // Ekstrak data universitas dari file SQL
        preg_match_all("/\(\d+, '([^']+)'\)/", $sqlFile, $matches);
        
        if (isset($matches[1])) {
            $universities = $matches[1];
            
            // Hapus data yang ada (opsional)
            Universitas::truncate();
            
            // Insert data ke tabel universitas
            foreach ($universities as $university) {
                Universitas::create([
                    'nama_universitas' => trim($university)
                ]);
            }
            
            $this->command->info(count($universities) . ' universitas berhasil ditambahkan');
        } else {
            $this->command->error('Gagal mengekstrak data universitas dari file SQL');
        }
    }
}