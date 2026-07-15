<x-layouts.app title="Edit Brand" heading="Edit Brand">
    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data">
        @include('admin.brands._form')
    </form>
</x-layouts.app>
