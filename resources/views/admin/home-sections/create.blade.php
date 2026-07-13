<x-layouts.app title="Add Homepage Section" heading="Add Homepage Section">
    <form method="POST" action="{{ route('admin.home-sections.store') }}" enctype="multipart/form-data">
        @include('admin.home-sections._form')
    </form>
</x-layouts.app>
