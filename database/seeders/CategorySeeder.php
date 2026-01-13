<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['code' => '01', 'name' => 'Disposables-Nic', 'sort_order' => 1],
            ['code' => '02', 'name' => 'E-Liquid', 'sort_order' => 2],
            ['code' => '03', 'name' => 'Delta 8-Disp', 'sort_order' => 3],
            ['code' => '04', 'name' => 'Delta 8-Carts', 'sort_order' => 4],
            ['code' => '05', 'name' => 'Delta 8-Edibles', 'sort_order' => 5],
            ['code' => '30', 'name' => 'Paper', 'sort_order' => 30],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'slug' => Str::slug($cat['name']),
                    'sort_order' => $cat['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}