<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_product_to_session_cart()
    {
        $product = Product::factory()->create(['price' => 100.00, 'stock' => 50]);

        $response = $this->post(route('customer.cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(302);
        $this->assertSame(2, session('cart')[$product->id] ?? 0);
    }

    public function test_guest_checkout_redirects_to_login()
    {
        $product = Product::factory()->create(['price' => 100.00, 'stock' => 50]);
        session(['cart' => [$product->id => 2]]);

        $response = $this->get(route('customer.checkout'));

        $response->assertStatus(302);
        $response->assertRedirect(route('customer.login'));
    }

    public function test_guest_place_order_redirects_to_login_with_intended()
    {
        $response = $this->post(route('customer.order.store'), [
            'delivery_address' => '123 Main St',
            'contact_number' => '09121234567',
            'payment_method' => 'cash',
            'latitude' => '16.0433',
            'longitude' => '120.3654',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_session_cart_merges_into_database_cart_on_login()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'customer',
        ]);

        $product1 = Product::factory()->create(['price' => 100.00, 'stock' => 50]);
        $product2 = Product::factory()->create(['price' => 200.00, 'stock' => 50]);

        $sessionCart = [
            (string) $product1->id => 2,
            (string) $product2->id => 1,
        ];

        $response = $this->withSession(['cart' => $sessionCart])
            ->post(route('customer.authenticate'), [
                'email' => 'testuser@example.com',
                'password' => 'Password123!',
            ]);

        $this->assertAuthenticatedAs($user);
        $this->assertFalse(session()->has('cart'));

        $cartItems = Cart::where('user_id', $user->id)->get();
        $this->assertCount(2, $cartItems);
        $this->assertSame(2, $cartItems->firstWhere('product_id', $product1->id)->quantity);
        $this->assertSame(1, $cartItems->firstWhere('product_id', $product2->id)->quantity);
    }

    public function test_session_cart_merges_incrementally_with_existing_database_cart()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'customer',
        ]);

        $product1 = Product::factory()->create(['price' => 100.00, 'stock' => 50]);
        $product2 = Product::factory()->create(['price' => 200.00, 'stock' => 50]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 1,
        ]);

        $sessionCart = [
            (string) $product1->id => 2,
            (string) $product2->id => 3,
        ];

        $response = $this->withSession(['cart' => $sessionCart])
            ->post(route('customer.authenticate'), [
                'email' => 'testuser@example.com',
                'password' => 'Password123!',
            ]);

        $this->assertAuthenticatedAs($user);

        $cartItems = Cart::where('user_id', $user->id)->get();
        $this->assertCount(2, $cartItems);
        $this->assertSame(3, $cartItems->firstWhere('product_id', $product1->id)->quantity);
        $this->assertSame(3, $cartItems->firstWhere('product_id', $product2->id)->quantity);
    }

    public function test_guest_cart_persists_after_login_and_redirects_to_checkout()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'customer',
        ]);

        $product = Product::factory()->create(['price' => 100.00, 'stock' => 50]);

        $sessionCart = [
            (string) $product->id => 2,
        ];

        $response = $this->withSession(['cart' => $sessionCart])
            ->post(route('customer.authenticate'), [
                'email' => 'testuser@example.com',
                'password' => 'Password123!',
            ]);

        $response->assertStatus(302);
        $this->assertAuthenticatedAs($user);

        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->firstOrFail();
        $this->assertSame(2, $cartItem->quantity);
    }

    public function test_registered_new_user_session_cart_merges_to_database()
    {
        $product = Product::factory()->create(['price' => 100.00, 'stock' => 50]);

        $sessionCart = [
            (string) $product->id => 1,
        ];

        $response = $this->withSession(['cart' => $sessionCart])
            ->post(route('customer.register'), [
                'name' => 'New Customer',
                'email' => 'newcustomer@test.com',
                'phone' => '09121234567',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $user = User::where('email', 'newcustomer@test.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        $this->assertFalse(session()->has('cart'));

        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->firstOrFail();
        $this->assertSame(1, $cartItem->quantity);
    }

    public function test_empty_session_cart_does_not_cause_issues_on_login()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'customer',
        ]);

        $response = $this->withSession(['cart' => []])
            ->post(route('customer.authenticate'), [
                'email' => 'testuser@example.com',
                'password' => 'Password123!',
            ]);

        $this->assertAuthenticatedAs($user);
        $this->assertCount(0, Cart::where('user_id', $user->id)->get());
    }

    public function test_invalid_product_ids_in_session_cart_are_skipped_on_merge()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'customer',
        ]);

        $product = Product::factory()->create(['price' => 100.00, 'stock' => 50]);

        $sessionCart = [
            (string) $product->id => 2,
            '99999' => 1,
        ];

        $response = $this->withSession(['cart' => $sessionCart])
            ->post(route('customer.authenticate'), [
                'email' => 'testuser@example.com',
                'password' => 'Password123!',
            ]);

        $this->assertAuthenticatedAs($user);

        $cartItems = Cart::where('user_id', $user->id)->get();
        $this->assertCount(1, $cartItems);
        $this->assertSame(2, $cartItems->first()->quantity);
    }
}
