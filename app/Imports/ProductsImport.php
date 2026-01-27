<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsOnError, SkipsOnFailure
{
    private array $categoryCache = [];
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    public function model(array $row): ?Product
    {
        if (empty($row['sku'])) {
            $maxSku = Product::whereRaw('sku REGEXP "^[0-9]+$"')
                ->orderByRaw('CAST(sku AS UNSIGNED) DESC')
                ->value('sku');

            $sku = $maxSku ? ((int)$maxSku + 1) : 10000;
        } else {
            $sku = trim($row['sku']);
        }

        $name = !empty($row['name']) ? $row['name'] : 'Product ' . $sku;
        $categoryId = $this->getCategoryId($row['category'] ?? 'Uncategorized');
        $existing = Product::where('sku', $sku)->first();

        if ($existing) {
            $existing->update([
                'external_id' => $row['id'] ?? null,
                'name' => $name,
                'price' => (float) ($row['price'] ?? 0),
                'is_taxable' => $this->parseBool($row['tax'] ?? true),
                'track_inventory' => $this->parseBool($row['track_inv'] ?? true),
                'stock' => (int) ($row['on_hand'] ?? 0),
                'category_id' => $categoryId,
                'is_active' => strtolower(trim($row['status'] ?? 'active')) === 'active',
            ]);
            $this->updated++;
            return null;
        }

        $this->created++;
        return new Product([
            'external_id' => $row['id'] ?? null,
            'sku' => $sku,
            'name' => $name,
            'price' => (float) ($row['price'] ?? 0),
            'is_taxable' => $this->parseBool($row['tax'] ?? true),
            'track_inventory' => $this->parseBool($row['track_inv'] ?? true),
            'stock' => (int) ($row['on_hand'] ?? 0),
            'category_id' => $categoryId,
            'is_active' => strtolower(trim($row['status'] ?? 'active')) === 'active',
            'age_restricted' => true,
        ]);
    }

    private function getCategoryId(string $str): int
    {
        if (empty($str)) {
            $str = '99-Uncategorized';
        }

        if (isset($this->categoryCache[$str])) {
            return $this->categoryCache[$str];
        }

        $parts = explode('-', $str, 2);
        $code = count($parts) >= 2 ? trim($parts[0]) : '99';
        $name = count($parts) >= 2 ? trim($parts[1]) : $str;

        $category = Category::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => (int) $code,
                'is_active' => true,
            ]
        );

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

    public function onError(\Throwable $error)
    {
        \Log::error('Product import error: ' . $error->getMessage());
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            \Log::warning('Import row ' . $failure->row() . ' failed: ' . implode(', ', $failure->errors()));
        }
    }
}