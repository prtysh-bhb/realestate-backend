<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreditPackagesSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter Pack',
                'price' => 99.00,
                'coins' => 100,
                'description' => 'Perfect for beginners. Get 100 credits for ₹99',
                'status' => 'active',
            ],
            [
                'name' => 'Pro Pack',
                'price' => 449.00,
                'coins' => 500,
                'description' => 'Most popular! Get 500 credits for ₹449 (Save 10%)',
                'status' => 'active',
            ],
            [
                'name' => 'Mega Pack',
                'price' => 799.00,
                'coins' => 1000,
                'description' => 'Best value! Get 1000 credits for ₹799 (Save 20%)',
                'status' => 'active',
            ],
        ];

        foreach ($packages as $package) {
            DB::table('credits')->insert(array_merge($package, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}