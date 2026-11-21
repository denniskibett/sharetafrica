<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Support\Kyc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Business settings for a tenant (merchant or business): the bank-grade business profile (KYB).
 * Public details live on `companies`; directors and the settlement account are encrypted in settings.kyb.
 * Any verified personal account can start a business here (Account menu › Start a business; the first save creates
 * it and makes the holder a merchant), and they are always listed as its first director. Business documents
 * (DOCUMENTS) are uploaded here, stored privately and encrypted in settings.kyb_documents. Full business verification also needs their personal
 * bank-grade verification (App\Support\Kyc).
 */
class CompanySettingsController extends Controller
{
    public const REQUIRED = ['legal_name', 'type', 'registration_number', 'tax_id', 'nature_of_business', 'address', 'email', 'phone', 'directors', 'settlement', 'documents'];

    /** Business documents staff check before approving (Administration › Businesses). `required`: always, never, or only for one business type. */
    public const DOCUMENTS = [
        'certificate' => ['label' => 'Certificate of registration', 'hint' => 'Business name or incorporation certificate from BRS.', 'required' => true],
        'kra_pin' => ['label' => 'KRA PIN certificate', 'hint' => "The business's PIN certificate from iTax.", 'required' => true],
        'cr12' => ['label' => 'CR12', 'hint' => 'Limited companies: the official list of directors and shareholders.', 'required' => 'limited_company'],
        'bank_letter' => ['label' => 'Bank letter or statement', 'hint' => 'Shows the settlement account in the business name.', 'required' => true],
        'permit' => ['label' => 'Business permit', 'hint' => 'County single business permit, if you have one.', 'required' => false],
    ];

    public function edit(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('settings.company');
        }

        $user = $request->user();
        $company = $user->company;
        $kyb = $this->kyb($company);
        $kyb['directors'] = $this->withOwner($user, $kyb['directors'] ?? []);
        $values = ($company?->only(['name', 'legal_name', 'registration_number', 'tax_id', 'email', 'phone', 'website', 'country', 'city', 'address']) ?? ['country' => 'KE']) + $kyb;
        $values['country'] = Company::countryName($values['country'] ?? null);
        $values['documents'] = self::documentsComplete($company, $kyb['type'] ?? null) ? ['all'] : null;
        $done = self::countDone($values);

