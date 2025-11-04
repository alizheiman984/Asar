<?php

namespace Database\Seeders;

use App\Models\Government;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GovernmentsSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $governments = [
            ['name' => 'owner',
             'email' =>'owner@gmail.com',
             'password' => Hash::make('password'),
            ],
          
        ];

        foreach ($governments as $government) {
            Government::create($government);
        }
    }
}
