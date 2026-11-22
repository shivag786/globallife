<x-layouts.app title="Site Settings" heading="Site Settings">
    <p class="fs-sm text-secondary mb-6">Controls the logo, title, SEO, analytics, and social sharing details used across the public site.</p>

    {{-- Where uploaded images are stored, and whether a deploy can delete them.
         Readable from the browser, so it can be checked without server access. --}}
    <div class="card shadow-sm mb-6 mw-4xl {{ $uploads['safe'] ? 'border-0' : 'border-danger border-2' }}">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3">
                <span class="badge rounded-pill {{ $uploads['safe'] ? 'text-bg-success' : 'text-bg-danger' }} mt-1">
                    {{ $uploads['safe'] ? 'Safe' : 'At risk' }}
                </span>
                <div class="min-w-0">
                    <h2 class="h6 fw-semibold mb-1">Uploaded images storage</h2>
                    <p class="fs-sm mb-3">{{ $uploads['reason'] }}</p>
                    <dl class="d-grid grid-cols-sm-2 column-gap-6 row-gap-2 fs-sm mb-0">
                        <div>
                            <dt class="fs-xs text-secondary text-uppercase tracking-wide">Uploads folder</dt>
                            <dd class="font-monospace text-break mb-0">{{ $uploads['root'] }}</dd>
                        </div>
                        <div>
                            <dt class="fs-xs text-secondary text-uppercase tracking-wide">Deploy folder</dt>
                            <dd class="font-monospace text-break mb-0">{{ $uploads['deploy_dir'] }}</dd>
                        </div>
                        <div>
                            <dt class="fs-xs text-secondary text-uppercase tracking-wide">Set by</dt>
                            <dd class="mb-0">{{ $uploads['explicit'] ? 'FILESYSTEM_PUBLIC_ROOT in .env' : 'Automatic (beside the deploy folder)' }}</dd>
                        </div>
                        <div>
                            <dt class="fs-xs text-secondary text-uppercase tracking-wide">Environment</dt>
                            <dd class="mb-0">
                                {{ $uploads['environment'] }} &middot; PHP {{ $uploads['php'] }}
                                @if ($uploads['config_cached']) &middot; config cached @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mw-4xl">
        @csrf
        @method('PUT')

        <div class="d-flex flex-column gap-4">

            {{-- General --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body">
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
                                                 help="Full logo (used on payslips and other print pages). PNG or SVG with transparent background works best. Max 2MB." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.file-input name="site_logo_icon" label="Header logo icon (emblem only)" :current="$settings['site_logo_icon'] ?? null"
                                                 help="Just the emblem, without the company name. Shown large in the website and dashboard headers, with the Site Title written beside it. Leave empty to use the built-in GLW emblem. Max 1MB." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.file-input name="favicon" label="Favicon" :current="$settings['favicon'] ?? null"
                                                 help="Square image, ideally 512×512px. Max 1MB." />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Typography --}}
            @php
                $fontGroups = \App\Services\TypographyService::groupedOptions();
                $fontNow = app(\App\Services\TypographyService::class)->current();
                $fontFields = [
                    'font_body' => ['Body Font', 'body', 'Paragraphs, forms, tables and menus — public site and every dashboard.'],
                    'font_heading' => ['Heading Font', 'heading', 'Section titles, cards and dashboard headings.'],
                    'font_display' => ['Display Font', 'display', 'Large hero titles, logo text and article headings.'],
                ];
            @endphp
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body">
                    <h2 class="h6 mb-0 fw-semibold">Typography</h2>
                    <p class="small text-muted mb-0">Fonts used across the public site, microsites, shop and all dashboards. Loaded from Google Fonts.</p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach ($fontFields as $key => [$label, $slot, $help])
                            <div class="col-md-6">
                                <x-forms.input :name="$key" :label="$label" as="select" :help="$help">
                                    @foreach ($fontGroups as $group => $names)
                                        <optgroup label="{{ $group }}">
                                            @foreach ($names as $name)
                                                <option value="{{ $name }}" @selected(old($key, $fontNow[$slot]) === $name)>{{ $name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </x-forms.input>
                            </div>
                        @endforeach
                        <div class="col-md-6">
                            <x-forms.input name="font_size_base" label="Base Font Size" as="select"
                                           help="Scales body text everywhere. 16px is the browser default.">
                                @foreach (range(\App\Services\TypographyService::MIN_SIZE, \App\Services\TypographyService::MAX_SIZE) as $px)
                                    <option value="{{ $px }}" @selected((int) old('font_size_base', $fontNow['size']) === $px)>{{ $px }}px{{ $px === \App\Services\TypographyService::DEFAULT_SIZE ? ' (default)' : '' }}</option>
                                @endforeach
                            </x-forms.input>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Homepage & Announcement --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body">
                    <h2 class="h6 mb-0 fw-semibold">Homepage &amp; Announcement</h2>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-8">
                            <x-forms.input name="announcement_text" label="Announcement Bar Text" :value="$settings['announcement_text'] ?? ''"
                                           help="Shown in a thin bar at the very top of the public site. Leave blank to hide it." />
                        </div>
                        <div class="col-md-4">
                            <x-forms.input name="announcement_link" label="Announcement Link" :value="$settings['announcement_link'] ?? ''"
                                           placeholder="/products or https://…"
                                           help="A page on this site (e.g. /products) or a full URL." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="hero_rating" label="Hero Rating" :value="$settings['hero_rating'] ?? ''"
                                           placeholder="4.8" help="Shows a star-rating chip in the hero. Leave blank to hide." />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="hero_rating_count" label="Hero Rating Count" :value="$settings['hero_rating_count'] ?? ''"
                                           placeholder="2,500+" help="e.g. shown as “2,500+ happy customers”." />
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body">
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
                <div class="card-header bg-body">
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
                <div class="card-header bg-body">
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
                <div class="card-header bg-body">
                    <h2 class="h6 mb-0 fw-semibold">Contact</h2>
                    <p class="small text-muted mb-0">Powers the footer and contact links across the public site.</p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <x-forms.input name="contact_email" type="email" label="Contact Email" :value="$settings['contact_email'] ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="contact_email_hr" type="email" label="HR Email" :value="$settings['contact_email_hr'] ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="contact_whatsapp" label="WhatsApp / Phone Number" :value="$settings['contact_whatsapp'] ?? ''" />
                        </div>
                        <div class="col-12">
                            <x-forms.input name="contact_address" label="Address" :value="$settings['contact_address'] ?? ''" />
                        </div>
                        <div class="col-md-6">
                            <x-forms.input name="fssai_license" label="FSSAI License No. (shown in the footer)" :value="$settings['fssai_license'] ?? ''" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivery --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body">
                    <h2 class="h6 mb-0 fw-semibold">Delivery</h2>
                    <p class="small text-muted mb-0">The global delivery promise shown on every product page ("Delivery by …") and used as the default estimated delivery date on new orders.</p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <x-forms.input name="delivery_days" type="number" label="Delivery Window (days)"
                                           :value="$settings['delivery_days'] ?? \App\Services\DeliveryService::DEFAULT_DAYS"
                                           placeholder="5" />
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="btn btn-brand btn-sm">
                    Save Settings
                </button>
            </div>
        </div>
    </form>
</x-layouts.app>
