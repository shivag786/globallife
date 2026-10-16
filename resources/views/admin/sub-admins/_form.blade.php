@csrf
@isset($subAdmin) @method('PUT') @endisset

<div class="bootstrap-scope" style="max-width: 48rem">
    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0 fw-semibold">Sub Admin Details</h2>
                <p class="small text-muted mb-0">
                    What they can actually reach is set separately, on the Permissions screen.
                </p>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-forms.input name="name" label="Name" :value="$subAdmin->name ?? ''" required />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="mobile" label="Mobile Number" :value="$subAdmin->mobile ?? ''"
                                       data-mobile-input type="tel" inputmode="numeric" maxlength="10"
                                       autocomplete="tel-national" placeholder="9876543210"
                                       help="10 digits. A +91 or leading 0 is trimmed automatically." />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="email" type="email" label="Email" :value="$subAdmin->email ?? ''" required />
                    </div>
                    @unless (isset($subAdmin))
                        <div class="col-md-6">
                            <x-forms.input name="password" type="password" label="Password" required
                                           help="At least 8 characters." />
                        </div>
                    @endunless
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">
                {{ isset($subAdmin) ? 'Update Sub Admin' : 'Create Sub Admin' }}
            </button>
            <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-link text-muted">Cancel</a>
        </div>
    </div>
</div>
