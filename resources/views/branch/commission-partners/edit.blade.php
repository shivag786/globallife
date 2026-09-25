<x-layouts.app title="Edit Commission Partner" heading="Edit Commission Partner">
    <form method="POST" action="{{ route('branch.commission-partners.update', $partner) }}">
        @include('branch.commission-partners._form')
    </form>

    {{-- Its own form, a sibling of the one above: nesting forms is invalid HTML,
         and a password reset should be deliberate rather than something that
         rides along with a profile save. --}}
    <div class="bootstrap-scope max-w-3xl mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
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
                            <label for="cp-password" class="form-label small fw-medium text-slate-700">New Password</label>
                            <input id="cp-password" type="password" name="password" required minlength="8"
                                   autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="small text-muted mb-0 mt-1">At least 8 characters.</p>
                        </div>
                        <div class="col-md-6">
                            <label for="cp-password-confirm" class="form-label small fw-medium text-slate-700">Confirm New Password</label>
                            <input id="cp-password-confirm" type="password" name="password_confirmation" required
                                   minlength="8" autocomplete="new-password" class="form-control">
                            <p class="small text-muted mb-0 mt-1">Type it again &mdash; there is no login attempt to catch a typo.</p>
                        </div>
                    </div>

                    <button type="submit"
                            class="mt-4 bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                        Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
