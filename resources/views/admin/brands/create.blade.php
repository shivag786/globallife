<x-layouts.app title="Add Brand" heading="Add Brand">
    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
        @include('admin.brands._form')
    </form>
</x-layouts.app>
