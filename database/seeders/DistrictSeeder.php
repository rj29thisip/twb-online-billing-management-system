<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\District;

class DistrictSeeder extends Seeder {
    public function run(): void {
        $districts = [
            ['name' => 'Headquarters', 'code' => 'HQ',    'is_headquarters' => true,  'description' => 'Main HQ — can see all customers'],
            ['name' => 'Area 1',       'code' => 'AREA1', 'is_headquarters' => false, 'description' => 'District Area 1'],
            ['name' => 'Area 2',       'code' => 'AREA2', 'is_headquarters' => false, 'description' => 'District Area 2'],
            ['name' => 'Area 3',       'code' => 'AREA3', 'is_headquarters' => false, 'description' => 'District Area 3'],
            ['name' => 'Area 4',       'code' => 'AREA4', 'is_headquarters' => false, 'description' => 'District Area 4'],
            ['name' => 'Area 5',       'code' => 'AREA5', 'is_headquarters' => false, 'description' => 'District Area 5'],
        ];
        foreach ($districts as $d) {
            District::firstOrCreate(['code' => $d['code']], array_merge($d, ['is_active' => true]));
        }
    }
}
