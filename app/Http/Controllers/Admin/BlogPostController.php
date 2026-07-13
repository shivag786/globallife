<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogPostRequest;
use App\Http\Requests\Admin\UpdateBlogPostRequest;
use App\Models\BlogPost;
use App\Repositories\BlogPostRepository;
use App\Services\PermissionMatrixService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BlogPostController extends Controller
{
    public function __construct(private readonly BlogPostRepository $posts)
    {
    }

    public function index(): View
    {
        $this->ensureModuleAccess();

        return view('admin.blog.index', ['posts' => $this->posts->allLatest()]);
    }

    public function create(): View
    {
        $this->ensureModuleAccess();

        return view('admin.blog.create');
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('uploads', 'public');
        }

        $this->posts->create($data);

        return redirect()->route('admin.blog.index')->with('status', 'Post created successfully.');
    }

    public function edit(BlogPost $blogPost): View
    {
        $this->ensureModuleAccess();

        return view('admin.blog.edit', ['post' => $blogPost]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('uploads', 'public');
        }

        if ($data['status'] === 'published' && ! $blogPost->published_at) {
            $data['published_at'] = now();
        }

        $this->posts->update($blogPost, $data);

        return redirect()->route('admin.blog.index')->with('status', 'Post updated successfully.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        abort_unless(auth()->user()->can('blog.delete'), 403);

        $blogPost->delete();

        return redirect()->route('admin.blog.index')->with('status', 'Post deleted.');
    }

    private function ensureModuleAccess(): void
    {
        if (! PermissionMatrixService::userCanAccessModule(auth()->user(), 'blog')) {
            throw new AccessDeniedHttpException;
        }
    }
}
