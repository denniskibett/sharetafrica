<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Support\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Website › Team: the people shown on /company#team, with photos. */
class TeamController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('admin.team.index');
        }

        return Table::respond($request, TeamMember::query(), fn (TeamMember $m) => [
            'id' => $m->id,
            'person' => ['name' => $m->name, 'email' => $m->role, 'initials' => $m->initials, 'avatar' => $m->photo_url],
            'name' => $m->name, 'role' => $m->role, 'bio' => $m->bio, 'linkedin' => $m->linkedin, 'sort' => $m->sort,
            'published' => $m->published,
            'status' => Table::pill($m->published ? 'On the website' : 'Hidden', $m->published ? 'success' : 'neutral'),
        ], ['name', 'role'], ['sort' => 'sort', 'name' => 'name'], [
            'total' => TeamMember::count(), 'total_hint' => 'People on the team',
            'published' => TeamMember::where('published', true)->count(), 'published_hint' => 'Shown on /company',
            'photos' => TeamMember::whereNotNull('photo')->count(), 'photos_hint' => 'Have a photo',
        ], 'sort');
    }

    public function store(Request $request): JsonResponse
    {
        $member = TeamMember::create($this->validated($request) + ['sort' => (int) TeamMember::max('sort') + 1]);
        $this->savePhoto($request, $member);

        return response()->json(['message' => "{$member->name} added to the team."]);
    }

    public function update(Request $request, TeamMember $member): JsonResponse
    {
        $member->update($this->validated($request));
        $this->savePhoto($request, $member);

        return response()->json(['message' => "{$member->name} saved."]);
    }

    public function destroy(TeamMember $member): JsonResponse
    {
        $member->photo && Storage::disk('public')->delete($member->photo);
        $member->delete();

        return response()->json(['message' => "{$member->name} removed from the team."]);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'role' => 'required|string|max:120',
            'bio' => 'nullable|string|max:600',
            'linkedin' => 'nullable|url|max:255',
            'sort' => 'nullable|integer|min:0|max:999',
            'published' => 'nullable|boolean',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        unset($data['photo']);
        $data['published'] = $request->boolean('published', true);

        return array_filter($data, fn ($v) => $v !== null) + ['bio' => null, 'linkedin' => null];
    }

    protected function savePhoto(Request $request, TeamMember $member): void
    {
        if ($request->hasFile('photo')) {
            $member->photo && Storage::disk('public')->delete($member->photo);
            $member->update(['photo' => $request->file('photo')->store('team', 'public')]);
        }
    }
}