        return response()->json([
            'company' => $values + [
                'directors' => $kyb['directors'],
                'settlement' => $kyb['settlement'] ?? ['method' => 'bank', 'bank' => '', 'branch' => '', 'account_name' => '', 'account_number' => '', 'paybill' => ''],
            ],
            'logo' => $company?->logo ? asset('storage/'.$company->logo) : null,
            'documents' => self::documentsOf($company, $kyb['type'] ?? null, fn ($type) => route('settings.company.document', $type)),
            'progress' => ['done' => $done, 'of' => count(self::REQUIRED)],
            'verified' => (bool) $company?->verified_at,
            'review' => $company?->settings['kyb_review'] ?? null,   // staff decision (Administration › Businesses); a rejection carries a reason
            'exists' => (bool) $company,
            // The business's own Sharet account (separate from the owner's personal one): payments, API keys and settlements run on it
            'account' => $company ? ['sharet_id' => $company->walletUser()->sharet_id, 'handle' => $company->walletUser()->handle] : null,
            'owner' => [
                'name' => $user->name,
                'personal' => Kyc::progress($user)['basic']['complete'],
                'phone' => filled($user->phone), // with a number on file, what is missing is the ID check, not the number
                'bank_grade' => Kyc::isBankGrade($user),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'legal_name' => 'nullable|string|max:190',
            'type' => 'nullable|in:sole_proprietor,partnership,limited_company,ngo,other',
            'registration_number' => 'nullable|string|max:60',
            'registered_on' => 'nullable|date|before_or_equal:today',
            'tax_id' => ['nullable', 'string', 'regex:/^[AP][0-9]{9}[A-Z]$/i'],
            'vat_number' => 'nullable|string|max:40',
            'nature_of_business' => 'nullable|string|max:190',
            'annual_turnover' => 'nullable|in:under_5m,5m_50m,50m_500m,over_500m',
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:190',
            'country' => 'nullable|string|max:60',
            'city' => 'nullable|string|max:60',
            'address' => 'nullable|string|max:255',
            'directors' => 'nullable|array|max:10',
            'directors.*.user_id' => 'nullable|integer',
            'directors.*.name' => 'nullable|string|max:120',
            'directors.*.id_number' => 'nullable|string|max:30',
            'directors.*.role' => 'nullable|string|max:60',
            'directors.*.shareholding' => 'nullable|numeric|min:0|max:100',
            'settlement.method' => 'nullable|in:bank,mpesa',
            'settlement.bank' => 'nullable|string|max:120',
            'settlement.branch' => 'nullable|string|max:120',
            'settlement.account_name' => 'nullable|string|max:160',
            'settlement.account_number' => 'nullable|string|max:40',
            'settlement.paybill' => 'nullable|string|max:20',
        ], ['tax_id.regex' => 'A KRA PIN looks like P051234567X.']);

        $user = $request->user();
        if (! Kyc::progress($user)['basic']['complete']) {
            return response()->json(['message' => filled($user->phone)
                    ? 'Verify your personal account first (ID photos and a selfie): only verified account holders can register a business.'
                    : 'Add your M-Pesa number to your personal account first: only account holders can register a business.',
                'errors' => ['owner' => ['Personal account needed.']]], 422);
        }
        $company = $user->company ?? new Company(['status' => true]);
        $company->fill(collect($data)->only(['name', 'legal_name', 'registration_number', 'email', 'phone', 'website', 'country', 'city', 'address'])->all());
        $company->phone = \App\Support\Identity::normalisePhone($data['phone'] ?? null);   // +254… like people's numbers
        $company->tax_id = isset($data['tax_id']) ? strtoupper($data['tax_id']) : null;
        $company->country = Company::countryCode($data['country'] ?? null);   // companies.country is ISO 3166 alpha-2

        $kyb = collect($data)->only(['type', 'registered_on', 'vat_number', 'nature_of_business', 'annual_turnover', 'settlement'])->all();
        $kyb['directors'] = $this->withOwner($user, array_values(array_filter($data['directors'] ?? [], fn ($d) => filled($d['name'] ?? null))));
        $company->settings = array_merge($company->settings ?? [], ['kyb' => encrypt($kyb), 'kyb_updated_at' => now()->toIso8601String()]);
        if (($company->settings['kyb_review']['status'] ?? null) === 'rejected') {   // fixed after a rejection: back in the staff queue
            $company->settings = array_merge($company->settings, ['kyb_review' => ['status' => 'pending'] + $company->settings['kyb_review']]);
        }
        $company->save();

        if (! $user->company_id) {
            $user->update(['company_id' => $company->id]);
        }
        $company->walletUser();   // the business's own account exists from the first save (its Sharet ID and @name)
        // A business started from a personal account: the holder can now take payments as a merchant (test keys at once;
        // live keys only take money once staff verify the business). The personal account stays as it is.
        if (! $user->hasAnyRole(['merchant', 'business'])) {
            $user->assignRole('merchant');
        }
        $user->setRelation('company', $company);

        return response()->json(['message' => 'Business details saved. Our team reviews them before raising your limits.']);
    }

    /** Upload one business document (PDF or photo, 8 MB), stored privately; replaces the previous one of that kind. */
    public function uploadDocument(Request $request, string $type): JsonResponse
    {
        abort_unless(isset(self::DOCUMENTS[$type]), 404);
        $request->validate(['file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:8192'],
            ['file.mimes' => 'Upload a PDF or a photo (JPG, PNG or WebP).', 'file.max' => 'That file is over 8 MB.']);
        $company = $request->user()->company;
        if (! $company) {
            return response()->json(['message' => 'Save your business details first, then add its documents.', 'errors' => ['file' => ['Save your business details first.']]], 422);
        }

        $docs = self::documentFiles($company);
        isset($docs[$type]['path']) && Storage::disk('local')->delete($docs[$type]['path']);
        $file = $request->file('file');
        $docs[$type] = ['path' => $file->storeAs('kyb/'.$company->id, $type.'-'.\Illuminate\Support\Str::random(10).'.'.strtolower($file->extension()), 'local'),
            'name' => mb_substr($file->getClientOriginalName(), 0, 120), 'at' => now()->toIso8601String(), 'by' => $request->user()->id];
        $settings = array_merge($company->settings ?? [], ['kyb_documents' => encrypt($docs)]);
        if (($settings['kyb_review']['status'] ?? null) === 'rejected') {   // a new document after a rejection: back in the staff queue
            $settings['kyb_review'] = ['status' => 'pending'] + $settings['kyb_review'];
        }
        $company->update(['settings' => $settings]);

        return response()->json(['message' => self::DOCUMENTS[$type]['label'].' uploaded.']);
    }

