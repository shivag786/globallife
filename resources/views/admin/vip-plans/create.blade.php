<x-layouts.app title="Add VIP Plan" heading="Add VIP Plan">
    <form method="POST" action="{{ route('admin.vip-plans.store') }}">
        @include('admin.vip-plans._form')
    </form>
</x-layouts.app>
