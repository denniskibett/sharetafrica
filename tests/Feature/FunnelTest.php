<?php

namespace Tests\Feature;

use App\Models\RampOrder;
use App\Models\User;
use App\Support\Funnel;
use App\Support\TransactionPin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FunnelTest extends TestCase
{
    use RefreshDatabase;

    private function steps(User $user): array
    {
        return DB::table('funnel_events')->where('user_id', $user->id)->orderBy('id')->pluck('step')->all();
    }

    private function order(User $owner, string $type, string $status = 'paid'): RampOrder
    {
        return RampOrder::create([
            'uuid' => (string) Str::uuid(), 'user_id' => $owner->id, 'rail' => 'simulator', 'destination_type' => $type,
            'destination' => ['name' => 'Akinyi', 'phone' => '254712000222', 'handle' => 'akinyi', 'bank' => 'Equity', 'bank_code' => '68', 'account' => '0123456789'], 'payer_phone' => '254712000111', 'funding' => 'wallet',
            'amount' => 50000, 'fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => 50000, 'currency' => 'KES', 'status' => $status, 'quote_expires_at' => now(),
        ]);
    }

    public function test_each_step_is_recorded_once_as_the_account_moves_along(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->assertSame(['signed_up'], $this->steps($user));

        $user->forceFill(['email_verified_at' => now()])->save();
        TransactionPin::set($user, '4729');
        $user->forceFill(['kyc' => encrypt(['id_number' => '12345678'])])->save();
        $user->forceFill(['kyc_level' => 'basic'])->save();
        $user->forceFill(['kyc_level' => 'standard'])->save(); // already verified: not counted twice

        $this->assertSame(['signed_up', 'email_verified', 'pin_set', 'id_submitted', 'verified'], $this->steps($user));
    }

    public function test_top_ups_count_as_money_in_and_other_payments_as_money_out_only_when_paid(): void
    {
        $user = User::factory()->create();
        $pending = $this->order($user, 'phone', 'collecting');
        $this->assertNotContains('first_money_out', $this->steps($user));

        $pending->update(['status' => 'paid']);
        $this->order($user, 'wallet');
        $this->order($user, 'sharet'); // a second payment out: still one row

        $this->assertSame(1, DB::table('funnel_events')->where('user_id', $user->id)->where('step', 'first_money_out')->count());
        $this->assertContains('first_money_in', $this->steps($user));
    }

    public function test_the_website_asks_how_the_first_payment_was_once(): void
    {
        $user = User::factory()->create(['onboarding_status' => 'active', 'intent' => 'individual', 'kyc_level' => 'basic']);
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertDontSee('How was that?');

        $this->order($user, 'phone');
        $this->assertTrue(Funnel::askFeedback($user));
        $this->get('/dashboard')->assertOk()->assertSee('Your first payment went through. How was that?');

        $this->postJson('/feedback', ['score' => 9])->assertUnprocessable();
        $this->postJson('/feedback', ['score' => 4, 'comment' => '  Took a while to find Withdraw  '])->assertOk();
        $this->assertDatabaseHas('feedback', ['user_id' => $user->id, 'score' => 4, 'comment' => 'Took a while to find Withdraw', 'channel' => 'web']);
        $this->assertFalse(Funnel::askFeedback($user));
        $this->get('/dashboard')->assertDontSee('How was that?');
    }

    public function test_the_app_signs_in_tells_the_app_to_ask_and_takes_the_answer(): void
    {
        $this->seed(\Database\Seeders\LedgerAccountSeeder::class);
        $user = User::factory()->create(['phone' => '0712345678', 'password' => bcrypt('Secret-pass-1')]);
        $token = $this->postJson('/api/mobile/v1/auth/login', ['identifier' => '0712345678', 'password' => 'Secret-pass-1', 'device_id' => 'sh-funnel', 'platform' => 'android'])->assertCreated()->json('token');
        $this->assertDatabaseHas('funnel_events', ['user_id' => $user->id, 'step' => 'app_signed_in', 'channel' => 'app']);

        $app = $this->withHeaders(['Authorization' => "Bearer $token", 'X-Device-Id' => 'sh-funnel']);
        $app->getJson('/api/mobile/v1/me')->assertJsonPath('ask_feedback', false);
        $this->order($user, 'bank');
        $app->getJson('/api/mobile/v1/me')->assertJsonPath('ask_feedback', true);
        $app->postJson('/api/mobile/v1/feedback', ['score' => null])->assertOk(); // "Not now"
        $app->getJson('/api/mobile/v1/me')->assertJsonPath('ask_feedback', false);
        $this->assertDatabaseHas('feedback', ['user_id' => $user->id, 'score' => null, 'channel' => 'app']);
    }

    public function test_admin_sees_the_funnel_without_staff_and_the_ratings(): void
    {
        $a = User::factory()->create(['email_verified_at' => null]);
        $b = User::factory()->create(['email_verified_at' => null]);
        $b->forceFill(['email_verified_at' => now()])->save();
        $this->order($b, 'phone');
        Funnel::feedback($b, 2, 'The ID photo kept failing', 'web');

        $admin = User::factory()->create(['onboarding_status' => 'active', 'intent' => 'individual']);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $admin->givePermissionTo(Permission::findOrCreate('analytics.view', 'web'));

        $report = Funnel::report(30);
        $this->assertSame(2, $report['signups']); // the admin is staff: left out
        $this->assertSame(['signed_up' => 2, 'email_verified' => 1, 'first_money_out' => 1], collect($report['steps'])->whereIn('step', ['signed_up', 'email_verified', 'first_money_out'])->pluck('people', 'step')->all());
        $this->assertSame(50, collect($report['steps'])->firstWhere('step', 'email_verified')['of_signups']);

        $this->actingAs($admin)->get('/admin/analytics')->assertOk()
            ->assertSee('Sign-up to first payment')->assertSee('How was the first payment?')->assertSee('The ID photo kept failing');
    }

    public function test_backfill_fills_older_accounts_once_and_keeps_live_rows(): void
    {
        $user = User::factory()->create();
        $this->order($user, 'wallet');
        DB::table('funnel_events')->delete();
        DB::table('users')->where('id', $user->id)->update(['kyc_level' => 'basic']);

        $this->artisan('funnel:backfill')->assertSuccessful();
        $this->artisan('funnel:backfill')->expectsOutputToContain('0 steps added')->assertSuccessful();
        $this->assertEqualsCanonicalizing(['signed_up', 'email_verified', 'verified', 'first_money_in'], $this->steps($user));
        $this->assertSame(0, DB::table('funnel_events')->where('backfilled', false)->count());
    }
}
