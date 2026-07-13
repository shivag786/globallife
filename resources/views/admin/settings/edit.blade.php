<x-layouts.app title="Site Settings" heading="Site Settings">
    <p class="text-sm text-slate-500 mb-6">Controls the logo, title, SEO, analytics, and social sharing details used across the public site.</p>

    {{-- .bootstrap-scope activates Bootstrap's real (unprefixed) classes for this
         subtree only — see resources/css/bootstrap.scss / scripts/build-bootstrap.mjs. --}}
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="bootstrap-scope max-w-4xl">
        @csrf
        @method('PUT')

        <div class="d-flex flex-column gap-4">

            {{-- General --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">General</h2>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <x-forms.input name="site_title" label="Site Title" :value="$settings['site_title'] ?? ''"
                                           help="Shown in the browser tab and as the default page title." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="site_tagline" label="Tagline" :value="$settings['site_tagline'] ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.file-input name="site_logo" label="Logo" :current="$settings['site_logo'] ?? null"
                                                 help="PNG or SVG with transparent background works best. Max 2MB." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.file-input name="favicon" label="Favicon" :current="$settings['favicon'] ?? null"
                                                 help="Square image, ideally 512×512px. Max 1MB." />
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">SEO</h2>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-12">
                            <x-forms.input name="meta_description" label="Meta Description" as="textarea"
                                           :value="$settings['meta_description'] ?? ''"
                                           help="Recommended: under 160 characters." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="meta_keywords" label="Meta Keywords" :value="$settings['meta_keywords'] ?? ''"
                                           help="Comma-separated. Optional — most search engines ignore this today." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="canonical_url" type="url" label="Canonical URL"
                                           :value="$settings['canonical_url'] ?? ''" placeholder="https://example.com" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="robots" label="Search Engine Indexing" as="select" :value="$settings['robots'] ?? 'index'">
                                <option value="index" @selected(old('robots', $settings['robots'] ?? 'index') === 'index')>Index (default — allow search engines)</option>
                                <option value="noindex" @selected(old('robots', $settings['robots'] ?? '') === 'noindex')>No Index (hide from search engines)</option>
                            </x-forms.input>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Social & Open Graph --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Social &amp; Open Graph</h2>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <x-forms.input name="og_title" label="OG Title" :value="$settings['og_title'] ?? ''"
                                           help="Falls back to Site Title when left blank." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="twitter_site" label="Twitter / X Handle" :value="$settings['twitter_site'] ?? ''" placeholder="@globallife" />
                        </div>
                        <div class="col-12">
                            <x-forms.input name="og_description" label="OG Description" as="textarea"
                                           :value="$settings['og_description'] ?? ''"
                                           help="Falls back to Meta Description when left blank." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.file-input name="og_image" label="OG / Share Image" :current="$settings['og_image'] ?? null"
                                                 help="Recommended: 1200×630px. Used when the site is shared on social media." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="twitter_card" label="Twitter Card Type" as="select" :value="$settings['twitter_card'] ?? 'summary_large_image'">
                                <option value="summary_large_image" @selected(old('twitter_card', $settings['twitter_card'] ?? 'summary_large_image') === 'summary_large_image')>Summary with Large Image</option>
                                <option value="summary" @selected(old('twitter_card', $settings['twitter_card'] ?? '') === 'summary')>Summary</option>
                            </x-forms.input>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-4">
                        @foreach (['social_facebook' => 'Facebook', 'social_instagram' => 'Instagram', 'social_youtube' => 'YouTube', 'social_linkedin' => 'LinkedIn'] as $key => $label)
                            <div class="col-md-6">
                                <x-forms.input :name="$key" :label="$label.' URL'" :value="$settings[$key] ?? ''" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Analytics --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Analytics</h2>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <x-forms.input name="ga_measurement_id" label="Google Analytics (GA4) Measurement ID"
                                           :value="$settings['ga_measurement_id'] ?? ''" placeholder="G-XXXXXXXXXX" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="gtm_id" label="Google Tag Manager Container ID"
                                           :value="$settings['gtm_id'] ?? ''" placeholder="GTM-XXXXXXX" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 fw-semibold">Contact</h2>
                    <p class="small text-muted mb-0">Powers the footer and contact links across the public site.</p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <x-forms.input name="contact_email" type="email" label="Contact Email" :value="$settings['contact_email'] ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="contact_whatsapp" label="WhatsApp / Phone Number" :value="$settings['contact_whatsapp'] ?? ''" />
                        </div>
                        <div class="col-12">
                            <x-forms.input name="contact_address" label="Address" :value="$settings['contact_address'] ?? ''" />
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="bg-brand-700 text-white text-sm px-[1.25rem] py-2.5 rounded-md hover:bg-brand-800 transition font-medium">
                    Save Settings
                </button>
            </div>
        </div>
    </form>
</x-layouts.app>
