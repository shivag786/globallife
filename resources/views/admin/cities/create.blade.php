<x-layouts.app title="Add City" heading="Add City">
    <form method="POST" action="{{ route('admin.cities.store') }}">
        @include('admin.cities._form')
    </form>
</x-layouts.app>
