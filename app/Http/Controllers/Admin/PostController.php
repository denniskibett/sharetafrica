<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\RichText;
use App\Support\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Website › Blog (rich text) and the public /blog pages. */
class PostController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('admin.posts.index');
        }

        $query = Post::query()->with('author:id,name')->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')));
        $reads = $this->reads();

        return Table::respond($request, $query, fn (Post $p) => [
            'id' => $p->id,
            'title' => ['label' => $p->title, 'url' => route('admin.posts.edit', $p)],
            'author' => $p->author?->name,
            'status' => $p->status === 'published' ? Table::pill($p->published_at?->isFuture() ? 'Scheduled' : 'Published', $p->published_at?->isFuture() ? 'info' : 'success') : Table::pill('Draft', 'neutral'),
            'date' => Table::date($p->published_at ?? $p->updated_at),
            'url' => $p->status === 'published' ? route('blog.show', $p->slug) : null,
            'views' => (int) ($reads['/blog/'.$p->slug]->views ?? 0),
            'readers' => (int) ($reads['/blog/'.$p->slug]->readers ?? 0),
            'read_through' => isset($reads['/blog/'.$p->slug]) && $reads['/blog/'.$p->slug]->views ? round($reads['/blog/'.$p->slug]->finished / $reads['/blog/'.$p->slug]->views * 100).'%' : '—',
        ], ['title', 'excerpt'], ['title' => 'title', 'date' => 'updated_at'], [
            'total' => Post::count(), 'total_hint' => 'All posts',
            'published' => Post::published()->count(), 'published_hint' => 'Live on /blog',
            'drafts' => Post::where('status', 'draft')->count(), 'drafts_hint' => 'Not published yet',
        ], '-updated_at');
    }

    /**
     * How one post is doing, from our own analytics (page_views for /blog/{slug}): views, readers,
     * reading time, how far people scroll, how many read to the end, where they came from, and views per day.
     */
    public function stats(Request $request, Post $post)
    {
        $views = \Illuminate\Support\Facades\DB::table('page_views')->where('path', '/blog/'.$post->slug);
        $t = (clone $views)->selectRaw('count(*) as views, count(distinct visitor) as readers,
            avg(case when duration_ms between 1000 and 1800000 then duration_ms end) as ms, avg(scroll_pct) as scroll,
            sum(case when scroll_pct >= 75 then 1 else 0 end) as finished')->first();
        $minutes = $post->reading_minutes;

        if ($request->query('chart')) {
            $daily = (clone $views)->where('created_at', '>=', now()->subDays(29)->startOfDay())
                ->selectRaw('date(created_at) as d, count(*) as n')->groupBy('d')->pluck('n', 'd');
            $days = collect(range(29, 0))->map(fn ($a) => now()->subDays($a));

            return response()->json(['type' => 'bar', 'series' => [['name' => 'Views', 'data' => $days->map(fn ($d) => (int) ($daily[$d->toDateString()] ?? 0))->values()]],
                'xaxis' => ['categories' => $days->map->format('j M')->values(), 'tickAmount' => 10]]);
        }

        $avgSeconds = $t->ms ? (int) round($t->ms / 1000) : 0;

        return response()->json(['stats' => [
            'views' => (int) $t->views, 'views_hint' => 'All time',
            'readers' => (int) $t->readers, 'readers_hint' => 'Counted per day, no cookies',
            'time' => $avgSeconds ? ($avgSeconds >= 60 ? intdiv($avgSeconds, 60).'m '.($avgSeconds % 60).'s' : $avgSeconds.'s') : '—',
            'time_hint' => "Post takes about $minutes min to read",
            'read_through' => $t->views ? round($t->finished / $t->views * 100).'%' : '—', 'read_through_hint' => 'Scrolled at least 75%',
            'scroll' => $t->scroll ? round($t->scroll).'%' : '—',
            // A rough "is it working" signal: people stay for at least half the reading time and most scroll past halfway
            'verdict' => ! $t->views ? 'No readers yet' : (($avgSeconds >= $minutes * 30 && $t->scroll >= 50) ? 'People are reading it' : 'People leave early: tighten the opening'),
        ], 'sources' => (clone $views)->whereNotNull('referrer_host')->selectRaw('referrer_host as label, count(*) as n')->groupBy('referrer_host')->orderByDesc('n')->limit(5)->get()]);
    }

    /** Totals per blog path for the list. */
    protected function reads()
    {
        return \Illuminate\Support\Facades\DB::table('page_views')->where('path', 'like', '/blog/%')
            ->selectRaw('path, count(*) as views, count(distinct visitor) as readers, sum(case when scroll_pct >= 75 then 1 else 0 end) as finished')
            ->groupBy('path')->get()->keyBy('path');
    }

    public function create()
    {
        return view('admin.posts.edit', ['post' => new Post(['status' => 'draft'])]);
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', ['post' => $post]);
    }

    public function store(Request $request): JsonResponse
    {
        $post = Post::create($this->validated($request) + ['author_id' => $request->user()->id]);
        $this->saveCover($request, $post);

        return response()->json(['message' => 'Post saved.', 'edit' => route('admin.posts.edit', $post)]);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        $post->update($this->validated($request, $post));
        $this->saveCover($request, $post);

        return response()->json(['message' => $post->status === 'published' ? 'Post saved and live.' : 'Draft saved.', 'url' => $post->status === 'published' ? route('blog.show', $post->slug) : null]);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->cover && Storage::disk('public')->delete($post->cover);
        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }

    public function publicIndex()
    {
        return view('blog.index', ['posts' => Post::published()->with('author:id,name')->latest('published_at')->paginate(12)]);
    }

    public function publicShow(string $slug)
    {
        return view('blog.show', ['post' => Post::published()->with('author:id,name')->where('slug', $slug)->firstOrFail()]);
    }

    protected function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200|alpha_dash',
            'excerpt' => 'nullable|string|max:300',
            'body' => 'nullable|string|max:200000',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        unset($data['cover']);

        $slug = Str::slug(($data['slug'] ?? null) ?: $data['title']) ?: 'post';
        $base = $slug;
        for ($i = 2; Post::where('slug', $slug)->when($post, fn ($q) => $q->whereKeyNot($post->id))->exists(); $i++) {
            $slug = "$base-$i";
        }

        $data['slug'] = $slug;
        $data['body'] = RichText::clean($data['body'] ?? '');
        $data['excerpt'] = ($data['excerpt'] ?? null) ?: Str::limit(trim(strip_tags($data['body'])), 200);
        $data['published_at'] = $data['status'] === 'published' ? ($data['published_at'] ?? $post?->published_at ?? now()) : ($data['published_at'] ?? null);

        return $data;
    }

    protected function saveCover(Request $request, Post $post): void
    {
        if ($request->hasFile('cover')) {
            $post->cover && Storage::disk('public')->delete($post->cover);
            $post->update(['cover' => $request->file('cover')->store('blog', 'public')]);
        }
    }
}
