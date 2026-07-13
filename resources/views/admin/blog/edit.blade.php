<x-layouts.app title="Edit Post" heading="Edit Post">
    <form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
        @include('admin.blog._form')
    </form>
</x-layouts.app>
