{{--
    Read-only view of a member's personal / address / nominee / KYC details.
    KYC numbers are masked; files open through the authorised kyc.file route.

    @param \App\Models\User $member
--}}
@php
    $profile = $member->profile;
    $row = fn (string $label, $value) => ['label' => $label, 'value' => $value ?: '—'];
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-body">
        <h2 class="h6 fw-semibold mb-0">Profile &amp; KYC</h2>
        <p class="fs-xs text-secondary mb-0">Entered by their manager. Read-only here.</p>
    </div>
    <div class="card-body">
        @if (! $profile)
            <p class="fs-sm text-secondary mb-0">No personal or KYC details on record yet.</p>
        @else
            <div class="d-flex flex-column flex-sm-row gap-4">
                @if ($profile->hasFile('photo'))
                    <img src="{{ route('kyc.file', [$member, 'photo']) }}" alt="Passport photo"
                         class="rounded-3 border object-fit-cover flex-shrink-0" style="width: 7rem; aspect-ratio: 35 / 45">
                @endif

                <dl class="d-grid grid-cols-sm-2 column-gap-6 row-gap-3 fs-sm mb-0 flex-grow-1">
                    @foreach ([
                        $row('Father / Spouse', $profile->relationLine()),
                        $row('Gender', \App\Models\UserProfile::GENDERS[$profile->gender] ?? null),
                        $row('Date of birth', $profile->date_of_birth?->format('d M Y').($profile->age() !== null ? ' ('.$profile->age().')' : '')),
                        $row('Blood group', $profile->blood_group),
                        $row('Alternate mobile', $profile->alternate_mobile),
                        $row('Address', collect([$profile->address_line1, $profile->address_line2, $profile->city, $profile->state, $profile->pincode])->filter()->implode(', ')),
                        $row('Nominee', $profile->nominee_name
                            ? $profile->nominee_name.($profile->nominee_relation ? ' ('.(\App\Models\UserProfile::NOMINEE_RELATIONS[$profile->nominee_relation] ?? $profile->nominee_relation).')' : '')
                            : null),
                        $row('Nominee mobile', $profile->nominee_mobile),
                        $row('Aadhaar', $profile->maskedAadhaar()),
                        $row('PAN', $profile->maskedPan()),
                    ] as $item)
                        <div>
                            <dt class="fs-xs text-secondary text-uppercase tracking-wide">{{ $item['label'] }}</dt>
                            <dd class="mb-0 {{ in_array($item['label'], ['Aadhaar', 'PAN']) ? 'font-monospace' : '' }}">{{ $item['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            @php $docs = collect(['aadhaar_file', 'pan_file'])->filter(fn ($k) => $profile->hasFile($k)); @endphp
            @if ($docs->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mt-4">
                    @foreach ($docs as $kind)
                        <a href="{{ route('kyc.file', [$member, $kind]) }}" target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline-secondary">
                            View {{ \App\Models\UserProfile::FILE_LABELS[$kind] }}
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>
