<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user); // accounts are soft-deleted so records stay for audit
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_security_tab_sets_then_changes_the_sharet_pin(): void
    {
        $user = User::factory()->create(['onboarding_status' => 'active']);
        $this->actingAs($user)->get('/profile/edit')->assertOk()->assertSee('Sharet PIN');
        $this->actingAs($user)->getJson('/profile/data')->assertJsonPath('user.pin_set', false);

        $this->actingAs($user)->postJson('/pay/pin', ['pin' => '5937', 'pin_confirmation' => '5937'])->assertOk();
        $this->actingAs($user)->getJson('/profile/data')->assertJsonPath('user.pin_set', true);
        $this->actingAs($user)->postJson('/pay/pin', ['pin' => '8642', 'pin_confirmation' => '8642', 'current' => '0000'])->assertStatus(422);
        $this->actingAs($user)->postJson('/pay/pin', ['pin' => '8642', 'pin_confirmation' => '8642', 'current' => '5937'])->assertOk();
    }
}
