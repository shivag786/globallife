<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlogCommentRequest;
use App\Models\BlogPost;
use App\Repositories\BlogPostRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    private const LIKE_COOKIE = 'gl_like_token';

    public function index(BlogPostRepository $posts): View
    {
        return view('blog.index', ['posts' => $posts->publishedPaginated()]);
    }

    public function show(Request $request, BlogPost $post, BlogPostRepository $posts): View
    {
        abort_unless($post->status === 'published', 404);

        $post->increment('views');

        $likeToken = $request->cookie(self::LIKE_COOKIE);
        $hasLiked = $likeToken && $post->likes()->where('like_token', $likeToken)->exists();

        return view('blog.show', [
            'post' => $post->load('comments'),
            'likesCount' => $post->likes()->count(),
            'hasLiked' => $hasLiked,
            'relatedPosts' => $posts->latestPublished(4)->reject(fn ($p) => $p->id === $post->id)->take(3),
        ]);
    }

    public function like(Request $request, BlogPost $post): JsonResponse
    {
        $likeToken = $request->cookie(self::LIKE_COOKIE);
        $setCookie = null;

        if (! $likeToken) {
            $likeToken = Str::random(40);
            $setCookie = Cookie::make(self::LIKE_COOKIE, $likeToken, 60 * 24 * 365);
        }

        $existing = $post->likes()->where('like_token', $likeToken)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $post->likes()->create(['like_token' => $likeToken]);
            $liked = true;
        }

        $response = response()->json(['liked' => $liked, 'count' => $post->likes()->count()]);

        return $setCookie ? $response->withCookie($setCookie) : $response;
    }

    public function storeComment(StoreBlogCommentRequest $request, BlogPost $post): RedirectResponse
    {
        $post->comments()->create($request->safe()->except('website'));

        return back()->with('status', 'Thanks for your comment!')->withFragment('comments');
    }
}