    /** The owner opens one of their business documents. */
    public function document(Request $request, string $type)
    {
        return self::streamDocument($request->user()->company ?? abort(404), $type);
    }

    /** Streams a stored business document (owner here, staff in Administration › Businesses). */
    public static function streamDocument(Company $company, string $type)
    {
        $path = self::documentFiles($company)[$type]['path'] ?? null;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    /** @return array<string, array{path: string, name: string, at: string}> */
    public static function documentFiles(?Company $company): array
    {
        try {
            return isset($company?->settings['kyb_documents']) ? (array) decrypt($company->settings['kyb_documents']) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /** What the business must send for its type, with what it has sent. $url gives each document's link. */
    public static function documentsOf(?Company $company, ?string $businessType, callable $url): array
    {
        $files = self::documentFiles($company);

        return collect(self::DOCUMENTS)->map(fn ($d, $type) => [
            'type' => $type, 'label' => $d['label'], 'hint' => $d['hint'],
            'required' => $d['required'] === true || $d['required'] === $businessType,
            'uploaded' => isset($files[$type]), 'name' => $files[$type]['name'] ?? null,
            'at' => isset($files[$type]) ? \Illuminate\Support\Carbon::parse($files[$type]['at'])->timezone('Africa/Nairobi')->format('j M Y, H:i') : null,
            'url' => isset($files[$type]) ? $url($type) : null,
        ])->values()->all();
    }

    public static function documentsComplete(?Company $company, ?string $businessType): bool
    {
        return $company !== null && collect(self::documentsOf($company, $businessType, fn () => null))->every(fn ($d) => ! $d['required'] || $d['uploaded']);
    }

    public function logo(Request $request): JsonResponse
    {
        $request->validate(['logo' => 'required|image|mimes:jpg,jpeg,png,webp,svg|max:2048']);
        $company = $request->user()->company ?? abort(422, 'Save your business details first.');
        $company->logo && Storage::disk('public')->delete($company->logo);
        $company->update(['logo' => $request->file('logo')->store('images/companies', 'public')]);

        return response()->json(['message' => 'Logo updated.']);
    }

    /** The account holder is always director #1 (name and ID from their personal verification). */
    protected function withOwner($user, array $directors): array
    {
        $mine = collect($directors)->first(fn ($d) => ($d['user_id'] ?? null) === $user->id) ?? [];
        $others = array_values(array_filter($directors, fn ($d) => ($d['user_id'] ?? null) !== $user->id));

        return array_merge([[
            'user_id' => $user->id, 'name' => $user->name, 'id_number' => Kyc::data($user)['id_number'] ?? '',
            'role' => $mine['role'] ?? 'Director', 'shareholding' => $mine['shareholding'] ?? '', 'self' => true,
        ]], $others);
    }

    /** How many REQUIRED KYB items are filled in, from the merged public + encrypted values. */
    public static function countDone(array $values): int
    {
        return collect(self::REQUIRED)->filter(fn ($k) => filled($values[$k] ?? null) && (! is_array($values[$k]) || array_filter($values[$k], fn ($r) => filled(is_array($r) ? array_filter($r) : $r))))->count();
    }

    /** A saved business's KYB progress for staff review: ['done' => n, 'of' => m]. */
    public static function progressOf(Company $company): array
    {
        $values = $company->only(['legal_name', 'registration_number', 'tax_id', 'email', 'phone', 'address']) + (new self)->kyb($company);
        $values['documents'] = self::documentsComplete($company, $values['type'] ?? null) ? ['all'] : null;

        return ['done' => self::countDone($values), 'of' => count(self::REQUIRED)];
    }

    public function kyb(?Company $company): array
    {
        try {
            return isset($company?->settings['kyb']) ? (array) decrypt($company->settings['kyb']) : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
