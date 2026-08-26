<?php

namespace App\Services;

use App\Models\Category;
use App\Models\HomepageSetting;

class CategoryService
{
    /**
     * Category configurations for the 4 core business niches.
     */
    public static function getNicheDefinitions(): array
    {
        return [
            'lpg' => [
                'name' => 'LPG Gas Business',
                'industry_noun' => 'LPG Tanks',
                'spec_label' => 'Weight (kg)',
                'spec_placeholder' => 'e.g. 11kg',
                'categories' => [
                    [
                        'slug' => 'tank',
                        'name' => 'LPG Tanks / Cylinders',
                        'description' => 'Standard and commercial LPG tanks and cylinders',
                        'icon_class' => 'fas fa-fire',
                        'color_code' => '#1a6db0',
                        'requires_exchange' => true,
                    ],
                    [
                        'slug' => 'accessories',
                        'name' => 'Safety Accessories & Parts',
                        'description' => 'Regulators, gas hoses, clamps, and fittings',
                        'icon_class' => 'fas fa-tools',
                        'color_code' => '#f7941d',
                        'requires_exchange' => false,
                    ],
                    [
                        'slug' => 'appliances',
                        'name' => 'Burners & Stoves',
                        'description' => 'Single and double burners, cast iron stoves',
                        'icon_class' => 'fas fa-blender',
                        'color_code' => '#27ae60',
                        'requires_exchange' => false,
                    ],
                ],
            ],
            'water' => [
                'name' => 'Water Refilling Station',
                'industry_noun' => 'Purified Water',
                'spec_label' => 'Container Capacity',
                'spec_placeholder' => 'e.g. 5-Gallon (18.9L)',
                'categories' => [
                    [
                        'slug' => 'water',
                        'name' => 'Purified Water Containers',
                        'description' => '5-Gallon round, slim bottles, and refill gallons',
                        'icon_class' => 'fas fa-tint',
                        'color_code' => '#0088cc',
                        'requires_exchange' => true,
                    ],
                    [
                        'slug' => 'dispensers',
                        'name' => 'Dispensers & Stands',
                        'description' => 'Tabletop dispensers, floor stands, and gallon racks',
                        'icon_class' => 'fas fa-faucet',
                        'color_code' => '#00b4d8',
                        'requires_exchange' => false,
                    ],
                    [
                        'slug' => 'accessories',
                        'name' => 'Water Accessories & Pumps',
                        'description' => 'Non-spill caps, electric pumps, and cleaning brushes',
                        'icon_class' => 'fas fa-pump-soap',
                        'color_code' => '#03045e',
                        'requires_exchange' => false,
                    ],
                ],
            ],
            'foods' => [
                'name' => 'Foods & Meals',
                'industry_noun' => 'Food & Meals',
                'spec_label' => 'Portion / Serving Size',
                'spec_placeholder' => 'e.g. 1-2 Persons / 1 Pax',
                'categories' => [
                    [
                        'slug' => 'meals',
                        'name' => 'Main Rice Meals',
                        'description' => 'Freshly cooked rice meals and specialty dishes',
                        'icon_class' => 'fas fa-utensils',
                        'color_code' => '#e03131',
                        'requires_exchange' => false,
                    ],
                    [
                        'slug' => 'snacks',
                        'name' => 'Short Orders & Snacks',
                        'description' => 'Pancit, sandwiches, finger food, and sides',
                        'icon_class' => 'fas fa-burger',
                        'color_code' => '#ff922b',
                        'requires_exchange' => false,
                    ],
                    [
                        'slug' => 'beverages',
                        'name' => 'Beverages & Drinks',
                        'description' => 'Refreshing drinks, iced tea, juices, and desserts',
                        'icon_class' => 'fas fa-mug-hot',
                        'color_code' => '#f06595',
                        'requires_exchange' => false,
                    ],
                    [
                        'slug' => 'bilao',
                        'name' => 'Party Bilao & Trays',
                        'description' => 'Family trays and party feast packages',
                        'icon_class' => 'fas fa-bowl-rice',
                        'color_code' => '#fab005',
                        'requires_exchange' => false,
                    ],
                ],
            ],
            'appliances' => [
                'name' => 'Appliances',
                'industry_noun' => 'Appliances',
                'spec_label' => 'Power / Model Specs',
                'spec_placeholder' => 'e.g. 220V / Double Burner',
                'categories' => [
                    [
                        'slug' => 'appliances',
                        'name' => 'Gas Stoves & Hobs',
                        'description' => 'Single/double burner stoves and glass-top hobs',
                        'icon_class' => 'fas fa-fire-burner',
                        'color_code' => '#0ca678',
                        'requires_exchange' => false,
                    ],
                    [
                        'slug' => 'kitchen',
                        'name' => 'Kitchen Appliances',
                        'description' => 'Rice cookers, electric kettles, and air fryers',
                        'icon_class' => 'fas fa-blender',
                        'color_code' => '#15aabf',
                        'requires_exchange' => false,
                    ],
                    [
                        'slug' => 'parts',
                        'name' => 'Parts & Replacement',
                        'description' => 'Burner caps, ignition knobs, and safety valves',
                        'icon_class' => 'fas fa-screwdriver-wrench',
                        'color_code' => '#20c997',
                        'requires_exchange' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Determine active niche key based on industry noun or settings.
     */
    public static function detectNicheKey(?string $industryNoun = null): string
    {
        if (! $industryNoun) {
            try {
                $setting = HomepageSetting::first();
                $industryNoun = $setting?->industry_noun ?? 'LPG Tanks';
            } catch (\Throwable $e) {
                $industryNoun = 'LPG Tanks';
            }
        }

        $nounLower = strtolower(trim($industryNoun));

        if (str_contains($nounLower, 'water')) {
            return 'water';
        }

        if (str_contains($nounLower, 'food') || str_contains($nounLower, 'meal')) {
            return 'foods';
        }

        if (str_contains($nounLower, 'appliance')) {
            return 'appliances';
        }

        return 'lpg';
    }

    /**
     * Get active niche configuration.
     */
    public static function getCurrentNicheConfig(?string $industryNoun = null): array
    {
        $key = self::detectNicheKey($industryNoun);
        $defs = self::getNicheDefinitions();

        return $defs[$key] ?? $defs['lpg'];
    }

    /**
     * Get category list for current active niche.
     */
    public static function getCategoriesForCurrentNiche(?string $industryNoun = null): array
    {
        $config = self::getCurrentNicheConfig($industryNoun);
        return $config['categories'] ?? [];
    }

    /**
     * Get all allowed category slugs across all niches for validation.
     */
    public static function getAllAllowedCategorySlugs(): array
    {
        $slugs = ['tank', 'water', 'meals', 'snacks', 'beverages', 'bilao', 'dispensers', 'accessories', 'appliances', 'kitchen', 'parts', 'freebie'];
        return array_unique($slugs);
    }

    /**
     * Synchronize and populate database categories table for a given niche.
     */
    public static function syncCategoriesForNiche(string $nicheKey): void
    {
        $defs = self::getNicheDefinitions();
        $config = $defs[$nicheKey] ?? $defs['lpg'];

        if (! isset($config['categories'])) {
            return;
        }

        foreach ($config['categories'] as $catData) {
            Category::updateOrCreate(
                ['slug' => $catData['slug']],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                    'icon_class' => $catData['icon_class'],
                    'color_code' => $catData['color_code'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Format a category slug into a readable label based on active niche.
     */
    public static function formatCategoryLabel(string $slug, ?string $industryNoun = null): string
    {
        $slugLower = strtolower(trim($slug));
        $categories = self::getCategoriesForCurrentNiche($industryNoun);

        foreach ($categories as $cat) {
            if (strtolower($cat['slug']) === $slugLower) {
                return $cat['name'];
            }
        }

        return match ($slugLower) {
            'tank' => 'LPG Tanks / Cylinders',
            'water' => 'Purified Water Containers',
            'meals' => 'Main Rice Meals',
            'snacks' => 'Short Orders & Snacks',
            'beverages' => 'Beverages & Drinks',
            'bilao' => 'Party Bilao & Trays',
            'dispensers' => 'Dispensers & Stands',
            'accessories' => 'Accessories & Parts',
            'appliances' => 'Appliances & Equipment',
            'kitchen' => 'Kitchen Appliances',
            'parts' => 'Parts & Replacement',
            'freebie' => 'Freebies & Rewards',
            default => ucfirst(str_replace(['-', '_'], ' ', $slug)),
        };
    }
}
