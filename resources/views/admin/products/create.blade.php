<x-layouts.app title="Add Product" heading="Add Product">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @include('admin.products._form')
    </form>
</x-layouts.app>
