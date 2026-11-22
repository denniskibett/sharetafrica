<?php

namespace Tests\Feature\Business;

use App\Models\BusinessBatch;
use App\Models\LedgerJournal;
use App\Models\RampOrder;
use App\Ramp\RampOrders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Business in the app: company wallet, bulk payouts with a maker and a checker, team and roles, statements. */
class BusinessAppTest extends TestCase
{
    use BusinessTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootBusiness();
    }

    private function lines(array $phones, string $amount = '1000'): array
    {
        return array_map(fn ($p) => ['name' => 'Staff '.$p, 'to' => ['type' => 'mpesa', 'phone' => $p], 'amount' => $amount], $phones);
    }

    public function test_someone_on_no_team_sees_no_business_and_the_registering_person_is_owner(): void
    {
        $nobody = $this->person('Juma Otieno');
        $this->getJson('/api/mobile/v1/business', $this->phone($nobody))->assertOk()->assertJsonPath('business', null);
        $this->getJson('/api/mobile/v1/business/batches', $this->phone($nobody))->assertForbidden()->assertJsonPath('error', 'not_a_member');

        $owner = $this->person('Peter Kamau');
        $c = $this->business($owner, 'Kamau Logistics Ltd', '50000');
        $this->getJson('/api/mobile/v1/business', $this->phone($owner))->assertOk()
            ->assertJsonPath('business.name', 'Kamau Logistics Ltd')->assertJsonPath('business.role', 'owner')
            ->assertJsonPath('business.balance', '50,000.00')->assertJsonPath('business.verified', true)
            ->assertJsonPath('business.sharet_id', $c->walletUser()->sharet_id)->assertJsonPath('business.can.team', true);
    }

    public function test_maker_prepares_checker_approves_with_pin_and_each_line_is_its_own_payout(): void
    {
        $owner = $this->person('Peter Kamau');
        $c = $this->business($owner, 'Kamau Logistics Ltd', '100000');
        $grace = $this->person('Grace Wambui');
        $daniel = $this->person('Daniel Otieno');
        $this->member($c, $grace, 'maker');
        $this->member($c, $daniel, 'checker', 100_000_000);
        $wallet = $c->walletUser();
        $orders = app(RampOrders::class);

        // Prepare: nothing moves; fees from the business tariff; failing number ends in 0000 (simulator)
        $made = $this->postJson('/api/mobile/v1/business/batches', ['name' => 'Salaries November', 'lines' => $this->lines(['0712345678', '0722118904', '0733220000'])], $this->phone($grace))
            ->assertCreated()->assertJsonPath('batch.status', 'awaiting_approval')->assertJsonPath('batch.people', 3)
            ->assertJsonPath('batch.amount', '3,000.00')->assertJsonPath('batch.new_payees', 3)->assertJsonPath('batch.can_approve', false);
        $id = $made->json('batch.id');
        $this->assertNotSame('0.00', $made->json('batch.fees'));
        $this->assertSame(10_000_000, $orders->walletBalance($wallet));
        $this->assertSame(0, RampOrder::where('user_id', $wallet->id)->count());

        $gh = $this->phone($grace);
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($gh, 'approve-grace-1'))->assertForbidden()->assertJsonPath('error', 'not_allowed');

        // A checker can't pay new people inside the cool-off; the owner can
        $dh = $this->phone($daniel);
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $dh)->assertStatus(428);
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($dh, 'approve-dan-1'))->assertForbidden()->assertJsonPath('error', 'new_payee_cool_off');
        $this->travel(25)->hours();
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '0000'], $this->key($dh, 'approve-dan-2'))->assertStatus(422)->assertJsonValidationErrors('pin');
        $this->assertSame('awaiting_approval', BusinessBatch::where('public_id', $id)->value('status'));

        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($dh, 'approve-dan-2'))->assertOk()->assertJsonPath('batch.approvals.0.name', 'Daniel Otieno');
        $batch = BusinessBatch::where('public_id', $id)->first();
        $this->assertSame(3, RampOrder::where('user_id', $wallet->id)->count());
        foreach (RampOrder::where('user_id', $wallet->id)->get() as $o) {
            $this->settle($o);
        }

        $show = $this->getJson("/api/mobile/v1/business/batches/$id", $dh)->assertOk()
            ->assertJsonPath('batch.status', 'partially_failed')->assertJsonPath('batch.paid.count', 2)->assertJsonPath('batch.returned.count', 1);
        $this->assertSame('returned', $show->json('batch.lines.2.status'));
        $this->assertNotNull($show->json('batch.lines.0.reference'));

        // The failed line came back in full, fee included: only the two paid lines and their fees left the wallet
        $paidTotal = (int) RampOrder::where('user_id', $wallet->id)->where('status', 'paid')->sum('total');
        $this->assertSame(10_000_000 - $paidTotal, $orders->walletBalance($wallet));
        $this->assertGreaterThan(0, LedgerJournal::count());

        $csv = $this->get("/api/mobile/v1/business/batches/$id/report", $dh)->assertOk();
        $this->assertStringContainsString('sharet_reference', $csv->getContent());
        $this->assertSame(4, substr_count(trim($csv->getContent()), "\n") + 1);
        $this->assertSame($daniel->name, $batch->approvals[0]['name']);
    }

    public function test_the_owner_approves_new_payees_at_once_and_a_retry_never_pays_twice(): void
    {
        $owner = $this->person('Peter Kamau');
        $c = $this->business($owner, 'Kamau Logistics Ltd', '20000');
        $grace = $this->person('Grace Wambui');
        $this->member($c, $grace, 'maker');
        $id = $this->postJson('/api/mobile/v1/business/batches', ['name' => 'Suppliers week 47', 'lines' => $this->lines(['0712345678'], '5000')], $this->phone($grace))->assertCreated()->json('batch.id');

        $oh = $this->phone($owner);
        $first = $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($oh, 'approve-owner-1'))->assertOk();
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($oh, 'approve-owner-1'))->assertOk()->assertHeader('Idempotent-Replay', 'true');
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($oh, 'approve-owner-2'))->assertStatus(409);
        $this->assertSame(1, RampOrder::where('user_id', $c->walletUser()->id)->count());
        $this->assertContains($first->json('batch.status'), ['processing', 'completed']);
    }

    public function test_the_maker_never_approves_their_own_and_limits_and_two_checkers_apply(): void
    {
        config(['business.two_checkers_above' => 500_000, 'business.new_payee_hours' => 0]);   // above KES 5,000: two checkers
        $owner = $this->person('Peter Kamau');
        $c = $this->business($owner, 'Kamau Logistics Ltd', '50000');
        $small = $this->person('Small Limit');
        $a = $this->person('Checker A');
        $b = $this->person('Checker B');
        $this->member($c, $small, 'checker', 100_000);   // KES 1,000
        $this->member($c, $a, 'checker');
        $this->member($c, $b, 'checker');

        $oh = $this->phone($owner);
        $id = $this->postJson('/api/mobile/v1/business/batches', ['name' => 'Big', 'lines' => $this->lines(['0712345678', '0722118904'], '4000')], $oh)
            ->assertCreated()->assertJsonPath('batch.approvals_needed', 2)->json('batch.id');
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($oh, 'own-approve-1'))->assertForbidden()->assertJsonPath('error', 'maker_cannot_approve');
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($this->phone($small), 'small-key-1'))->assertForbidden()->assertJsonPath('error', 'over_your_limit');

        $ah = $this->phone($a);
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($ah, 'a-approve-1'))->assertOk()
            ->assertJsonPath('batch.status', 'awaiting_approval')->assertJsonPath('message', 'Approved. It needs one more checker.');
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($ah, 'a-approve-2'))->assertStatus(409)->assertJsonPath('error', 'already_approved');
        $this->assertSame(0, RampOrder::where('user_id', $c->walletUser()->id)->count());
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($this->phone($b), 'b-approve-1'))->assertOk()->assertJsonPath('batch.approvals_needed', 2);
        $this->assertSame(2, RampOrder::where('user_id', $c->walletUser()->id)->count());
    }

    public function test_csv_lists_flag_duplicates_and_bad_lines_stop_the_whole_batch(): void
    {
        $owner = $this->person('Peter Kamau');
        $c = $this->business($owner, 'Kamau Logistics Ltd', '50000');
        $oh = $this->phone($owner);
        $peer = $this->person('Akinyi Wekesa');
        $handle = \App\Support\Handle::for($peer);

        $this->postJson('/api/mobile/v1/business/batches', ['name' => 'Bad', 'csv' => "name,number,amount\nA,0712345678,100\nB,12,100"], $oh)
            ->assertStatus(422)->assertJsonValidationErrors('lines.1');
        $this->assertSame(0, BusinessBatch::count());

        $res = $this->postJson('/api/mobile/v1/business/batches', ['name' => 'Mixed', 'csv' => "name,number,amount\nA,0712345678,100\nA again,+254712345678,100\nAkinyi,@$handle,250\nKPLC,888880#12345,500\nShop,till 123456,50"], $oh)
            ->assertCreated()->assertJsonPath('batch.people', 5)->assertJsonPath('batch.flagged', 2);
        $this->assertSame(['duplicate_in_batch', 'new_payee'], $res->json('batch.lines.0.flags'));
        $this->assertSame('sharet', $res->json('batch.lines.2.type'));
        $this->assertSame('0.00', $res->json('batch.lines.2.fee'));   // Sharet to Sharet is free
        $this->assertSame('paybill', $res->json('batch.lines.3.type'));

        // Unverified businesses can't prepare payouts
        $other = $this->person('New Owner');
        $this->business($other, 'Unverified Ltd', '0', verified: false);
        $this->postJson('/api/mobile/v1/business/batches', ['name' => 'X', 'lines' => $this->lines(['0712345678'])], $this->phone($other))
            ->assertForbidden()->assertJsonPath('error', 'business_not_verified');
    }

    public function test_reject_needs_a_reason_and_the_maker_can_cancel_and_repeat(): void
    {
        $owner = $this->person('Peter Kamau');
        $c = $this->business($owner, 'Kamau Logistics Ltd', '50000');
        $grace = $this->person('Grace Wambui');
        $this->member($c, $grace, 'maker');
        $gh = $this->phone($grace);
        $oh = $this->phone($owner);

        $id = $this->postJson('/api/mobile/v1/business/batches', ['name' => 'Salaries October', 'lines' => $this->lines(['0712345678'])], $gh)->json('batch.id');
        $this->postJson("/api/mobile/v1/business/batches/$id/reject", [], $oh)->assertStatus(422);
        $this->postJson("/api/mobile/v1/business/batches/$id/reject", ['reason' => 'Wrong amounts'], $oh)->assertOk()
            ->assertJsonPath('batch.status', 'rejected')->assertJsonPath('batch.reject_reason', 'Wrong amounts')->assertJsonPath('batch.rejected_by', 'Peter Kamau');

        $again = $this->postJson("/api/mobile/v1/business/batches/$id/repeat", ['name' => 'Salaries November'], $gh)->assertCreated()->assertJsonPath('batch.people', 1)->json('batch.id');
        $this->postJson("/api/mobile/v1/business/batches/$again/cancel", [], $gh)->assertOk()->assertJsonPath('batch.status', 'cancelled');
        $this->getJson('/api/mobile/v1/business/batches?status=cancelled,rejected', $gh)->assertOk()->assertJsonCount(2, 'data');
        $this->assertSame(0, RampOrder::where('user_id', $c->walletUser()->id)->count());
    }

    public function test_the_owner_runs_the_team_with_their_pin_and_viewers_only_see_statements(): void
    {
        $owner = $this->person('Peter Kamau');
        $c = $this->business($owner, 'Kamau Logistics Ltd', '30000');
        $faith = $this->person('Faith Njeri');
        $daniel = $this->person('Daniel Otieno');
        $oh = $this->phone($owner);

        $this->postJson('/api/mobile/v1/business/team', ['who' => $faith->phone, 'role' => 'viewer', 'pin' => '1357'], $oh)->assertStatus(422)->assertJsonValidationErrors('pin');
        $this->postJson('/api/mobile/v1/business/team', ['who' => $faith->phone, 'role' => 'viewer', 'pin' => '4826'], $oh)->assertCreated()->assertJsonPath('member.role', 'viewer');
        $this->postJson('/api/mobile/v1/business/team', ['who' => '@'.\App\Support\Handle::for($daniel), 'role' => 'checker', 'approval_limit' => '1000000', 'pin' => '4826'], $oh)
            ->assertCreated()->assertJsonPath('member.approval_limit', '1,000,000.00');
        $this->postJson('/api/mobile/v1/business/team', ['who' => $faith->email, 'role' => 'maker', 'pin' => '4826'], $oh)->assertStatus(422)->assertJsonValidationErrors('who');

        $team = $this->getJson('/api/mobile/v1/business/team', $oh)->assertOk()->assertJsonCount(3, 'team')->json('team');
        $this->assertSame(['owner', 'checker', 'viewer'], array_column($team, 'role'));
        $ownerRow = collect($team)->firstWhere('role', 'owner');
        $this->deleteJson('/api/mobile/v1/business/team/'.$ownerRow['id'], ['pin' => '4826'], $oh)->assertStatus(422);

        $fh = $this->phone($faith);
        $this->postJson('/api/mobile/v1/business/team', ['who' => $daniel->phone, 'role' => 'owner', 'pin' => '4826'], $fh)->assertForbidden();
        $this->postJson('/api/mobile/v1/business/batches', ['name' => 'X', 'lines' => $this->lines(['0712345678'])], $fh)->assertForbidden();
        $statement = $this->getJson('/api/mobile/v1/business/statement', $fh)->assertOk()->assertJsonPath('balance', '30,000.00')
            ->assertJsonPath('statement.in', '30,000.00')->json('statement.lines');
        $this->assertSame('in', $statement[0]['direction']);
        $this->assertNotEmpty($statement[0]['sharet_reference']);
        $csv = $this->get('/api/mobile/v1/business/statement?format=csv', $fh)->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('network_reference', $csv->getContent());

        $danielRow = collect($team)->firstWhere('role', 'checker');
        $this->patchJson('/api/mobile/v1/business/team/'.$danielRow['id'], ['role' => 'maker', 'pin' => '4826'], $oh)->assertOk()->assertJsonPath('member.role', 'maker');
        $this->deleteJson('/api/mobile/v1/business/team/'.$danielRow['id'], ['pin' => '4826'], $oh)->assertOk();
        $this->getJson('/api/mobile/v1/business', $this->phone($daniel))->assertOk()->assertJsonPath('business', null);
    }

    public function test_approving_is_stopped_by_the_ledger_freeze(): void
    {
        config(['business.new_payee_hours' => 0]);
        $owner = $this->person('Peter Kamau');
        $c = $this->business($owner, 'Kamau Logistics Ltd', '30000');
        $grace = $this->person('Grace Wambui');
        $this->member($c, $grace, 'maker');
        $id = $this->postJson('/api/mobile/v1/business/batches', ['name' => 'X', 'lines' => $this->lines(['0712345678'])], $this->phone($grace))->json('batch.id');
        \App\Security\Lockdown::set('ledger', true, 'test');
        $this->postJson("/api/mobile/v1/business/batches/$id/approve", ['pin' => '4826'], $this->key($this->phone($owner), 'frozen-1'))->assertStatus(423);
        $this->assertSame(0, RampOrder::where('user_id', $c->walletUser()->id)->count());
    }

    protected function tearDown(): void
    {
        \App\Security\Lockdown::forget();   // the freeze above must not leak into other tests
        parent::tearDown();
    }
}
