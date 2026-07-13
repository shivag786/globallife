@php
    $microsite = $user->vipMicrosite;
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $hours = $microsite->business_hours ?? [];
@endphp
<x-layouts.app title="Edit Profile" heading="Business Profile">
    <form method="POST" action="{{ route('vip.profile.update') }}" enctype="multipart/form-data" class="bootstrap-scope max-w-4xl">
        @csrf
        @method('PUT')

        <div class="d-flex flex-column gap-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Business Name &amp; City</h2>
                    <p class="small text-muted mb-0">Permanent &mdash; part of your public page URL. Contact your Commission Partner to change these.</p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="block text-sm font-medium text-slate-700">Business Name</label>
                            <p class="mt-[0.25rem] text-slate-800">{{ $microsite->business_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="block text-sm font-medium text-slate-700">City</label>
                            <p class="mt-[0.25rem] text-slate-800">{{ $microsite->city->name }}, {{ $microsite->city->state }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Basic Information</h2>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <x-forms.input name="owner_name" label="Owner Name" :value="$microsite->owner_name ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="mobile" label="Mobile Number" :value="$user->mobile ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="business_category" label="Business Category" :value="$microsite->business_category ?? ''" placeholder="e.g. Healthcare, Restaurant, Consulting" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="business_sub_category" label="Business Sub Category" :value="$microsite->business_sub_category ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="establishment_year" type="number" label="Establishment Year" :value="$microsite->establishment_year ?? ''" />
                        </div>
                        <div class="col-12">
                            <x-forms.input name="short_description" label="Short Description" :value="$microsite->short_description ?? ''" help="A one-line tagline shown near your business name." />
                        </div>
                        <div class="col-12">
                            <x-forms.input name="description" label="Full Description" as="textarea" :value="$microsite->description ?? ''" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.input name="gst_no" label="GST No." :value="$microsite->gst_no ?? ''" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.input name="pan_no" label="PAN (Optional)" :value="$microsite->pan_no ?? ''" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.input name="cin_no" label="CIN (Optional)" :value="$microsite->cin_no ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.file-input name="logo" label="Business Logo" :current="$microsite->logo_path" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.file-input name="cover_banner" label="Cover Banner" :current="$microsite->cover_banner_path" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Contact Details</h2>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <x-forms.input name="business_email" type="email" label="Business Email" :value="$microsite->business_email ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="phone_number" label="Phone Number" :value="$microsite->phone_number ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="alternate_number" label="Alternate Number" :value="$microsite->alternate_number ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="whatsapp_number" label="WhatsApp Number" :value="$microsite->whatsapp_number ?? ''" help="Include country code, e.g. 919876543210" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="website_url" type="url" label="Website URL" :value="$microsite->website_url ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="google_map_url" type="url" label="Google Maps Link" :value="$microsite->google_map_url ?? ''" />
                        </div>
                        <div class="col-12">
                            <x-forms.input name="address" label="Business Address" :value="$microsite->address ?? ''" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Business Hours</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        @foreach ($days as $day)
                            <div class="row g-3 align-items-center">
                                <div class="col-md-2 text-capitalize small fw-semibold">{{ $day }}</div>
                                <div class="col-md-3">
                                    <input type="time" name="business_hours[{{ $day }}][open]" value="{{ $hours[$day]['open'] ?? '09:00' }}"
                                           class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <input type="time" name="business_hours[{{ $day }}][close]" value="{{ $hours[$day]['close'] ?? '18:00' }}"
                                           class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="d-flex align-items-center gap-2 small">
                                        <input type="checkbox" name="business_hours[{{ $day }}][closed]" value="1"
                                               @checked($hours[$day]['closed'] ?? false)>
                                        Closed
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Social Media</h2>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach (['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'youtube_url' => 'YouTube', 'linkedin_url' => 'LinkedIn', 'twitter_url' => 'Twitter / X', 'telegram_url' => 'Telegram', 'pinterest_url' => 'Pinterest'] as $field => $label)
                            <div class="col-md-6">
                                <x-forms.input :name="$field" :label="$label.' URL'" :value="$microsite->{$field} ?? ''" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                    Save Profile
                </button>
                <a href="{{ route('vip.modules.edit') }}" class="ms-3 text-sm text-brand-700 hover:underline">Manage Section Visibility &rarr;</a>
            </div>
        </div>
    </form>
</x-layouts.app>
