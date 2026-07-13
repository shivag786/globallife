<x-layouts.app title="Edit Commission Partner" heading="Edit Commission Partner">
    <form method="POST" action="{{ route('branch.commission-partners.update', $partner) }}">
        @include('branch.commission-partners._form')
    </form>
</x-layouts.app>
