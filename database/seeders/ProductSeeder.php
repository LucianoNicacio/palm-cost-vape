<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Run CategorySeeder first.');
            return;
        }

        // Create products for each category
        $productsPerCategory = [
            'disposable-vapes' => 20,
            'e-liquids' => 15,
            'pod-systems' => 10,
            'mods-kits' => 8,
            'coils-pods' => 12,
            'accessories' => 10,
            'nicotine-pouches' => 6,
            'cbd-products' => 5,
        ];

        $totalCreated = 0;

        foreach ($categories as $category) {
            $count = $productsPerCategory[$category->slug] ?? 5;
            
            Product::factory()
                ->count($count)
                ->create([
                    'category_id' => $category->id,
                ]);

            $totalCreated += $count;
        }

        // Add some out of stock products
        Product::factory()
            ->count(5)
            ->outOfStock()
            ->create([
                'category_id' => $categories->random()->id,
            ]);

        // Add some low stock products
        Product::factory()
            ->count(8)
            ->lowStock()
            ->create([
                'category_id' => $categories->random()->id,
            ]);

        // Add some inactive products
        Product::factory()
            ->count(5)
            ->inactive()
            ->create([
                'category_id' => $categories->random()->id,
            ]);

        $this->command->info("Created {$totalCreated} products + 18 special status products.");
    }
}
