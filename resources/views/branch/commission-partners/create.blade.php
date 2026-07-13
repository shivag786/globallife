<x-layouts.app title="Add Commission Partner" heading="Add Commission Partner">
    <form method="POST" action="{{ route('branch.commission-partners.store') }}">
        @include('branch.commission-partners._form')
    </form>
</x-layouts.app>
