<x-layouts.app title="Edit Category" heading="Edit Category">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @include('admin.categories._form')
    </form>
</x-layouts.app>
