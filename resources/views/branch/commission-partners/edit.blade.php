<x-layouts.app title="Edit Commission Partner" heading="Edit Commission Partner">
    {{-- Changes Super Admin made to this partner, with the reason, so they are
         never a surprise. Unread ones are highlighted and can be acknowledged. --}}
    @if ($adminUpdates->isNotEmpty())
        <div id="admin-updates" class="card border-0 shadow-sm mb-6 mw-3xl">
            <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h2 class="h6 fw-semibold mb-0">Updated by Super Admin</h2>
                    <p class="fs-xs text-secondary mb-0">Changes made to this partner from the admin panel.</p>
                </div>
            </div>
            <div class="card-body py-1">
                @foreach ($adminUpdates as $update)
                    <div class="{{ $loop->first ? '' : 'border-top' }}">
                        @include('partials.partner-update-note', [
                            'update' => $update,
                            'showPartner' => false,
                            'canMarkRead' => true,
                        ])
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('branch.commission-partners.update', $partner) }}">
        @include('branch.commission-partners._form')
    </form>

    {{-- Its own form, a sibling of the one above: nesting forms is invalid HTML,
         and a password reset should be deliberate rather than something that
         rides along with a profile save. --}}
    <div class="mw-3xl mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-body">
                <h2 class="h6 mb-0 fw-semibold">Change Password</h2>
                <p class="small text-muted mb-0">
                    Sets a new password for <strong>{{ $partner->name }}</strong>. They are signed out
                    everywhere and must use the new one &mdash; you are not shown the current password,
                    because it is only ever stored hashed.
                </p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('branch.commission-partners.password.update', $partner) }}"
                      data-confirm="Set a new password for {{ $partner->name }}? This signs them out of every device immediately."
                      data-confirm-title="Change Password" data-confirm-button="Yes, change it">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="cp-password" class="form-label small fw-medium text-body-secondary">New Password</label>
                            <input id="cp-password" type="password" name="password" required minlength="8"
                                   autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <p class="fs-sm text-danger mt-1">{{ $message }}</p>
                            @enderror
                            <p class="small text-muted mb-0 mt-1">At least 8 characters.</p>
                        </div>
                        <div class="col-md-6">
                            <label for="cp-password-confirm" class="form-label small fw-medium text-body-secondary">Confirm New Password</label>
                            <input id="cp-password-confirm" type="password" name="password_confirmation" required
                                   minlength="8" autocomplete="new-password" class="form-control">
                            <p class="small text-muted mb-0 mt-1">Type it again &mdash; there is no login attempt to catch a typo.</p>
                        </div>
                    </div>

                    <button type="submit"
                            class="btn btn-brand btn-sm mt-4">
                        Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
