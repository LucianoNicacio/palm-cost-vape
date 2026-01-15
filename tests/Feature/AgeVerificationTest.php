// tests/Feature/AgeVerificationTest.php

<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects to age gate if not verified', function () {
    $this->get('/shop')
        ->assertRedirect('/age-verification');
});

it('allows access after age verification', function () {
    $this->post('/age-verification', ['confirmed' => true])
        ->assertRedirect('/');

    $this->get('/shop')
        ->assertOk();
});

it('stores age verification in session', function () {
    $this->post('/age-verification', ['confirmed' => true]);

    expect(session('age_verified'))->toBeTrue();
});

it('records verification in database', function () {
    $this->post('/age-verification', ['confirmed' => true]);

    $this->assertDatabaseHas('age_verifications', [
        'session_id' => session()->getId(),
    ]);
});

it('rejects without confirmation', function () {
    $this->post('/age-verification', ['confirmed' => false])
        ->assertSessionHasErrors('confirmed');
});

it('redirects verified users away from age gate', function () {
    session(['age_verified' => true]);

    $this->get('/age-verification')
        ->assertRedirect('/');
});