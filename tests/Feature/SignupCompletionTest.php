<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

class SignupCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_signup_sends_verification_and_preserves_destination(): void
    {
        Notification::fake();
        $this->post('/customer/register?redirect=checkout', [
            'name' => 'New Customer', 'email' => 'new@example.com', 'phone' => '09123456789',
            'password' => 'StrongPass123!', 'password_confirmation' => 'StrongPass123!',
        ])->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertRedirect(route('customer.checkout'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_incomplete_users_cannot_bypass_setup(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'customer']);
        $this->actingAs($user)->get(route('customer.checkout'))->assertRedirect(route('verification.notice'));
        $this->postJson(route('customer.order.store'), [])->assertForbidden()->assertJsonPath('redirect', route('verification.notice'));
        $this->get(route('verification.notice'))->assertOk()->assertSee('Verify your email');
    }

    public function test_verification_rejects_expired_and_wrong_account_links(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        $params = ['id' => $user->id, 'hash' => sha1($user->email)];
        $this->get(URL::temporarySignedRoute('verification.verify', now()->subMinute(), $params))->assertForbidden();
        $params['id'] = $user->id + 1;
        $this->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), $params))->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_resend_is_throttled(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->post(route('verification.send'))->assertRedirect();
        $this->post(route('verification.send'))->assertStatus(429);
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_google_requires_password_once_and_accepts_verified_email(): void
    {
        $google = (new GoogleUser)->map(['id' => 'google-123', 'name' => 'Google Customer', 'email' => 'google@example.com']);
        $google->setRaw(['email_verified' => true]);
        Socialite::shouldReceive('driver->user')->once()->andReturn($google);
        $this->get(route('auth.google.callback'))->assertRedirect(route('signup.password'));
        $user = User::where('google_id', 'google-123')->firstOrFail();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->get(route('customer.checkout'))->assertRedirect(route('signup.password'));
        $this->get(route('signup.password'))->assertOk()->assertSee('Create your GasGo password');
        $this->post(route('signup.password.store'), ['password' => 'short', 'password_confirmation' => 'different'])->assertSessionHasErrors('password');
        $this->post(route('signup.password.store'), ['password' => 'StrongPass123!', 'password_confirmation' => 'StrongPass123!'])->assertRedirect(route('customer.checkout'));
        $this->assertFalse($user->fresh()->requiresPasswordSetup());
        $this->assertTrue(password_verify('StrongPass123!', $user->fresh()->password));
        $this->post(route('signup.password.store'), ['password' => 'AnotherPass123!', 'password_confirmation' => 'AnotherPass123!'])->assertForbidden();
    }

    public function test_google_rejects_unverified_identity_and_duplicate_email(): void
    {
        $google = (new GoogleUser)->map(['id' => 'google-123', 'name' => 'Google Customer', 'email' => 'google@example.com']);
        $google->setRaw(['email_verified' => false]);
        Socialite::shouldReceive('driver->user')->twice()->andReturn($google);
        $this->get(route('auth.google.callback'))->assertRedirect(route('customer.login'));
        $this->assertGuest();
        $google->setRaw(['email_verified' => true]);
        User::factory()->create(['email' => 'google@example.com']);
        $this->get(route('auth.google.callback'))->assertRedirect(route('customer.login'));
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_legacy_google_password_login_preserves_existing_password(): void
    {
        $hash = bcrypt('ExistingPass123!');
        $user = User::factory()->create(['google_id' => 'legacy', 'password' => $hash]);
        $this->post(route('customer.authenticate'), ['email' => $user->email, 'password' => 'ExistingPass123!'])->assertRedirect();
        $this->assertFalse($user->fresh()->requiresPasswordSetup());
        $this->assertTrue(password_verify('ExistingPass123!', $user->fresh()->password));
    }

    public function test_email_delivery_failure_leaves_account_recoverable(): void
    {
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('Mail unavailable'));
        $this->post(route('customer.register'), [
            'name' => 'New Customer', 'email' => 'retry@example.com', 'phone' => '09123456789',
            'password' => 'StrongPass123!', 'password_confirmation' => 'StrongPass123!',
        ])->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'retry@example.com', 'email_verified_at' => null]);
        $this->get(route('verification.notice'))->assertOk()->assertSee('could not be sent');
    }

    public function test_profile_email_change_sends_verification_and_saves_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => $user->name, 'email' => 'changed@example.com', 'phone' => '09123456789',
            'password' => 'ChangedPass123!', 'password_confirmation' => 'ChangedPass123!',
        ])->assertRedirect();
        $user->refresh();
        $this->assertSame('changed@example.com', $user->email);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertTrue(password_verify('ChangedPass123!', $user->password));
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
