<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    // Vape product name parts for realistic names
    protected array $brands = [
        'Elf Bar', 'Lost Mary', 'Funky Republic', 'Geek Bar', 'RAZ', 'SMOK',
        'Vaporesso', 'Uwell', 'Voopoo', 'GeekVape', 'Naked 100', 'Air Factory',
        'Juice Head', 'Pod Juice', 'Hyde', 'Breeze', 'Puff Bar', 'Fume',
    ];

    protected array $productTypes = [
        'Disposable' => ['5000 Puff', '6000 Puff', '7000 Puff', '10000 Puff', '15000 Puff'],
        'Pod System' => ['Starter Kit', 'Pod Kit', 'AIO Kit'],
        'Mod' => ['Box Mod', 'Mod Kit', 'Starter Kit'],
        'E-Liquid' => ['30ml', '60ml', '100ml'],
        'Coils' => ['5 Pack', '3 Pack', 'Replacement Coils'],
        'Pods' => ['4 Pack', '2 Pack', 'Replacement Pods'],
    ];

    protected array $flavors = [
        'Blue Razz Ice', 'Strawberry Mango', 'Watermelon Ice', 'Grape Ice',
        'Peach Mango', 'Cool Mint', 'Lush Ice', 'Banana Ice', 'Cherry Cola',
        'Pink Lemonade', 'Tropical Fruit', 'Mixed Berries', 'Apple Peach',
        'Kiwi Passion Guava', 'Blueberry Lemon', 'Strawberry Banana',
        'Mango Peach', 'Pineapple Coconut', 'Raspberry Mint', 'Orange Soda',
    ];

    protected array $colors = [
        'Black', 'Silver', 'Blue', 'Red', 'Green', 'Purple', 'Gold', 'Rainbow',
    ];

    public function definition(): array
    {
        $brand = $this->faker->randomElement($this->brands);
        $productType = $this->faker->randomElement(array_keys($this->productTypes));
        $variant = $this->faker->randomElement($this->productTypes[$productType]);
        
        // Generate name based on product type
        if (in_array($productType, ['Disposable', 'E-Liquid'])) {
            $flavor = $this->faker->randomElement($this->flavors);
            $name = "{$brand} {$variant} {$flavor}";
        } else {
            $color = $this->faker->randomElement($this->colors);
            $name = "{$brand} {$variant} - {$color}";
        }

        // Price ranges by type
        $priceRanges = [
            'Disposable' => [14.99, 29.99],
            'Pod System' => [19.99, 44.99],
            'Mod' => [49.99, 89.99],
            'E-Liquid' => [12.99, 29.99],
            'Coils' => [9.99, 19.99],
            'Pods' => [9.99, 16.99],
        ];

        $priceRange = $priceRanges[$productType];
        $price = $this->faker->randomFloat(2, $priceRange[0], $priceRange[1]);

        // Generate SKU
        $sku = strtoupper(substr($brand, 0, 3)) . '-' . 
               strtoupper($this->faker->lexify('???')) . '-' . 
               $this->faker->numerify('###');

        return [
            'name' => $name,
            'sku' => $sku,
            'description' => $this->generateDescription($productType, $brand),
            'price' => $price,
            'is_taxable' => true,
            'track_inventory' => true,
            'stock' => $this->faker->numberBetween(0, 100),
            'category_id' => null, // Set in seeder
            'is_active' => $this->faker->boolean(90), // 90% active
            'brand' => $brand,
            'image' => $this->getProductImage($productType),
            'age_restricted' => true,
        ];
    }

    protected function generateDescription(string $type, string $brand): string
    {
        $descriptions = [
            'Disposable' => [
                "Premium disposable vape by {$brand}. Features mesh coil technology for smooth, flavorful hits.",
                "Rechargeable disposable device with long-lasting battery. Enjoy consistent flavor until the last puff.",
                "Compact and portable disposable vape. No refilling or charging needed - just open and enjoy.",
            ],
            'Pod System' => [
                "Sleek pod system by {$brand}. Features adjustable airflow and fast charging.",
                "Compact and powerful pod device. Compatible with both freebase and salt nicotine.",
                "User-friendly pod system perfect for beginners and experienced vapers alike.",
            ],
            'Mod' => [
                "Advanced box mod by {$brand}. Variable wattage and temperature control.",
                "Powerful dual-battery mod with comprehensive safety features.",
                "High-performance mod kit includes tank and coils. Ready to vape out of the box.",
            ],
            'E-Liquid' => [
                "Premium e-liquid crafted with high-quality ingredients. Smooth and satisfying.",
                "Award-winning flavor profile. Available in multiple nicotine strengths.",
                "USA-made e-juice with carefully balanced VG/PG ratio for optimal vapor production.",
            ],
            'Coils' => [
                "Genuine replacement coils for optimal performance. Mesh design for better flavor.",
                "High-quality coils designed for longevity. Compatible with multiple devices.",
                "Premium coils featuring organic cotton wicking for clean, pure taste.",
            ],
            'Pods' => [
                "Replacement pods with built-in coils. Easy snap-in installation.",
                "Leak-resistant pod design. Compatible with both MTL and DTL styles.",
                "Refillable pods with side-fill design for easy e-liquid top-ups.",
            ],
        ];

        return $this->faker->randomElement($descriptions[$type] ?? $descriptions['Disposable']);
    }

    protected function getProductImage(string $type): string
    {
        // Using picsum.photos for placeholder images with consistent seeds
        $seed = $this->faker->numberBetween(1, 500);
        return "https://picsum.photos/seed/{$seed}/400/400";
    }

    // State methods for specific product types
    public function disposable(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement($this->brands) . ' ' . 
                      $this->faker->randomElement(['5000', '6000', '7000']) . ' Puff ' .
                      $this->faker->randomElement($this->flavors),
            'price' => $this->faker->randomFloat(2, 14.99, 29.99),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'track_inventory' => true,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $this->faker->numberBetween(1, 5),
            'track_inventory' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
