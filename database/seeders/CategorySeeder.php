<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'DISP',
                'name' => 'Disposable Vapes',
                'slug' => 'disposable-vapes',
                'description' => 'Single-use vape devices, pre-filled and ready to use',
                'sort_order' => 0,
                'placeholder' => 'https://picsum.photos/seed/vape1/400/400',
            ],
            [
                'code' => 'ELIQ',
                'name' => 'E-Liquids',
                'slug' => 'e-liquids',
                'description' => 'Premium vape juices in various flavors and nicotine levels',
                'sort_order' => 1,
                'placeholder' => 'https://picsum.photos/seed/vape2/400/400',
            ],
            [
                'code' => 'PODS',
                'name' => 'Pod Systems',
                'slug' => 'pod-systems',
                'description' => 'Compact, refillable pod-based vaping devices',
                'sort_order' => 2,
                'placeholder' => 'https://picsum.photos/seed/vape3/400/400',
            ],
            [
                'code' => 'MODS',
                'name' => 'Mods & Kits',
                'slug' => 'mods-kits',
                'description' => 'Advanced vaping devices and starter kits',
                'sort_order' => 3,
                'placeholder' => 'https://picsum.photos/seed/vape4/400/400',
            ],
            [
                'code' => 'COIL',
                'name' => 'Coils & Pods',
                'slug' => 'coils-pods',
                'description' => 'Replacement coils and pod cartridges',
                'sort_order' => 4,
                'placeholder' => 'https://picsum.photos/seed/vape5/400/400',
            ],
            [
                'code' => 'ACCS',
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Batteries, chargers, cases, and more',
                'sort_order' => 5,
                'placeholder' => 'https://picsum.photos/seed/vape6/400/400',
            ],
            [
                'code' => 'NIC',
                'name' => 'Nicotine Pouches',
                'slug' => 'nicotine-pouches',
                'description' => 'Tobacco-free nicotine pouches',
                'sort_order' => 6,
                'placeholder' => 'https://picsum.photos/seed/vape7/400/400',
            ],
            [
                'code' => 'CBD',
                'name' => 'CBD Products',
                'slug' => 'cbd-products',
                'description' => 'CBD vapes, oils, and edibles',
                'sort_order' => 7,
                'placeholder' => 'https://picsum.photos/seed/vape8/400/400',
            ],
        ];

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('categories');

        foreach ($categories as $category) {
            $imagePath = null;

            // Download placeholder image
            if (isset($category['placeholder'])) {
                try {
                    $response = Http::get($category['placeholder']);
                    if ($response->successful()) {
                        $filename = "categories/{$category['slug']}.jpg";
                        Storage::disk('public')->put($filename, $response->body());
                        $imagePath = $filename;
                    }
                } catch (\Exception $e) {
                    // Skip image if download fails
                }
            }

            unset($category['placeholder']);

            Category::updateOrCreate(
                ['code' => $category['code']],
                array_merge($category, ['image' => $imagePath])
            );
        }
    }
}