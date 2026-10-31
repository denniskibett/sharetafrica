<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON for the dashboard's Alpine tables (resources/js/dashboard.js → dataTable):
 * { data: [...rows], meta: { current_page, last_page, per_page, total, from, to }, stats: {...} }.
 * Handles ?search=, ?sort=column or -column (whitelisted), ?per_page= (10–100).
 */
class Table
{
    /**
     * @param  array<string>  $searchable  columns matched with LIKE
     * @param  array<string, string>  $sortable  ?sort key => column
     */
    public static function respond(Request $request, Builder $query, callable $row, array $searchable = [], array $sortable = [], array $stats = [], string $defaultSort = '-created_at', array $extra = []): JsonResponse
    {
        if (($term = trim((string) $request->query('search'))) !== '' && $searchable) {
            $query->where(function (Builder $q) use ($searchable, $term) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%');
                }
            });
        }

        $sort = (string) $request->query('sort', $defaultSort) ?: $defaultSort;
        $key = ltrim($sort, '-');
        $column = $sortable[$key] ?? ($key === ltrim($defaultSort, '-') ? $key : null);
        if ($column) {
            $query->orderBy($column, str_starts_with($sort, '-') ? 'desc' : 'asc');
        }

        $page = $query->paginate(min(100, max(5, (int) $request->query('per_page', 10))))->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map($row)->values(),
            'meta' => [
                'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'per_page' => $page->perPage(),
                'total' => $page->total(), 'from' => $page->firstItem() ?? 0, 'to' => $page->lastItem() ?? 0,
            ],
            'stats' => $stats,
            'extra' => $extra,
        ]);
    }

    /** Person cell: { name, email, initials, avatar, url } */
    public static function person($user, ?string $url = null): array
    {
        $name = trim((string) ($user->name ?? '')) ?: 'Unnamed';

        return [
            'name' => $name,
            'email' => $user->email ?? '',
            'initials' => collect(preg_split('/\s+/', $name))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode(''),
            'avatar' => ! empty($user->avatar) ? asset('storage/'.$user->avatar) : null,
            'url' => $url,
        ];
    }

    public static function pill(string $label, string $tone = 'neutral'): array
    {
        return ['label' => $label, 'tone' => $tone];
    }

    /** % change between two counts, null when there is nothing to compare with. */
    public static function delta(int|float $now, int|float $before): ?float
    {
        return $before > 0 ? round(($now - $before) / $before * 100, 1) : null;
    }

    public static function date($value): ?string
    {
        return $value ? $value->timezone('Africa/Nairobi')->format('j M Y') : null;
    }
}
