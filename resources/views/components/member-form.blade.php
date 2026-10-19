@props([
    // [['id' => 'section-account', 'title' => 'Account', 'icon' => 'user'], ...]
    'sections' => [],
])

{{--
    A long form split into collapsible sections, with a rail on the left that
    jumps to (and opens) each one. The rail's dot fills once a section has
    something in it and turns red if it holds a validation error, so a problem
    in a collapsed section is never hidden. Behaviour: resources/js/forms/member-form.js
--}}
<div class="member-form" data-member-form>
    <nav class="member-form-rail" aria-label="Form sections">
        <div class="member-form-rail-inner">
            @foreach ($sections as $section)
                <button type="button" class="member-rail-item" data-rail-target="{{ $section['id'] }}">
                    <x-icon :name="$section['icon']" class="w-4 h-4 flex-shrink-0" />
                    <span class="text-truncate">{{ $section['title'] }}</span>
                    <span class="member-rail-dot" data-rail-dot="{{ $section['id'] }}"></span>
                </button>
            @endforeach

            <div class="member-rail-actions">
                <button type="button" class="btn btn-link btn-sm p-0" data-sections-toggle="open">Expand all</button>
                <span class="text-secondary">&middot;</span>
                <button type="button" class="btn btn-link btn-sm p-0" data-sections-toggle="close">Collapse all</button>
            </div>
        </div>
    </nav>

    <div class="member-form-body">
        {{ $slot }}
    </div>
</div>
