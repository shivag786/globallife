<x-layouts.app title="Edit Product" heading="Edit Product">
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @include('admin.products._form')
    </form>
</x-layouts.app>
