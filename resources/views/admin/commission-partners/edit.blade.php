<x-layouts.app title="Edit Commission Partner" heading="Edit Commission Partner">
    <div class="mw-3xl d-flex flex-column gap-6">
        <div class="alert alert-info fs-sm mb-0">
            @if ($partner->creator)
                This partner is managed by <strong>{{ $partner->creator->name }}</strong>.
                Saving a change leaves them a note on their dashboard showing what you changed and why.
            @else
                This partner has no Branch Manager, so no one is notified of changes.
            @endif
        </div>

        <form method="POST" action="{{ route('admin.commission-partners.update', $partner) }}"
              class="card border-0 shadow-sm">
            @csrf
            @method('PUT')

            <div class="card-header bg-body">
                <h2 class="h6 fw-semibold mb-0">Partner Details</h2>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-forms.input name="name" label="Name" :value="$partner->name" required />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="email" type="email" label="Email" :value="$partner->email" required />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="mobile" label="Mobile Number" :value="$partner->mobile"
                                       data-mobile-input type="tel" inputmode="numeric" maxlength="10"
                                       autocomplete="tel-national" placeholder="9876543210"
                                       help="10 digits. A +91 or leading 0 is trimmed automatically." />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="commission_percentage" type="number" label="Commission Percentage"
                                       :value="$partner->commission_percentage" step="0.01" min="0" :max="$cap" required
                                       :help="'Their share of each VIP activation. At most '.rtrim(rtrim(number_format($cap, 2), '0'), '.').'% — '.($partner->creator ? $partner->creator->name.'\'s' : 'the Branch Manager\'s').' own share, which it comes out of.'" />
                    </div>
                    <div class="col-12">
                        <x-forms.input name="note" as="textarea" label="Reason for the change (optional)"
                                       placeholder="e.g. Rate revised after the September performance review"
                                       help="Shown to the Branch Manager alongside the change." />
                    </div>
                </div>
            </div>
            <div class="card-footer bg-body d-flex gap-2">
                <button type="submit" class="btn btn-brand">Save Changes</button>
                <a href="{{ route('admin.commission-partners.index') }}" class="btn btn-link text-secondary">Back to list</a>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-body">
                <h2 class="h6 fw-semibold mb-0">Change History</h2>
                <p class="fs-xs text-secondary mb-0">Every edit made from this screen, newest first.</p>
            </div>
            <div class="card-body pt-1">
                @forelse ($history as $update)
                    <div class="{{ $loop->first ? '' : 'border-top' }}">
                        @include('partials.partner-update-note', [
                            'update' => $update,
                            'showPartner' => false,
                            'unreadLabel' => 'Not seen by the Branch Manager yet',
                        ])
                        @if ($update->isRead())
                            <p class="fs-xs text-success mb-2 ms-12">Seen by the Branch Manager {{ $update->read_at->diffForHumans() }}</p>
                        @endif
                    </div>
                @empty
                    <p class="fs-sm text-secondary py-3 mb-0">No changes made by Super Admin yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
