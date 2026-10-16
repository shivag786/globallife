@csrf
@isset($member) @method('PUT') @endisset

<div class="mw-6xl">
    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-body">
                <h2 class="h6 mb-0 fw-semibold">Account Details</h2>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <x-forms.input name="name" label="Name" :value="$member->name ?? ''" required />
                    </div>
                    <div class="col-md-6">
                        <x-forms.input name="email" type="email" label="Email" :value="$member->email ?? ''" required />
                    </div>
                    @unless (isset($member))
                        <div class="col-md-6">
                            <x-forms.input name="password" type="password" label="Password" required />
                        </div>
                    @endunless
                </div>
            </div>
        </div>

        {{-- The same packages, and the same cards, the partner sees when renewing. --}}
        <div>
            <h2 class="font-display fs-lg fw-bold text-brand-emphasis">Package</h2>
            <p class="fs-sm text-secondary mt-1 mb-4">
                Choose the package this member has paid for. It sets how long their page stays live and how
                many products and services they may list.
            </p>

            @error('vip_plan_id')
                <p class="fs-sm text-danger mb-3">{{ $message }}</p>
            @enderror

            @php $selectedPlan = old('vip_plan_id', $member->vipMicrosite->vip_plan_id ?? null); @endphp
            <div class="d-grid grid-cols-sm-2 grid-cols-xl-4 gap-6 align-items-stretch">
                @foreach ($plans as $plan)
                    <x-package-card :package="$plan" selectable
                                    :checked="(int) $selectedPlan === $plan->id" />
                @endforeach
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-body">
                <h2 class="h6 mb-0 fw-semibold">Business / Microsite</h2>
                @if (isset($member))
                    <p class="small text-muted mb-0">Business name and city are permanent once created &mdash; they're part of the public page URL.</p>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @if (isset($member))
                        <div class="col-md-6">
                            <label class="d-block fs-sm fw-medium text-body-secondary">Business Name</label>
                            <p class="text-body">{{ $member->vipMicrosite->business_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fs-sm fw-medium text-body-secondary">City</label>
                            <p class="text-body">{{ $member->vipMicrosite->city->name }}, {{ $member->vipMicrosite->city->state }}</p>
                        </div>
                    @else
                        <div class="col-md-6">
                            <x-forms.input name="business_name" label="Business Name" required />
                        </div>
                        <div class="col-12">
                            <x-city-picker single :states="$states" :cities-by-state="$citiesByState" />
                            <p class="small text-muted mt-2 mb-0">
                                Pick the state, then the city. If the city isn't listed, choose
                                &ldquo;Other&rdquo; and type it &mdash; it will be created and added to the
                                cities you serve.
                            </p>
                        </div>
                    @endif
                    <div class="col-12">
                        <x-forms.input name="description" label="Business Description" as="textarea"
                                       :value="$member->vipMicrosite->description ?? ''"
                                       help="Shown on the VIP member's public business page." />
                    </div>
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-brand btn-sm">
                {{ isset($member) ? 'Update VIP Member' : 'Create VIP Member' }}
            </button>
        </div>
    </div>
</div>
