<x-layouts.app title="Edit Homepage Section" heading="Edit Homepage Section">
    <form method="POST" action="{{ route('admin.home-sections.update', $section) }}" enctype="multipart/form-data">
        @include('admin.home-sections._form')
    </form>
</x-layouts.app>
