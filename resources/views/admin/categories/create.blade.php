<x-layouts.app title="Add Category" heading="Add Category">
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @include('admin.categories._form')
    </form>
</x-layouts.app>
