<x-layouts.app title="Add VIP Member" heading="Add VIP Member">
    {{-- Submitting this activates the plan: the public page goes live and the
         commission split is booked. That is irreversible, so it is confirmed here
         rather than on a separate Activate button. --}}
    <form method="POST" action="{{ route('manager.vip-members.store') }}"
          data-confirm="This activates the member's plan straight away: their page goes live and the commission split is recorded. Only continue if the joining fee has been received. This cannot be undone."
          data-confirm-title="Confirm payment received?" data-confirm-button="Yes, create & activate">
        @include('manager.vip-members._form')
    </form>
</x-layouts.app>
