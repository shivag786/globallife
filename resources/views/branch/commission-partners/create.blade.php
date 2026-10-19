<x-layouts.app title="Add Commission Partner" heading="Add Commission Partner">
    <form method="POST" action="{{ route('branch.commission-partners.store') }}" enctype="multipart/form-data">
        @include('branch.commission-partners._form')
    </form>
</x-layouts.app>
