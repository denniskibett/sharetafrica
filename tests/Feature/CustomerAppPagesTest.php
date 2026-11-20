<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Identity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Phase 1 customer app screens in the Sharet UI: auth and onboarding look, Help, and the coming-soon previews. */
class CustomerAppPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function customer(array $extra = []): User
    {
        return User::factory()->create(array_merge(['onboarding_status' => 'active', 'intent' => 'individual', 'phone' => '+254712000009'], $extra));
    }

    public function test_auth_pages_use_the_sharet_ui_and_keep_their_fields(): void
    {
        $this->get('/login')->assertOk()->assertSee('class="sharet-app', false)
            ->assertSee('name="email"', false)->assertSee('name="password"', false)->assertSee('name="remember"', false)
            ->assertSee('action="'.route('login').'"', false)->assertSee(route('password.request'));

        $this->get('/register')->assertOk()->assertSee('class="sharet-app', false)
            ->assertSee('action="'.route('register').'"', false);
        foreach (['name', 'email', 'phone', 'password', 'password_confirmation'] as $field) {
            $this->get('/register')->assertSee('name="'.$field.'"', false);
        }

        $this->get('/forgot-password')->assertOk()->assertSee('action="'.route('password.email').'"', false);
        $this->get('/reset-password/some-token?email=a@example.com')->assertOk()
            ->assertSee('name="token" value="some-token"', false)->assertSee('value="a@example.com"', false);

        $this->actingAs($this->customer());
        $this->get('/confirm-password')->assertOk()->assertSee('action="'.route('password.confirm').'"', false);
    }

    public function test_sign_up_errors_show_under_the_fields(): void
    {
        $this->from('/register')->followingRedirects()->post('/register', [
            'name' => 'juma', 'email' => 'juma@example.com', 'phone' => '+254700000002', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertOk()->assertSee(Identity::MESSAGES['name.regex'])->assertSee('aria-invalid="true"', false);
        $this->assertGuest();
    }

    public function test_sign_in_still_works(): void
    {
        $user = $this->customer();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_onboarding_pages_render(): void
    {
        $this->actingAs($user = $this->customer(['onboarding_status' => 'waiting_list']));
        $this->get("/onboarding")->assertOk()->assertSee("You're on the list", false);

        $user->update(['onboarding_status' => 'invited']);
        $this->get('/onboarding/choose-lane')->assertOk()->assertSee('name="lane"', false)->assertSee('action="'.route('onboarding.store_lane').'"', false);

        foreach (['individual' => 'use_case', 'merchant' => 'business_name', 'business' => 'corridors[]', 'developer' => 'interest'] as $lane => $field) {
            $this->get("/onboarding/$lane")->assertOk()->assertSee('name="'.$field.'"', false)->assertSee('action="'.route("onboarding.$lane.store").'"', false);
        }
    }

    public function test_help_and_the_previews_render_for_a_customer(): void
    {
        $this->actingAs($this->customer());

        $this->get('/help')->assertOk()->assertSee('class="sharet-app', false)->assertSee('Common questions')
            ->assertSee('Report fraud')->assertSee('mailto:support@sharet.africa', false)->assertSee('KES 70,000');

        foreach (['/requests', '/card', '/abroad', '/abroad/swift'] as $url) {
            $this->get($url)->assertOk()->assertSee('class="sharet-app', false)
                ->assertSee("Tell me when it's ready", false)->assertSee('disabled', false)
                ->assertDontSee(route('pay.transfer'));
        }
        $this->get('/abroad')->assertSee('Tanzania')->assertSee('Next after the Kenya pilot');
        $this->get('/card')->assertSee('Cards need bank grade');

        // The account menu links Help and the previews
        $this->get('/dashboard')->assertOk()->assertSee(route('help'))->assertSee(route('card'));
    }

    public function test_the_new_pages_need_sign_in(): void
    {
        foreach (['/help', '/requests', '/card', '/abroad', '/abroad/swift'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
