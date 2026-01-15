<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');
uses(RefreshDatabase::class)->in('Feature');

/**
 * Create a test product with optional attribute overrides.
 */
function createTestProduct(array $attributes = []): Product
{
    $category = Category::first() ?? Category::create([
        'code' => 'TEST',
        'name' => 'Test Category',
        'slug' => 'test-category',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    return Product::create(array_merge([
        'name' => 'Test Product',
        'sku' => 'SKU-' . uniqid(),
        'price' => 10.00,
        'stock' => 50,
        'category_id' => $category->id,
        'is_active' => true,
        'is_taxable' => true,
        'track_inventory' => true,
        'age_restricted' => true,
    ], $attributes));
}

/**
 * Valid customer data for checkout tests.
 */
function validCustomerData(): array
{
    return [
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'customer_phone' => '386-555-1234',
        'customer_dob' => '1990-01-15',
        'is_subscribed' => true,
    ];
}
