<?php

namespace Tests\Feature;

use App\Models\RampOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** The header search: menu pages and records, only what the person may open. */
class HeaderSearchTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role, array $permissions = []): User
    {
        $user = User::factory()->create(['onboarding_status' => 'active', 'intent' => 'individual']);
        $user->assignRole(Role::findOrCreate($role, 'web'));
        foreach ($permissions as $p) {
            $user->givePermissionTo(Permission::findOrCreate($p, 'web'));
        }

        return $user;
    }

    private function order(User $owner, string $name): RampOrder
    {
        return RampOrder::withoutEvents(fn () => RampOrder::create([
            'uuid' => (string) Str::uuid(), 'user_id' => $owner->id, 'rail' => 'simulator', 'destination_type' => 'sharet',
            'destination' => ['name' => $name, 'handle' => 'akinyi', 'ref' => 'SHUK7Q2M4D'], 'payer_phone' => '254712000111', 'funding' => 'wallet',
            'amount' => 50000, 'fee' => 0, 'excise' => 0, 'rounding' => 0, 'total' => 50000, 'currency' => 'KES', 'status' => 'paid', 'quote_expires_at' => now(),
        ]));
    }

    private function search(string $q): array
    {
        return $this->getJson('/search?q='.urlencode($q))->assertOk()->assertHeader('Cache-Control', 'no-store, private')->json('results');
    }

    public function test_admin_finds_pages_and_people(): void
    {
        $this->actingAs($this->staff('super_admin'));
        $target = User::factory()->create(['name' => 'Wanjiru Kamau', 'email' => 'wanjiru@example.test']);

        $pages = $this->search('user man');
        $this->assertSame(['Go to', 'User management', route('users.index')], [$pages[0]['group'], $pages[0]['label'], $pages[0]['url']]);

        $people = collect($this->search('wanjiru'))->where('group', 'People')->values();
        $this->assertCount(1, $people);
        $this->assertSame(route('users.show', $target), $people[0]['url']);
        $this->get($people[0]['url'])->assertOk();   // the link opens

        $this->get('/dashboard')->assertOk()->assertSee('Search or jump to', false);   // the header renders
    }

    public function test_operations_staff_do_not_see_admin_pages_or_people(): void
    {
        $this->actingAs($this->staff('operations', ['admin.reconciliation']));
        User::factory()->create(['name' => 'Wanjiru Kamau']);

        $this->assertEmpty(collect($this->search('user'))->where('label', 'User management'));
        $this->assertEmpty(collect($this->search('wanjiru'))->where('group', 'People'));
        $this->assertNotEmpty(collect($this->search('ledger'))->where('label', 'Ledger'));
        $this->get('/users')->assertForbidden();   // and indeed cannot open it
    }

    public function test_customers_find_only_their_own_payments(): void
    {
        [$mine, $other] = [User::factory()->create(), User::factory()->create()];
        $order = $this->order($mine, 'Akinyi Wekesa');

        $this->actingAs($mine);
        $hits = collect($this->search('akinyi'))->where('group', 'Your payments')->values();
        $this->assertCount(1, $hits);
        $this->assertSame(route('pay.show', $order->uuid), $hits[0]['url']);
        $this->assertCount(1, collect($this->search('SHUK7Q2M4D'))->where('group', 'Your payments'));

        $this->actingAs($other);
        $results = collect($this->search('akinyi'));
        $this->assertEmpty($results->where('group', 'Your payments'));
        $this->assertEmpty($results->where('group', 'People'));
        $this->get(route('pay.show', $order->uuid))->assertNotFound();
    }

    public function test_short_queries_return_pages_only_and_guests_are_redirected(): void
    {
        $this->actingAs($this->staff('super_admin'));
        $this->assertSame(['Go to'], collect($this->search(''))->pluck('group')->unique()->values()->all());

        auth()->logout();
        $this->get('/search?q=wan')->assertRedirect();
    }
}
