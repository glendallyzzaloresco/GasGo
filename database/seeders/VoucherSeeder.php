<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Voucher::create([
            'name' => '₱50 OFF Voucher',
            'description' => 'Get ₱50 discount on your next order',
            'discount_amount' => 50,
            'reward_points_required' => 10,
            'is_active' => true,
        ]);

        Voucher::create([
            'name' => '₱100 OFF Voucher',
            'description' => 'Get ₱100 discount on orders ₱500 and up',
            'discount_amount' => 100,
            'reward_points_required' => 20,
            'is_active' => true,
        ]);

        Voucher::create([
            'name' => '₱150 OFF Voucher',
            'description' => 'Get ₱150 discount on orders ₱1000 and up',
            'discount_amount' => 150,
            'reward_points_required' => 30,
            'is_active' => true,
        ]);
    }
}
