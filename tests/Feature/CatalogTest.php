<?php

// tests/Feature/CatalogTest.php

use App\Models\Category;

beforeEach(function () {
    session(['age_verified' => true]);
});

describe('Catalog Page', function () {

    it('displays the catalog page', function () {
        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Catalog/Index'));
    });

    it('shows only active products', function () {
        createTestProduct(['name' => 'Active Product', 'is_active' => true]);
        createTestProduct(['name' => 'Inactive Product', 'is_active' => false]);

        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Active Product')
            );
    });

    it('includes categories with product counts', function () {
        $category = Category::create([
            'code' => 'VAPE',
            'name' => 'Vapes',
            'slug' => 'vapes',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        createTestProduct(['category_id' => $category->id]);
        createTestProduct(['category_id' => $category->id]);
        createTestProduct(['category_id' => $category->id]);

        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('categories.0.products_count', 3)
            );
    });

    it('includes tax rate from config', function () {
        config(['app.tax_rate' => 0.07]);

        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('taxRate', 0.07)
            );
    });

});

describe('Category Filter', function () {

    it('filters products by category slug', function () {
        $vapes = Category::create([
            'code' => 'VAPE',
            'name' => 'Vapes',
            'slug' => 'vapes',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $liquids = Category::create([
            'code' => 'LIQ',
            'name' => 'Liquids',
            'slug' => 'liquids',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        createTestProduct(['name' => 'Elf Bar', 'category_id' => $vapes->id]);
        createTestProduct(['name' => 'Juice Head', 'category_id' => $liquids->id]);

        $this->get('/shop?category=vapes')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Elf Bar')
            );
    });

    it('shows all products for invalid category slug', function () {
        createTestProduct(['name' => 'Product 1']);
        createTestProduct(['name' => 'Product 2']);

        $this->get('/shop?category=nonexistent')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 2)
            );
    });

    it('preserves category filter in response', function () {
        $this->get('/shop?category=vapes')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.category', 'vapes')
            );
    });

});

describe('Search Filter', function () {

    it('searches products by name', function () {
        createTestProduct(['name' => 'Elf Bar BC5000']);
        createTestProduct(['name' => 'Lost Mary']);
        createTestProduct(['name' => 'Juice Head']);

        $this->get('/shop?search=Elf')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Elf Bar BC5000')
            );
    });

    it('search is case insensitive', function () {
        createTestProduct(['name' => 'Elf Bar']);

        $this->get('/shop?search=elf')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
            );
    });

    it('returns empty for no search matches', function () {
        createTestProduct(['name' => 'Elf Bar']);

        $this->get('/shop?search=nonexistent')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 0)
            );
    });

    it('preserves search filter in response', function () {
        $this->get('/shop?search=test')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.search', 'test')
            );
    });

});

describe('In Stock Filter', function () {

    it('filters to only in-stock products', function () {
        createTestProduct(['name' => 'In Stock', 'stock' => 10]);
        createTestProduct(['name' => 'Out of Stock', 'stock' => 0]);

        $this->get('/shop?in_stock=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'In Stock')
            );
    });

    it('shows all products when in_stock is false', function () {
        createTestProduct(['stock' => 10]);
        createTestProduct(['stock' => 0]);

        $this->get('/shop?in_stock=0')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 2)
            );
    });

    it('shows all products when in_stock not provided', function () {
        createTestProduct(['stock' => 10]);
        createTestProduct(['stock' => 0]);

        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 2)
            );
    });

});

describe('Sorting', function () {

    it('sorts by name by default', function () {
        createTestProduct(['name' => 'Zebra Vape']);
        createTestProduct(['name' => 'Alpha Vape']);
        createTestProduct(['name' => 'Beta Vape']);

        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.data.0.name', 'Alpha Vape')
                ->where('products.data.1.name', 'Beta Vape')
                ->where('products.data.2.name', 'Zebra Vape')
            );
    });

    it('sorts by price ascending', function () {
        createTestProduct(['name' => 'Expensive', 'price' => 50.00]);
        createTestProduct(['name' => 'Cheap', 'price' => 10.00]);
        createTestProduct(['name' => 'Medium', 'price' => 25.00]);

        $this->get('/shop?sort=price')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.data.0.name', 'Cheap')
                ->where('products.data.1.name', 'Medium')
                ->where('products.data.2.name', 'Expensive')
            );
    });

    it('sorts by newest first', function () {
        createTestProduct(['name' => 'Old Product']);

        $this->travel(1)->days();

        createTestProduct(['name' => 'New Product']);

        $this->get('/shop?sort=created_at')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('products.data.0.name', 'New Product')
                ->where('products.data.1.name', 'Old Product')
            );
    });

    it('preserves sort filter in response', function () {
        $this->get('/shop?sort=price')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.sort', 'price')
            );
    });

});

describe('Pagination', function () {

    it('paginates at 24 per page', function () {
        for ($i = 0; $i < 30; $i++) {
            createTestProduct(['name' => "Product {$i}"]);
        }

        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 24)
                ->where('products.total', 30)
            );
    });

    it('can access second page', function () {
        for ($i = 0; $i < 30; $i++) {
            createTestProduct(['name' => "Product {$i}"]);
        }

        $this->get('/shop?page=2')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 6)
                ->where('products.current_page', 2)
            );
    });

});

describe('Combined Filters', function () {

    it('combines category and search filters', function () {
        $vapes = Category::create([
            'code' => 'VAPE',
            'name' => 'Vapes',
            'slug' => 'vapes',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $liquids = Category::create([
            'code' => 'LIQ',
            'name' => 'Liquids',
            'slug' => 'liquids',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        createTestProduct(['name' => 'Elf Bar Vape', 'category_id' => $vapes->id]);
        createTestProduct(['name' => 'Lost Mary Vape', 'category_id' => $vapes->id]);
        createTestProduct(['name' => 'Elf Bar Juice', 'category_id' => $liquids->id]);

        $this->get('/shop?category=vapes&search=Elf')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Elf Bar Vape')
            );
    });

    it('combines search and in_stock filters', function () {
        createTestProduct(['name' => 'Elf Bar In Stock', 'stock' => 10]);
        createTestProduct(['name' => 'Elf Bar Out', 'stock' => 0]);
        createTestProduct(['name' => 'Lost Mary', 'stock' => 10]);

        $this->get('/shop?search=Elf&in_stock=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Elf Bar In Stock')
            );
    });

    it('combines all filters with sorting', function () {
        $vapes = Category::create([
            'code' => 'VAPE',
            'name' => 'Vapes',
            'slug' => 'vapes',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        createTestProduct([
            'name' => 'Elf Expensive',
            'category_id' => $vapes->id,
            'stock' => 10,
            'price' => 30.00,
        ]);
        createTestProduct([
            'name' => 'Elf Cheap',
            'category_id' => $vapes->id,
            'stock' => 5,
            'price' => 15.00,
        ]);
        createTestProduct([
            'name' => 'Elf Out of Stock',
            'category_id' => $vapes->id,
            'stock' => 0,
            'price' => 10.00,
        ]);

        $this->get('/shop?category=vapes&search=Elf&in_stock=1&sort=price')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('products.data', 2)
                ->where('products.data.0.name', 'Elf Cheap')
                ->where('products.data.1.name', 'Elf Expensive')
            );
    });

});
