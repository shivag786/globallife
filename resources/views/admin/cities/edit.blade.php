<x-layouts.app title="Edit City" heading="Edit City">
    <form method="POST" action="{{ route('admin.cities.update', $city) }}">
        @include('admin.cities._form')
    </form>
</x-layouts.app>
