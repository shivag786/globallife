<x-layouts.app title="Add VIP Member" heading="Add VIP Member">
    <form method="POST" action="{{ route('manager.vip-members.store') }}">
        @include('manager.vip-members._form')
    </form>
</x-layouts.app>
