@csrf
@isset($member) @method('PUT') @endisset

<div class="bootstrap-scope max-w-3xl">
    <div class="d-flex flex-column gap-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
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
                    <div class="col-md-6">
                        <x-forms.input name="vip_plan_id" label="VIP Plan" as="select" :value="$member->vipMicrosite->vip_plan_id ?? ''" required>
                            <option value="">Select a plan&hellip;</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('vip_plan_id', $member->vipMicrosite->vip_plan_id ?? '') == $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </x-forms.input>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0 fw-semibold">Business / Microsite</h2>
                @if (isset($member))
                    <p class="small text-muted mb-0">Business name and city are permanent once created &mdash; they're part of the public page URL.</p>
                @endif
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @if (isset($member))
                        <div class="col-md-6">
                            <label class="block text-sm font-medium text-slate-700">Business Name</label>
                            <p class="mt-[0.25rem] text-slate-800">{{ $member->vipMicrosite->business_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="block text-sm font-medium text-slate-700">City</label>
                            <p class="mt-[0.25rem] text-slate-800">{{ $member->vipMicrosite->city->name }}, {{ $member->vipMicrosite->city->state }}</p>
                        </div>
                    @else
                        <div class="col-md-6">
                            <x-forms.input name="business_name" label="Business Name" required />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="city_id" label="City" as="select" required>
                                <option value="">Select a city&hellip;</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" @selected(old('city_id') == $city->id)>{{ $city->name }}, {{ $city->state }}</option>
                                @endforeach
                            </x-forms.input>
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
            <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                {{ isset($member) ? 'Update VIP Member' : 'Create VIP Member' }}
            </button>
        </div>
    </div>
</div>
