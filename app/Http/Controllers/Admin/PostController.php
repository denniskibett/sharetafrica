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

        return Table::respond($request, $query, fn (Post $p) => [
            'id' => $p->id,
            'title' => ['label' => $p->title, 'url' => route('admin.posts.edit', $p)],
            'author' => $p->author?->name,
            'status' => $p->status === 'published' ? Table::pill($p->published_at?->isFuture() ? 'Scheduled' : 'Published', $p->published_at?->isFuture() ? 'info' : 'success') : Table::pill('Draft', 'neutral'),
            'date' => Table::date($p->published_at ?? $p->updated_at),
            'url' => $p->status === 'published' ? route('blog.show', $p->slug) : null,
        ], ['title', 'excerpt'], ['title' => 'title', 'date' => 'updated_at'], [
            'total' => Post::count(), 'total_hint' => 'All posts',
            'published' => Post::published()->count(), 'published_hint' => 'Live on /blog',
            'drafts' => Post::where('status', 'draft')->count(), 'drafts_hint' => 'Not published yet',
        ], '-updated_at');
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
