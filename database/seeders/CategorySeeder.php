<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Disposable Vapes',
                'slug' => 'disposable-vapes',
                'code' => 'DISP',
                'description' => 'Single-use vape devices, pre-filled and ready to use',
            ],
            [
                'name' => 'E-Liquids',
                'slug' => 'e-liquids',
                'code' => 'ELIQ',
                'description' => 'Vape juices in various flavors and nicotine strengths',
            ],
            [
                'name' => 'Pod Systems',
                'slug' => 'pod-systems',
                'code' => 'PODS',
                'description' => 'Compact, refillable pod-based vape devices',
            ],
            [
                'name' => 'Mods & Kits',
                'slug' => 'mods-kits',
                'code' => 'MODS',
                'description' => 'Advanced vaping devices and starter kits',
            ],
            [
                'name' => 'Coils & Pods',
                'slug' => 'coils-pods',
                'code' => 'COIL',
                'description' => 'Replacement coils and pod cartridges',
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'code' => 'ACCS',
                'description' => 'Batteries, chargers, cases, and more',
            ],
            [
                'name' => 'Nicotine Pouches',
                'slug' => 'nicotine-pouches',
                'code' => 'NICO',
                'description' => 'Tobacco-free nicotine pouches',
            ],
            [
                'name' => 'CBD Products',
                'slug' => 'cbd-products',
                'code' => 'CBD',
                'description' => 'CBD vapes, oils, and edibles',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Created ' . count($categories) . ' categories.');
    }
}
