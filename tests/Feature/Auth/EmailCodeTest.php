<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\EmailCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailCodeTest extends TestCase
{
    use RefreshDatabase;

    private function register(): User
    {
        $this->post('/register', ['name' => 'Amina Wanjiru', 'email' => 'amina@example.com', 'phone' => '0712345678',
            'password' => 'Password-123', 'password_confirmation' => 'Password-123']);

        return User::where('email', 'amina@example.com')->firstOrFail();
    }

    private function codeFor(User $user, string $code = '123456'): void
    {
        Cache::put("email-code:{$user->id}", ['hash' => hash_hmac('sha256', $code, config('app.key')), 'tries' => 0], now()->addMinutes(15));
    }

    public function test_off_by_default_sign_up_goes_straight_in(): void
    {
        $this->assertFalse(EmailCode::enabled());
        $user = $this->register();
        $this->assertNull($user->email_code_required_at);
        $this->get('/dashboard')->assertRedirect(route('onboarding.welcome'));
    }

    public function test_when_on_new_accounts_must_enter_the_code(): void
    {
        Mail::fake();
        config(['security.email_code_on_signup' => true]);
        $user = $this->register();
        $this->assertNotNull($user->email_code_required_at);

        $this->get('/dashboard')->assertRedirect(route('verification.code'));
        $this->getJson('/dashboard/data')->assertForbidden();
        $this->get(route('verification.code'))->assertOk()->assertSee('amina@example.com');

        $this->codeFor($user);
        $this->post(route('verification.code.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post(route('verification.code.verify'), ['code' => '123 456'])->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->get(route('verification.code'))->assertRedirect(route('dashboard'));
    }

    public function test_five_wrong_codes_need_a_new_one(): void
    {
        $user = User::factory()->create(['email_verified_at' => null, 'email_code_required_at' => now()]);
        $this->codeFor($user);
        foreach (range(1, 5) as $_) {
            EmailCode::check($user, '999999');
        }
        $this->assertSame('Too many wrong codes. Send a new one.', EmailCode::check($user, '123456'));
    }

    public function test_existing_users_are_never_asked(): void
    {
        config(['security.email_code_on_signup' => true]);
        $old = User::factory()->create(['email_verified_at' => null]);
        $this->assertFalse(EmailCode::pending($old));
    }
}
