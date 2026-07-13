<x-layouts.app title="Edit VIP Plan" heading="Edit VIP Plan">
    <form method="POST" action="{{ route('admin.vip-plans.update', $plan) }}">
        @include('admin.vip-plans._form')
    </form>
</x-layouts.app>
