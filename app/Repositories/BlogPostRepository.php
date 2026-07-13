<?php

namespace App\Repositories;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BlogPostRepository
{
    /**
     * @return Collection<int, BlogPost>
     */
    public function allLatest(): Collection
    {
        return BlogPost::orderByDesc('created_at')->get();
    }

    public function publishedPaginated(int $perPage = 9): LengthAwarePaginator
    {
        return BlogPost::where('status', 'published')
            ->withCount('likes')
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function latestPublished(int $limit = 3): Collection
    {
        return BlogPost::where('status', 'published')
            ->withCount('likes')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): BlogPost
    {
        return BlogPost::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(BlogPost $post, array $data): BlogPost
    {
        $post->update($data);

        return $post;
    }
}
