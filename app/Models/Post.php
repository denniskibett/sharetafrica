<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A blog post (Website › Blog, public at /blog/{slug}). body is sanitised HTML from the rich-text editor. */
class Post extends Model
{
    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'cover', 'status', 'published_at', 'author_id'];

    protected $casts = ['published_at' => 'datetime'];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover ? asset('storage/'.$this->cover) : null;
    }

    public function getReadingMinutesAttribute(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body)) / 220));
    }
}
