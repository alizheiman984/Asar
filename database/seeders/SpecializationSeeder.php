<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specializations = [
            ['name' => 'معلوماتيه'],
            ['name' => 'طب'],
            ['name' => 'تمريض'],
            ['name' => 'شبكات'],
            ['name' => 'اعمال حره'],
      
        ];

        foreach ($specializations as $specialization) {
            Specialization::create($specialization);
        }
    }
}
