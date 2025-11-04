<?php

namespace Database\Seeders;

use App\Models\CampaignType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CampaignTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $CampaignTypes = [
            ['name' => 'توعيه'],
            ['name' => 'تنظيف'],
            ['name' => 'تمريض'],
            ['name' => 'اعمال حره'],
      
        ];

        foreach ($CampaignTypes as $CampaignType) {
            CampaignType::create($CampaignType);
        }
    }
}
