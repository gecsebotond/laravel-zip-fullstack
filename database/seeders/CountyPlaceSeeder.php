<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\County;
use App\Models\Place;
use Illuminate\Support\Facades\DB;

class CountyPlaceSeeder extends Seeder
{
public function run()
    {
        $file = storage_path('iranyitoszamok.csv');

        if (!file_exists($file) || !is_readable($file)) {
            $this->command->error("CSV file not found or not readable: $file");
            return;
        }

        DB::transaction(function () use ($file) {
            $handle = fopen($file, 'r');
            if ($handle === false) return;

            $countiesCache = [];
            $now = date('Y-m-d H:i:s');

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if (count($row) < 3) continue;

                $zipCode = trim($row[0]);
                $placeName = trim($row[1]);
                $countyName = trim($row[2]);

                if (!$zipCode || !$placeName || !$countyName) continue;

                if (!isset($countiesCache[$countyName])) {
                    $county = County::firstOrCreate(
                        ['name' => $countyName],
                        ['created_at' => $now, 'updated_at' => $now]
                    );
                    $countiesCache[$countyName] = $county->id;
                }

                Place::firstOrCreate(
                    [
                        'postal_code' => $zipCode,
                        'name' => $placeName,
                        'county_id' => $countiesCache[$countyName],
                    ],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }

            fclose($handle);
        });

        $this->command->info("CSV import finished successfully!");
    }
}
