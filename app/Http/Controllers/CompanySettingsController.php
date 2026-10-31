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
 * Only someone with a personal Sharet account (name + M-Pesa number) can register a business, and they
 * are always listed as its first director. Full business verification also needs their personal
 * bank-grade verification (App\Support\Kyc).
 */
class CompanySettingsController extends Controller
{
    public const REQUIRED = ['legal_name', 'type', 'registration_number', 'tax_id', 'nature_of_business', 'address', 'email', 'phone', 'directors', 'settlement'];

    public function edit(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('settings.company');
        }

        $user = $request->user();
        $company = $user->company;
        $kyb = $this->kyb($company);
        $kyb['directors'] = $this->withOwner($user, $kyb['directors'] ?? []);
        $values = ($company?->only(['name', 'legal_name', 'type', 'registration_number', 'tax_id', 'email', 'phone', 'website', 'country', 'city', 'address']) ?? ['country' => 'Kenya']) + $kyb;
        $done = collect(self::REQUIRED)->filter(fn ($k) => filled($values[$k] ?? null) && (! is_array($values[$k]) || array_filter($values[$k], fn ($r) => filled(is_array($r) ? array_filter($r) : $r))))->count();

        return response()->json([
            'company' => $values + [
                'directors' => $kyb['directors'],
                'settlement' => $kyb['settlement'] ?? ['method' => 'bank', 'bank' => '', 'branch' => '', 'account_name' => '', 'account_number' => '', 'paybill' => ''],
            ],
            'logo' => $company?->logo ? asset('storage/'.$company->logo) : null,
            'progress' => ['done' => $done, 'of' => count(self::REQUIRED)],
            'verified' => (bool) $company?->verified_at,
            'exists' => (bool) $company,
            'owner' => [
                'name' => $user->name,
                'personal' => Kyc::progress($user)['basic']['complete'],
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
            return response()->json(['message' => 'Add your M-Pesa number to your personal account first: only account holders can register a business.',
                'errors' => ['owner' => ['Personal account needed.']]], 422);
        }
        $company = $user->company ?? new Company(['status' => true]);
        $company->fill(collect($data)->only(['name', 'legal_name', 'type', 'registration_number', 'email', 'phone', 'website', 'country', 'city', 'address'])->all());
        $company->tax_id = isset($data['tax_id']) ? strtoupper($data['tax_id']) : null;

        $kyb = collect($data)->only(['registered_on', 'vat_number', 'nature_of_business', 'annual_turnover', 'settlement'])->all();
        $kyb['directors'] = $this->withOwner($user, array_values(array_filter($data['directors'] ?? [], fn ($d) => filled($d['name'] ?? null))));
        $company->settings = array_merge($company->settings ?? [], ['kyb' => encrypt($kyb), 'kyb_updated_at' => now()->toIso8601String()]);
        $company->save();

        if (! $user->company_id) {
            $user->update(['company_id' => $company->id]);
        }
        $user->setRelation('company', $company);

        return response()->json(['message' => 'Business details saved. Our team reviews them before raising your limits.']);
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

    protected function kyb(?Company $company): array
    {
        try {
            return isset($company?->settings['kyb']) ? (array) decrypt($company->settings['kyb']) : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
