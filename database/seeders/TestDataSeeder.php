<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Categories
        $categories = [
            ['name' => 'Disposables-Nic', 'code' => 'DISP-NIC', 'sort_order' => 1],
            ['name' => 'Delta 8-Disp', 'code' => 'D8-DISP', 'sort_order' => 2],
            ['name' => 'Delta 8-Carts', 'code' => 'D8-CART', 'sort_order' => 3],
            ['name' => 'Delta 8-Edibles', 'code' => 'D8-EDIBLE', 'sort_order' => 4],
            ['name' => 'E-Liquid', 'code' => 'ELIQUID', 'sort_order' => 5],
            ['name' => 'Glassware', 'code' => 'GLASS', 'sort_order' => 6],
            ['name' => 'Accessories', 'code' => 'ACCESS', 'sort_order' => 7],
            ['name' => 'Paper', 'code' => 'PAPER', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat['name'],
                'code' => $cat['code'],
                'slug' => Str::slug($cat['name']),
                'sort_order' => $cat['sort_order'],
                'is_active' => true,
            ]);
        }

        // Create Products
        $products = [
            // Disposables-Nic
            ['name' => 'Raz CA6000', 'price' => 24.99, 'category' => 'DISP-NIC', 'stock' => 50],
            ['name' => 'Raz TN9000', 'price' => 26.99, 'category' => 'DISP-NIC', 'stock' => 45],
            ['name' => 'Raz DC25000', 'price' => 29.99, 'category' => 'DISP-NIC', 'stock' => 30],
            ['name' => 'Lost Mary MO5000', 'price' => 22.99, 'category' => 'DISP-NIC', 'stock' => 60],
            ['name' => 'Elf Bar BC5000', 'price' => 19.99, 'category' => 'DISP-NIC', 'stock' => 75],
            ['name' => 'Funky Republic Ti7000', 'price' => 24.99, 'category' => 'DISP-NIC', 'stock' => 40],
            ['name' => 'Geek Bar Pulse', 'price' => 27.99, 'category' => 'DISP-NIC', 'stock' => 35],
            ['name' => 'SWFT Mod 5000', 'price' => 21.99, 'category' => 'DISP-NIC', 'stock' => 0],

            // Delta 8 Disposables
            ['name' => 'Cake Delta 8 - Blue Dream', 'price' => 34.99, 'category' => 'D8-DISP', 'stock' => 25],
            ['name' => 'Cake Delta 8 - Gelato', 'price' => 34.99, 'category' => 'D8-DISP', 'stock' => 20],
            ['name' => 'Urb Delta 8 - Sour Diesel', 'price' => 32.99, 'category' => 'D8-DISP', 'stock' => 15],
            ['name' => 'Flying Monkey - Gorilla Glue', 'price' => 29.99, 'category' => 'D8-DISP', 'stock' => 30],

            // Delta 8 Carts
            ['name' => 'Delta 8 Cart - OG Kush', 'price' => 29.99, 'category' => 'D8-CART', 'stock' => 40],
            ['name' => 'Delta 8 Cart - Wedding Cake', 'price' => 29.99, 'category' => 'D8-CART', 'stock' => 35],
            ['name' => 'Delta 8 Cart - Pineapple Express', 'price' => 29.99, 'category' => 'D8-CART', 'stock' => 28],
            ['name' => 'Delta 8 Cart - Skywalker OG', 'price' => 31.99, 'category' => 'D8-CART', 'stock' => 22],
            ['name' => 'Delta 8 Cart - Granddaddy Purple', 'price' => 29.99, 'category' => 'D8-CART', 'stock' => 0],

            // Delta 8 Edibles
            ['name' => 'Delta 8 Gummies - Mixed Fruit 500mg', 'price' => 24.99, 'category' => 'D8-EDIBLE', 'stock' => 50],
            ['name' => 'Delta 8 Gummies - Watermelon 500mg', 'price' => 24.99, 'category' => 'D8-EDIBLE', 'stock' => 45],
            ['name' => 'Delta 8 Gummies - Sour Worms 750mg', 'price' => 34.99, 'category' => 'D8-EDIBLE', 'stock' => 30],
            ['name' => 'Delta 8 Chocolate Bar', 'price' => 29.99, 'category' => 'D8-EDIBLE', 'stock' => 20],
            ['name' => 'Delta 8 Honey Sticks 10pk', 'price' => 19.99, 'category' => 'D8-EDIBLE', 'stock' => 40],

            // E-Liquid
            ['name' => 'Naked 100 - Lava Flow 60ml', 'price' => 19.99, 'category' => 'ELIQUID', 'stock' => 30],
            ['name' => 'Naked 100 - Hawaiian POG 60ml', 'price' => 19.99, 'category' => 'ELIQUID', 'stock' => 25],
            ['name' => 'Naked 100 - Amazing Mango 60ml', 'price' => 19.99, 'category' => 'ELIQUID', 'stock' => 28],
            ['name' => 'Juice Head - Blueberry Lemon 100ml', 'price' => 24.99, 'category' => 'ELIQUID', 'stock' => 20],
            ['name' => 'Candy King - Strawberry Watermelon 100ml', 'price' => 22.99, 'category' => 'ELIQUID', 'stock' => 15],
            ['name' => 'VGOD - Cubano 60ml', 'price' => 21.99, 'category' => 'ELIQUID', 'stock' => 0],

            // Glassware
            ['name' => 'Glass Water Pipe - 12" Beaker', 'price' => 49.99, 'category' => 'GLASS', 'stock' => 10],
            ['name' => 'Glass Water Pipe - 14" Straight', 'price' => 59.99, 'category' => 'GLASS', 'stock' => 8],
            ['name' => 'Silicone Bong - Unbreakable', 'price' => 34.99, 'category' => 'GLASS', 'stock' => 15],
            ['name' => 'Glass Hand Pipe - Spoon', 'price' => 14.99, 'category' => 'GLASS', 'stock' => 25],
            ['name' => 'Glass Hand Pipe - Sherlock', 'price' => 19.99, 'category' => 'GLASS', 'stock' => 18],
            ['name' => 'Dab Rig - Mini 6"', 'price' => 44.99, 'category' => 'GLASS', 'stock' => 12],

            // Accessories
            ['name' => 'Grinder - 4pc Metal 2"', 'price' => 12.99, 'category' => 'ACCESS', 'stock' => 40],
            ['name' => 'Grinder - 4pc Metal 2.5"', 'price' => 16.99, 'category' => 'ACCESS', 'stock' => 35],
            ['name' => 'Rolling Tray - Small', 'price' => 9.99, 'category' => 'ACCESS', 'stock' => 50],
            ['name' => 'Rolling Tray - Large', 'price' => 14.99, 'category' => 'ACCESS', 'stock' => 30],
            ['name' => 'Smell Proof Bag - Small', 'price' => 7.99, 'category' => 'ACCESS', 'stock' => 60],
            ['name' => 'Torch Lighter - Single Flame', 'price' => 8.99, 'category' => 'ACCESS', 'stock' => 45],
            ['name' => 'Torch Lighter - Triple Flame', 'price' => 14.99, 'category' => 'ACCESS', 'stock' => 25],

            // Paper
            ['name' => 'RAW Papers - King Size', 'price' => 4.99, 'category' => 'PAPER', 'stock' => 100],
            ['name' => 'RAW Papers - 1 1/4', 'price' => 3.99, 'category' => 'PAPER', 'stock' => 80],
            ['name' => 'RAW Cones - King Size 6pk', 'price' => 6.99, 'category' => 'PAPER', 'stock' => 60],
            ['name' => 'RAW Cones - King Size 32pk', 'price' => 19.99, 'category' => 'PAPER', 'stock' => 25],
            ['name' => 'Elements Papers - King Size', 'price' => 4.49, 'category' => 'PAPER', 'stock' => 70],
            ['name' => 'Juicy Jay - Watermelon King', 'price' => 2.99, 'category' => 'PAPER', 'stock' => 55],
            ['name' => 'Blunt Wraps - Honey 2pk', 'price' => 2.49, 'category' => 'PAPER', 'stock' => 90],
            ['name' => 'Backwoods - Honey 5pk', 'price' => 9.99, 'category' => 'PAPER', 'stock' => 40],
        ];

        $counter = 1;
        foreach ($products as $prod) {
            $category = Category::where('code', $prod['category'])->first();

            Product::create([
                'name' => $prod['name'],
                'sku' => strtoupper($prod['category'] . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT)),
                'price' => $prod['price'],
                'stock' => $prod['stock'],
                'category_id' => $category->id,
                'is_active' => true,
                'is_taxable' => true,
                'track_inventory' => true,
                'age_restricted' => true,
            ]);
            $counter++;
        }

        $this->command->info('Created ' . Category::count() . ' categories');
        $this->command->info('Created ' . Product::count() . ' products');
    }
}