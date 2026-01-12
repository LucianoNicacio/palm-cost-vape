<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow, WithChunkReading
{
    private array $categoryCache = [];
    public int $created = 0;
    public int $updated = 0;

    public function model(array $row): ?Product
    {
        // Get or create category
        $categoryId = $this->getCategoryId($row['category'] ?? '');

        if (!$categoryId) {
            return null; // Skip row if no category
        }

        // Check if product exists
        $existing = Product::where('sku', $row['sku'])->first();

        if ($existing) {
            // Update existing
            $existing->update([
                'external_id' => $row['id'] ?? null,
                'name' => $row['name'],
                'price' => (float) $row['price'],
                'is_taxable' => $this->parseBool($row['tax'] ?? true),
                'track_inventory' => $this->parseBool($row['track_inv'] ?? true),
                'stock' => (int) ($row['on_hand'] ?? 0),
                'category_id' => $categoryId,
                'is_active' => strtolower(trim($row['status'] ?? 'active')) === 'active',
            ]);
            $this->updated++;
            return null; // Don't create new
        }

        // Create new
        $this->created++;
        return new Product([
            'external_id' => $row['id'] ?? null,
            'sku' => $row['sku'],
            'name' => $row['name'],
            'price' => (float) $row['price'],
            'is_taxable' => $this->parseBool($row['tax'] ?? true),
            'track_inventory' => $this->parseBool($row['track_inv'] ?? true),
            'stock' => (int) ($row['on_hand'] ?? 0),
            'category_id' => $categoryId,
            'is_active' => strtolower(trim($row['status'] ?? 'active')) === 'active',
            'age_restricted' => true,
        ]);
    }

    private function getCategoryId(string $str): ?int
    {
        if (empty($str)) {
            return null;
        }

        // Check cache first
        if (isset($this->categoryCache[$str])) {
            return $this->categoryCache[$str];
        }

        // Parse "01-Disposables-Nic" format
        $parts = explode('-', $str, 2);
        $code = count($parts) >= 2 ? trim($parts[0]) : '00';
        $name = count($parts) >= 2 ? trim($parts[1]) : $str;

        // Find or create category
        $category = Category::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => (int) $code,
                'is_active' => true,
            ]
        );

        // Cache and return
        $this->categoryCache[$str] = $category->id;
        return $category->id;
    }

    private function parseBool($val): bool
    {
        if (is_bool($val)) {
            return $val;
        }

        return in_array(
            strtolower(trim((string) $val)),
            ['true', '1', 'yes']
        );
    }

    public function chunkSize(): int
    {
        return 100;
    }
}