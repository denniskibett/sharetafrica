<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupCompany;
use App\Support\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Website › Companies: the businesses Sharet runs, shown on /company and at /companies/{slug}. */
class GroupCompanyController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('admin.group-companies.index');
        }

        return Table::respond($request, GroupCompany::query(), fn (GroupCompany $c) => [
            'id' => $c->id,
            'company' => ['name' => $c->name, 'email' => $c->host ?? $c->sector, 'initials' => mb_strtoupper(mb_substr($c->name, 0, 2)), 'avatar' => $c->logo_url, 'url' => route('companies.show', $c->slug)],
            'sector' => $c->sector,
            'status' => Table::pill($c->published ? 'On the website' : 'Hidden', $c->published ? 'success' : 'neutral'),
            'sort' => $c->sort,
            'form' => $c->only(['name', 'slug', 'sector', 'tagline', 'description', 'served', 'url', 'email', 'phone', 'sort', 'published']) + ['services' => implode("\n", $c->services ?? []), 'logo' => $c->logo_url],
        ], ['name', 'sector', 'tagline'], ['sort' => 'sort', 'name' => 'name'], [
            'total' => GroupCompany::count(), 'total_hint' => 'Businesses in the group',
            'published' => GroupCompany::where('published', true)->count(), 'published_hint' => 'Shown on the website',
        ], 'sort');
    }

    public function store(Request $request): JsonResponse
    {
        $company = GroupCompany::create($this->validated($request) + ['sort' => (int) GroupCompany::max('sort') + 1]);
        $this->saveLogo($request, $company);

        return response()->json(['message' => "{$company->name} added."]);
    }

    public function update(Request $request, GroupCompany $company): JsonResponse
    {
        $company->update($this->validated($request, $company));
        $this->saveLogo($request, $company);

        return response()->json(['message' => "{$company->name} saved."]);
    }

    public function destroy(GroupCompany $company): JsonResponse
    {
        $company->logo && Storage::disk('public')->delete($company->logo);
        $company->delete();

        return response()->json(['message' => "{$company->name} removed."]);
    }

    protected function validated(Request $request, ?GroupCompany $company = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'slug' => 'nullable|alpha_dash|max:80',
            'sector' => 'nullable|string|max:60',
            'tagline' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:1500',
            'services' => 'nullable|string|max:3000',
            'served' => 'nullable|string|max:160',
            'url' => 'nullable|url|max:190',
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|string|max:40',
            'sort' => 'nullable|integer|min:0|max:999',
            'published' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);
        unset($data['logo']);

        $slug = Str::slug(($data['slug'] ?? null) ?: $data['name']);
        for ($i = 2, $base = $slug; GroupCompany::where('slug', $slug)->when($company, fn ($q) => $q->whereKeyNot($company->id))->exists(); $i++) {
            $slug = "$base-$i";
        }
        $data['slug'] = $slug;
        $data['services'] = array_values(array_filter(array_map('trim', preg_split('/\R/', $data['services'] ?? ''))));
        $data['published'] = $request->boolean('published', true);

        return $data;
    }

    protected function saveLogo(Request $request, GroupCompany $company): void
    {
        if ($request->hasFile('logo')) {
            $company->logo && Storage::disk('public')->delete($company->logo);
            $company->update(['logo' => $request->file('logo')->store('group-companies', 'public')]);
        }
    }
}
