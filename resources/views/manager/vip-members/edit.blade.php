<x-layouts.app title="Edit VIP Member" heading="Edit VIP Member">
    <form method="POST" action="{{ route('manager.vip-members.update', $member) }}">
        @include('manager.vip-members._form')
    </form>
</x-layouts.app>
