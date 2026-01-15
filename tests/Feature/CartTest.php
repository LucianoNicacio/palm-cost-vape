// tests/Feature/CartTest.php

<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    session(['age_verified' => true]);

    // Create a category for products
    $this->category = Category::create([
        'code' => 'TEST',
        'name' => 'Test Category',
        'slug' => 'test-category',
        'sort_order' => 0,
        'is_active' => true,
    ]);
});

// Helper to create a product
function createProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Test Product',
        'sku' => 'SKU-' . uniqid(),
        'price' => 10.00,
        'stock' => 50,
        'category_id' => test()->category->id,
        'is_active' => true,
        'is_taxable' => true,
        'track_inventory' => true,
        'age_restricted' => true,
    ], $attributes));
}

describe('Cart Page', function () {

    it('displays empty cart', function () {
        $this->get('/cart')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cart/Index')
                ->has('cartItems', 0)
                ->where('totals.subtotal', 0)
                ->where('totals.item_count', 0)
            );
    });

    it('displays cart with items', function () {
        $product = createProduct(['name' => 'Elf Bar', 'price' => 15.00]);
        session(['cart' => [$product->id => 2]]);

        $this->get('/cart')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('cartItems', 1)
                ->where('cartItems.0.quantity', 2)
                ->where('totals.item_count', 2)
            );
    });

    it('includes tax rate from config', function () {
        config(['app.tax_rate' => 0.07]);

        $this->get('/cart')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('taxRate', 0.07)
            );
    });

    it('only shows active products in cart', function () {
        $active = createProduct(['name' => 'Active Product', 'is_active' => true]);
        $inactive = createProduct(['name' => 'Inactive Product', 'is_active' => false]);

        session(['cart' => [
            $active->id => 1,
            $inactive->id => 1,
        ]]);

        $this->get('/cart')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('cartItems', 1)
                ->where('cartItems.0.product.name', 'Active Product')
            );
    });

});

describe('Add to Cart', function () {

    it('adds product to empty cart', function () {
        $product = createProduct();

        $this->post("/cart/add/{$product->id}", ['quantity' => 2])
            ->assertRedirect()
            ->assertSessionHas('success');

        expect(session('cart'))->toBe([$product->id => 2]);
    });

    it('increases quantity if product already in cart', function () {
        $product = createProduct();
        session(['cart' => [$product->id => 2]]);

        $this->post("/cart/add/{$product->id}", ['quantity' => 3])
            ->assertRedirect();

        expect(session('cart.' . $product->id))->toBe(5);
    });

    it('validates quantity is required', function () {
        $product = createProduct();

        $this->post("/cart/add/{$product->id}", [])
            ->assertSessionHasErrors('quantity');
    });

    it('validates quantity minimum is 1', function () {
        $product = createProduct();

        $this->post("/cart/add/{$product->id}", ['quantity' => 0])
            ->assertSessionHasErrors('quantity');
    });

    it('validates quantity maximum is 99', function () {
        $product = createProduct();

        $this->post("/cart/add/{$product->id}", ['quantity' => 100])
            ->assertSessionHasErrors('quantity');
    });

    it('prevents adding more than stock when tracking inventory', function () {
        $product = createProduct(['stock' => 5, 'track_inventory' => true]);

        $this->post("/cart/add/{$product->id}", ['quantity' => 10])
            ->assertRedirect()
            ->assertSessionHas('error');

        expect(session('cart'))->toBeEmpty();
    });

    it('allows adding any quantity when not tracking inventory', function () {
        $product = createProduct(['stock' => 5, 'track_inventory' => false]);

        $this->post("/cart/add/{$product->id}", ['quantity' => 10])
            ->assertRedirect()
            ->assertSessionHas('success');

        expect(session('cart.' . $product->id))->toBe(10);
    });

});

describe('Update Cart', function () {

    it('updates product quantity', function () {
        $product = createProduct();
        session(['cart' => [$product->id => 2]]);

        $this->put("/cart/update/{$product->id}", ['quantity' => 5])
            ->assertRedirect()
            ->assertSessionHas('success');

        expect(session('cart.' . $product->id))->toBe(5);
    });

    it('removes product when quantity is zero', function () {
        $product = createProduct();
        session(['cart' => [$product->id => 2]]);

        $this->put("/cart/update/{$product->id}", ['quantity' => 0])
            ->assertRedirect();

        expect(session('cart'))->not->toHaveKey($product->id);
    });

    it('removes product when quantity is negative', function () {
        $product = createProduct();
        session(['cart' => [$product->id => 2]]);

        $this->put("/cart/update/{$product->id}", ['quantity' => -1])
            ->assertSessionHasErrors('quantity');
    });

    it('validates quantity is required', function () {
        $product = createProduct();

        $this->put("/cart/update/{$product->id}", [])
            ->assertSessionHasErrors('quantity');
    });

});

describe('Remove from Cart', function () {

    it('removes product from cart', function () {
        $product = createProduct();
        session(['cart' => [$product->id => 2]]);

        $this->delete("/cart/remove/{$product->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        expect(session('cart'))->not->toHaveKey($product->id);
    });

    it('keeps other products when removing one', function () {
        $product1 = createProduct(['name' => 'Product 1']);
        $product2 = createProduct(['name' => 'Product 2']);
        session(['cart' => [
            $product1->id => 2,
            $product2->id => 3,
        ]]);

        $this->delete("/cart/remove/{$product1->id}")
            ->assertRedirect();

        expect(session('cart'))
            ->not->toHaveKey($product1->id)
            ->toHaveKey($product2->id);
    });

    it('handles removing non-existent product gracefully', function () {
        $product = createProduct();
        session(['cart' => []]);

        $this->delete("/cart/remove/{$product->id}")
            ->assertRedirect();
    });

});

describe('Clear Cart', function () {

    it('removes all items from cart', function () {
        $product1 = createProduct(['name' => 'Product 1']);
        $product2 = createProduct(['name' => 'Product 2']);
        session(['cart' => [
            $product1->id => 2,
            $product2->id => 3,
        ]]);

        $this->delete('/cart/clear')
            ->assertRedirect()
            ->assertSessionHas('success');

        expect(session('cart'))->toBeNull();
    });

    it('handles clearing empty cart', function () {
        $this->delete('/cart/clear')
            ->assertRedirect()
            ->assertSessionHas('success');
    });

});

describe('Cart Totals', function () {

    it('calculates subtotal correctly', function () {
        $product1 = createProduct(['price' => 10.00]);
        $product2 = createProduct(['price' => 25.00]);
        session(['cart' => [
            $product1->id => 2,  // 20.00
            $product2->id => 1,  // 25.00
        ]]);

        $this->get('/cart')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('totals.subtotal', fn ($value) => $value == 45.00)
            );
    });

    it('calculates item count correctly', function () {
        $product1 = createProduct();
        $product2 = createProduct();
        session(['cart' => [
            $product1->id => 2,
            $product2->id => 3,
        ]]);

        $this->get('/cart')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('totals.item_count', 5)
            );
    });

});