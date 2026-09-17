<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DeveloperApplication;
use App\Models\Enquiry;
use App\Models\TradeApplication;
use App\Models\User;
use App\Models\WaitingListEntry;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Marketing pages
    |--------------------------------------------------------------------------
    */

    public function index()      { return view('index'); }
    public function merchants()  { return view('merchants'); }
    public function personal()   { return view('personal'); }
    public function trade()      { return view('trade'); }
    public function developers() { return view('developers'); }
    public function company()    { return view('company'); }
    public function contact()    { return view('contact'); }

    /*
    |--------------------------------------------------------------------------
    | Personal page → waiting list (intent: individual)
    |--------------------------------------------------------------------------
    */

    public function storePersonalSignup(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:190',
            'phone'   => 'nullable|string|max:32',
            'country' => 'nullable|string|size:2',
        ]);

        $user = $this->upsertUser($data, 'individual');

        return $this->attachWaitingList($user, 'individual', 'personal-page');
    }

    /*
    |--------------------------------------------------------------------------
    | Merchants page → waiting list (intent: merchant)
    |--------------------------------------------------------------------------
    */

    public function storeMerchantSignup(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'business' => 'required|string|max:190',
            'email'    => 'required|email|max:190',
            'phone'    => 'required|string|max:32',
            'country'  => 'nullable|string|size:2',
            'rails'    => 'nullable|string|max:255',
            'volume'   => 'nullable|string|max:60',
            'brief'    => 'nullable|string|max:2000',
        ]);

        $user = $this->upsertUser($data, 'merchant');

        return $this->attachWaitingList($user, 'merchant', 'merchants-page', [
            'business' => $data['business'],
            'rails'    => $data['rails']  ?? null,
            'volume'   => $data['volume'] ?? null,
            'brief'    => $data['brief']  ?? null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Trade page → trade application
    |--------------------------------------------------------------------------
    */

    public function storeTradeApplication(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:120',
            'business'            => 'required|string|max:190',
            'email'               => 'required|email|max:190',
            'phone'               => 'required|string|max:32',
            'trade_type'          => 'required|in:importer,exporter,both,diaspora,intra_africa',
            'corridor'            => 'nullable|string|max:190',
            'corridor_other'      => 'nullable|string|max:190|required_if:corridor,OTHER',
            'need'                => 'required|in:payment_abroad,receive_from_abroad,intra_africa,credit_line,all',
            'monthly_volume'      => 'nullable|string|max:60',
            'expected_order_size' => 'nullable|string|max:120',
            'brief'               => 'nullable|string|max:2000',
        ]);

        $user = $this->upsertUser($data, 'business');

        // Resolve corridor — either the stored code or the free-text "Other"
        $corridor = $data['corridor'] ?? null;
        if ($corridor === 'OTHER' && !empty($data['corridor_other'])) {
            $corridor = 'Other: ' . $data['corridor_other'];
        }

        $existing = $user->tradeApplications()
            ->whereNotIn('status', ['rejected'])
            ->first();

        if ($existing) {
            $existing->update([
                'trade_type'          => $data['trade_type'],
                'corridor'            => $corridor,
                'need'                => $data['need'],
                'monthly_volume'      => $data['monthly_volume']      ?? $existing->monthly_volume,
                'expected_order_size' => $data['expected_order_size'] ?? $existing->expected_order_size,
                'brief'               => $data['brief']               ?? $existing->brief,
                'ip_address'          => $request->ip(),
                'user_agent'          => substr((string) $request->userAgent(), 0, 500),
            ]);

            return back()
                ->with('trade_received', true)
                ->with('trade_message', 'We updated your existing trade application with the latest details. Our trade team will be in touch.');
        }

        $user->tradeApplications()->create([
            'trade_type'          => $data['trade_type'],
            'corridor'            => $corridor,
            'need'                => $data['need'],
            'monthly_volume'      => $data['monthly_volume']      ?? null,
            'expected_order_size' => $data['expected_order_size'] ?? null,
            'brief'               => $data['brief']               ?? null,
            'status'              => 'new',
            'ip_address'          => $request->ip(),
            'user_agent'          => substr((string) $request->userAgent(), 0, 500),
        ]);

        return back()
            ->with('trade_received', true)
            ->with('trade_message', 'Thank you — your trade application has been received. Our trade team will be in touch within 1–2 working days.');
    }

    /*
    |--------------------------------------------------------------------------
    | Developers page → developer application
    |--------------------------------------------------------------------------
    */

    public function storeDeveloperApplication(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'company'  => 'nullable|string|max:190',
            'email'    => 'required|email|max:190',
            'interest' => 'required|in:sandbox,production_keys,white_label,licensing,bespoke_rail',
            'brief'    => 'required|string|max:2000',
        ]);

        $user = $this->upsertUser($data, 'techie');

        $existing = $user->developerApplications()
            ->whereNotIn('status', ['rejected'])
            ->first();

        if ($existing) {
            $existing->update([
                'interest'   => $data['interest'],
                'brief'      => $data['brief'],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);

            return back()
                ->with('dev_received', true)
                ->with('dev_message', 'We updated your existing developer request. Check your inbox — sandbox access is usually granted within one working day.');
        }

        $user->developerApplications()->create([
            'interest'   => $data['interest'],
            'brief'      => $data['brief'],
            'status'     => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return back()
            ->with('dev_received', true)
            ->with('dev_message', 'Thank you — your developer request has been received. Sandbox access is usually granted within one working day.');
    }

    /*
    |--------------------------------------------------------------------------
    | Contact page → generic enquiry
    |--------------------------------------------------------------------------
    */

    public function storeContact(Request $request)
    {
        $data = $request->validate([
            'audience' => 'required|in:merchant,individual,trader,developer,institution,press,career,other',
            'name'     => 'required|string|max:120',
            'business' => 'nullable|string|max:190',
            'email'    => 'required|email|max:190',
            'phone'    => 'nullable|string|max:32',
            'country'  => 'nullable|string|max:60',
            'brief'    => 'required|string|max:3000',
        ]);

        $user = $this->upsertUser($data, $this->intentFromAudience($data['audience']));

        // Enquiries are never blocked — a user can send many
        $user->enquiries()->create([
            'audience'   => $data['audience'],
            'business'   => $data['business'] ?? null,
            'brief'      => $data['brief'],
            'status'     => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return back()
            ->with('contact_received', true)
            ->with('contact_message', 'Thank you — your note has been received. A real person on our team will reply within 48 working hours.');
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    /**
     * Find-or-create the user for this email, then update any fields they
     * provided that the user does not already have.
     *
     * Never blocks. Always returns a User ready for the caller to attach
     * a pivot row.
     */
    protected function upsertUser(array $data, string $intent): User
    {
        $email = strtolower(trim($data['email']));

        $user = User::withTrashed()->firstOrNew(['email' => $email]);

        if ($user->exists && $user->trashed()) {
            $user->restore();
        }

        if (!$user->exists) {
            $user->email             = $email;
            $user->password          = bcrypt(Str::random(32));
            $user->onboarding_status = 'waiting_list';
            $user->status            = true;
        }

        $user->name    = $user->name    ?: ($data['name']  ?? null);
        $user->phone   = $user->phone   ?: ($data['phone'] ?? null);
        $user->country = $user->country ?: ($data['country'] ?? null);

        // First intent wins
        if (!$user->intent || $user->intent === 'undecided') {
            $user->intent = $intent;
        }

        if (!empty($data['business']) && !$user->company_id) {
            $company = $this->upsertCompany($data['business']);
            $user->company_id = $company->id;
        }

        try {
            $user->save();
        } catch (QueryException $e) {
            $user = User::withTrashed()->where('email', $email)->firstOrFail();
        }

        return $user;
    }

    protected function upsertCompany(string $name): Company
    {
        $slug = Str::slug($name);

        return Company::firstOrCreate(
            ['slug' => $slug],
            [
                'name'   => $name,
                'type'   => 'business',
                'status' => true,
            ]
        );
    }

    protected function attachWaitingList(User $user, string $intent, string $source, array $meta = [])
    {
        $existing = WaitingListEntry::where('user_id', $user->id)->first();

        if ($existing) {
            return back()
                ->with('waitlist_received', true)
                ->with('waitlist_message', 'You are already on the Sharet waiting list. We will be in touch when it is your turn.');
        }

        try {
            WaitingListEntry::create([
                'user_id'    => $user->id,
                'intent'     => $intent,
                'source'     => $source,
                'meta'       => $meta ?: null,
                'status'     => 'pending',
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
            ]);
        } catch (QueryException $e) {
            return back()
                ->with('waitlist_received', true)
                ->with('waitlist_message', 'You are already on the Sharet waiting list. We will be in touch when it is your turn.');
        }

        return back()
            ->with('waitlist_received', true)
            ->with('waitlist_message', 'You are on the list. We will be in touch when it is your turn — usually within a few weeks.');
    }

    protected function intentFromAudience(string $audience): string
    {
        return match ($audience) {
            'merchant'   => 'merchant',
            'trader'     => 'business',
            'developer'  => 'techie',
            'individual' => 'individual',
            default      => 'undecided',
        };
    }
}