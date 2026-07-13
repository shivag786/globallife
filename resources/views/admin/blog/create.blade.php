<x-layouts.app title="Add Post" heading="Add Post">
    <form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
        @include('admin.blog._form')
    </form>
</x-layouts.app>
