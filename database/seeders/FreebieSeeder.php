<?php

namespace Database\Seeders;

use App\Models\Freebie;
use Illuminate\Database\Seeder;

class FreebieSeeder extends Seeder
{
    public function run(): void
    {
        Freebie::create([
            'name' => 'Free LPG Tank (Reward)',
            'description' => 'Complimentary LPG tank as loyalty reward',
            'stock' => 999,
            'category' => 'Promotional Gifts',
            'reward_points_required' => 500,
            'redemption_type' => 'loyalty_points',
            'is_active' => true,
        ]);

        Freebie::create([
            'name' => 'Dish Washer Paste (Freebie)',
            'description' => 'Free dish washer paste with every purchase',
            'stock' => 999,
            'category' => 'Accessories',
            'reward_points_required' => 0,
            'redemption_type' => 'auto_included',
            'is_active' => true,
        ]);

        Freebie::create([
            'name' => 'Cloth Hanger Set (Freebie)',
            'description' => 'Free cloth hanger set promotion',
            'stock' => 999,
            'category' => 'Promotional Gifts',
            'reward_points_required' => 0,
            'redemption_type' => 'promotional',
            'is_active' => true,
        ]);

        Freebie::create([
            'name' => 'Safety Gloves (Freebie)',
            'description' => 'Free safety gloves for tank delivery',
            'stock' => 50,
            'category' => 'Safety Items',
            'reward_points_required' => 0,
            'redemption_type' => 'auto_included',
            'is_active' => true,
        ]);

        Freebie::create([
            'name' => 'LPG Sticker Pack (Freebie)',
            'description' => 'Free GasGo branded sticker pack',
            'stock' => 3,
            'category' => 'Promotional Gifts',
            'reward_points_required' => 25,
            'redemption_type' => 'loyalty_points',
            'is_active' => true,
        ]);
    }
}
