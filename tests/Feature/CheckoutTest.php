<?php

// tests/Feature/CheckoutTest.php

use App\Models\Customer;
use App\Models\Reservation;
use App\Notifications\ReservationConfirmation;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    session(['age_verified' => true]);
    Notification::fake();
});

describe('Checkout Page', function () {

    it('redirects to shop if cart is empty', function () {
        $this->get('/checkout')
            ->assertRedirect('/shop')
            ->assertSessionHas('error');
    });

    it('displays checkout page with cart items', function () {
        $product = createTestProduct(['price' => 25.00]);
        session(['cart' => [$product->id => 2]]);

        $this->get('/checkout')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Checkout/Index')
                ->has('cartItems', 1)
                ->has('totals')
                ->has('taxRate')
                ->has('ageRequirement')
            );
    });

    it('includes age requirement from config', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);
        config(['app.age_requirement' => 21]);

        $this->get('/checkout')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('ageRequirement', 21)
            );
    });

});

describe('Checkout Validation', function () {

    beforeEach(function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);
    });

    it('requires customer name', function () {
        $data = validCustomerData();
        unset($data['customer_name']);

        $this->post('/checkout', $data)
            ->assertSessionHasErrors('customer_name');
    });

    it('requires customer email', function () {
        $data = validCustomerData();
        unset($data['customer_email']);

        $this->post('/checkout', $data)
            ->assertSessionHasErrors('customer_email');
    });

    it('requires valid email format', function () {
        $data = validCustomerData();
        $data['customer_email'] = 'not-an-email';

        $this->post('/checkout', $data)
            ->assertSessionHasErrors('customer_email');
    });

    it('requires customer phone', function () {
        $data = validCustomerData();
        unset($data['customer_phone']);

        $this->post('/checkout', $data)
            ->assertSessionHasErrors('customer_phone');
    });

    it('requires customer date of birth', function () {
        $data = validCustomerData();
        unset($data['customer_dob']);

        $this->post('/checkout', $data)
            ->assertSessionHasErrors('customer_dob');
    });

    it('requires customer to be at least 21 years old', function () {
        $data = validCustomerData();
        $data['customer_dob'] = now()->subYears(18)->format('Y-m-d');

        $this->post('/checkout', $data)
            ->assertSessionHasErrors('customer_dob');
    });

    it('accepts customer who is exactly 21', function () {
        $data = validCustomerData();
        $data['customer_dob'] = now()->subYears(21)->subDay()->format('Y-m-d');

        $this->post('/checkout', $data)
            ->assertSessionHasNoErrors();
    });

});

describe('Checkout Process', function () {

    it('creates a new customer', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', validCustomerData());

        $this->assertDatabaseHas('customers', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    });

    it('uses existing customer if email matches', function () {
        $existingCustomer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'phone' => '000-000-0000',
            'dob' => '1985-01-01',
        ]);

        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', validCustomerData());

        expect(Customer::count())->toBe(1);
    });

    it('creates a reservation', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 2]]);

        $this->post('/checkout', validCustomerData());

        $this->assertDatabaseHas('reservations', [
            'status' => 'pending',
        ]);
    });

    it('creates reservation items', function () {
        $product = createTestProduct(['price' => 15.00]);
        session(['cart' => [$product->id => 3]]);

        $this->post('/checkout', validCustomerData());

        $this->assertDatabaseHas('reservation_items', [
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 15.00,
        ]);
    });

    it('decrements product stock', function () {
        $product = createTestProduct(['stock' => 50]);
        session(['cart' => [$product->id => 5]]);

        $this->post('/checkout', validCustomerData());

        expect($product->fresh()->stock)->toBe(45);
    });

    it('clears cart after successful checkout', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', validCustomerData());

        expect(session('cart'))->toBeNull();
    });

    it('redirects to confirmation page', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);

        $response = $this->post('/checkout', validCustomerData());

        $reservation = Reservation::first();
        $response->assertRedirect("/confirmation/{$reservation->confirmation_number}");
    });

    it('sends confirmation notification', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', validCustomerData());

        $customer = Customer::where('email', 'john@example.com')->first();
        Notification::assertSentTo($customer, ReservationConfirmation::class);
    });

    it('generates unique confirmation number', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', validCustomerData());

        $reservation = Reservation::first();
        expect($reservation->confirmation_number)->not->toBeNull();
    });

});

describe('Stock Validation', function () {

    it('prevents checkout if not enough stock', function () {
        $product = createTestProduct(['stock' => 5, 'track_inventory' => true]);
        session(['cart' => [$product->id => 10]]);

        $this->post('/checkout', validCustomerData())
            ->assertRedirect()
            ->assertSessionHas('error');

        expect(Reservation::count())->toBe(0);
    });

    it('allows checkout when not tracking inventory', function () {
        $product = createTestProduct(['stock' => 5, 'track_inventory' => false]);
        session(['cart' => [$product->id => 10]]);

        $this->post('/checkout', validCustomerData());

        expect(Reservation::count())->toBe(1);
    });

    it('rolls back everything on stock error', function () {
        $product1 = createTestProduct(['name' => 'Product 1', 'stock' => 50]);
        $product2 = createTestProduct(['name' => 'Product 2', 'stock' => 2]);

        session(['cart' => [
            $product1->id => 5,
            $product2->id => 10,
        ]]);

        $this->post('/checkout', validCustomerData());

        expect(Reservation::count())->toBe(0);
        expect(Customer::count())->toBe(0);
        expect($product1->fresh()->stock)->toBe(50);
    });

});

describe('Checkout with Empty Cart', function () {

    it('redirects to shop if cart becomes empty', function () {
        session(['cart' => []]);

        $this->post('/checkout', validCustomerData())
            ->assertRedirect('/shop')
            ->assertSessionHas('error');
    });

});

describe('Confirmation Page', function () {

    it('displays reservation details', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', validCustomerData());

        $reservation = Reservation::first();

        $this->get("/confirmation/{$reservation->confirmation_number}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Checkout/Confirmation')
                ->has('reservation')
                ->has('storeInfo')
            );
    });

    it('returns 404 for invalid confirmation number', function () {
        $this->get('/confirmation/INVALID-123')
            ->assertNotFound();
    });

    it('includes store info', function () {
        $product = createTestProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', validCustomerData());

        $reservation = Reservation::first();

        $this->get("/confirmation/{$reservation->confirmation_number}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('storeInfo.name', 'Palm Coast Vape and Glassware')
                ->has('storeInfo.address')
                ->has('storeInfo.phone')
            );
    });

});

describe('Multiple Products Checkout', function () {

    it('handles multiple products in cart', function () {
        $product1 = createTestProduct(['name' => 'Product 1', 'price' => 10.00, 'stock' => 50]);
        $product2 = createTestProduct(['name' => 'Product 2', 'price' => 20.00, 'stock' => 50]);
        $product3 = createTestProduct(['name' => 'Product 3', 'price' => 30.00, 'stock' => 50]);

        session(['cart' => [
            $product1->id => 2,
            $product2->id => 1,
            $product3->id => 3,
        ]]);

        $this->post('/checkout', validCustomerData());

        $reservation = Reservation::first();
        expect($reservation->items)->toHaveCount(3);

        expect($product1->fresh()->stock)->toBe(48);
        expect($product2->fresh()->stock)->toBe(49);
        expect($product3->fresh()->stock)->toBe(47);
    });

});
