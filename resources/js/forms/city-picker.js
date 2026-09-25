/**
 * Cascading state → city picker with inline creation.
 *
 * Pick a state, then a city from that state. If the city is not listed, choose
 * "Other" and type it — and likewise for a state that has no cities yet. Each
 * chosen city becomes a chip backed by a hidden input, so the form still posts
 * plain arrays and works identically whether or not JS ran:
 *
 *   cities[]                  existing city ids
 *   new_cities[N][name/state] typed-in cities the server find-or-creates
 *
 * Duplicates are refused client-side (by id, or by name+state for new ones) so a
 * partner cannot be assigned the same city twice.
 */
export function initCityPicker() {
    document.querySelectorAll('[data-city-picker]').forEach((root) => {
        if (root.dataset.cityPicker === 'single') {
            setupSingle(root);
        } else {
            setup(root);
        }
    });
}

/**
 * Single-select variant: the same cascade, but one city.
 *
 * The city <select> is named city_id and its "Other" option carries an empty
 * value, so choosing it posts no id and the typed name/state are used instead —
 * no disabling, and a no-JS submit still posts something coherent.
 */
function setupSingle(root) {
    const byState = JSON.parse(root.dataset.cities || '{}');

    const stateSelect = root.querySelector('[data-city-state]');
    const stateInput = root.querySelector('[data-city-state-new]');
    const citySelect = root.querySelector('[data-city-select]');
    const cityInput = root.querySelector('[data-city-name-new]');
    const stateValue = root.querySelector('[data-city-state-value]');

    const show = (el, visible) => el?.classList.toggle('hidden', !visible);
    const isCustomState = () => stateSelect.value === OTHER;
    const currentState = () => (isCustomState() ? stateInput.value : stateSelect.value).trim();

    /** Only meaningful when the chosen option is the "Other" one. */
    const typingCity = () => Boolean(citySelect.selectedOptions[0]?.dataset.other);

    function refreshCities() {
        const custom = isCustomState();
        show(stateInput, custom);

        const list = custom ? [] : byState[stateSelect.value] || [];

        citySelect.innerHTML = '';
        citySelect.add(new Option(stateSelect.value === '' ? 'Select a state first…' : 'Select a city…', ''));
        list.forEach((city) => citySelect.add(new Option(city.name, String(city.id))));

        const other = new Option('Other — type the city name…', '');
        other.dataset.other = '1';
        citySelect.add(other);

        // A hand-typed state can only ever produce a hand-typed city.
        if (custom) citySelect.selectedIndex = citySelect.options.length - 1;

        citySelect.disabled = stateSelect.value === '';
        refreshCityInput();
    }

    function refreshCityInput() {
        const typing = typingCity();
        show(cityInput, typing);
        if (!typing) cityInput.value = '';
        if (stateValue) stateValue.value = currentState();
    }

    stateSelect?.addEventListener('change', refreshCities);
    citySelect?.addEventListener('change', refreshCityInput);
    stateInput?.addEventListener('input', () => {
        if (stateValue) stateValue.value = currentState();
    });

    refreshCities();

    // Re-select whatever survived a failed submit.
    const keepCity = cityInput?.value.trim();
    if (keepCity) {
        citySelect.selectedIndex = citySelect.options.length - 1;
        refreshCityInput();
        cityInput.value = keepCity;
    }
}

const OTHER = '__other__';

function setup(root) {
    const byState = JSON.parse(root.dataset.cities || '{}');

    const stateSelect = root.querySelector('[data-city-state]');
    const stateInput = root.querySelector('[data-city-state-new]');
    const citySelect = root.querySelector('[data-city-select]');
    const cityInput = root.querySelector('[data-city-name-new]');
    const addButton = root.querySelector('[data-city-add]');
    const chips = root.querySelector('[data-city-chips]');
    const empty = root.querySelector('[data-city-empty]');
    const error = root.querySelector('[data-city-error]');

    let newIndex = 0;

    const show = (el, visible) => el?.classList.toggle('hidden', !visible);

    function fail(message) {
        if (!error) return;
        error.textContent = message;
        show(error, true);
    }

    function clearError() {
        show(error, false);
    }

    /** A state typed by hand has no known cities, so only "Other" applies. */
    function isCustomState() {
        return stateSelect.value === OTHER;
    }

    function currentState() {
        return (isCustomState() ? stateInput.value : stateSelect.value).trim();
    }

    function refreshCities() {
        const custom = isCustomState();
        show(stateInput, custom);

        const list = custom ? [] : byState[stateSelect.value] || [];

        citySelect.innerHTML = '';
        const placeholder = new Option(
            stateSelect.value === '' ? 'Select a state first…' : 'Select a city…',
            ''
        );
        citySelect.add(placeholder);

        list.forEach((city) => citySelect.add(new Option(city.name, String(city.id))));

        // Always offer the escape hatch, even when the state has cities.
        citySelect.add(new Option('Other — type the city name…', OTHER));

        // A hand-typed state can only ever produce a hand-typed city.
        if (custom) {
            citySelect.value = OTHER;
        }

        citySelect.disabled = stateSelect.value === '';
        refreshCityInput();
    }

    function refreshCityInput() {
        show(cityInput, citySelect.value === OTHER);
    }

    function alreadyChosen(id, name, state) {
        if (id) {
            return !!chips.querySelector(`[data-chip-city-id="${id}"]`);
        }
        const key = `${name}|${state}`.toLowerCase();
        return [...chips.querySelectorAll('[data-chip-key]')].some(
            (chip) => chip.dataset.chipKey === key
        );
    }

    function addChip({ id, name, state }) {
        const chip = document.createElement('span');
        chip.className =
            'inline-flex items-center gap-2 bg-brand-50 text-brand-800 border border-brand-200 rounded-full pl-3 pr-1 py-1 text-sm';

        if (id) {
            chip.dataset.chipCityId = String(id);
        } else {
            chip.dataset.chipKey = `${name}|${state}`.toLowerCase();
        }

        const label = document.createElement('span');
        label.textContent = id ? `${name}, ${state}` : `${name}, ${state} (new)`;
        chip.appendChild(label);

        if (id) {
            chip.appendChild(hidden('cities[]', String(id)));
        } else {
            const i = newIndex++;
            chip.appendChild(hidden(`new_cities[${i}][name]`, name));
            chip.appendChild(hidden(`new_cities[${i}][state]`, state));
        }

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className =
            'w-5 h-5 rounded-full text-brand-600 hover:bg-brand-200 hover:text-brand-900 leading-none';
        remove.innerHTML = '&times;';
        remove.setAttribute('aria-label', `Remove ${name}`);
        remove.addEventListener('click', () => {
            chip.remove();
            refreshEmpty();
        });
        chip.appendChild(remove);

        chips.appendChild(chip);
        refreshEmpty();
    }

    function hidden(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }

    function refreshEmpty() {
        show(empty, chips.children.length === 0);
    }

    addButton?.addEventListener('click', () => {
        clearError();

        const state = currentState();
        if (state === '') {
            fail('Choose a state first.');
            return;
        }

        const typedCity = citySelect.value === OTHER;
        const name = typedCity ? cityInput.value.trim() : citySelect.selectedOptions[0]?.text;
        const id = typedCity ? null : citySelect.value;

        if (typedCity && name === '') {
            fail('Type the city name.');
            cityInput.focus();
            return;
        }

        if (!typedCity && (id === '' || !id)) {
            fail('Choose a city, or pick "Other" to type one.');
            return;
        }

        if (alreadyChosen(id, name, state)) {
            fail(`${name} is already in the list.`);
            return;
        }

        addChip({ id: id || null, name, state });

        // Leave the state selected — adding several cities in one state is the
        // common case — but reset the city so the next pick starts clean.
        cityInput.value = '';
        citySelect.value = '';
        refreshCityInput();
    });

    stateSelect?.addEventListener('change', () => {
        clearError();
        refreshCities();
    });
    citySelect?.addEventListener('change', () => {
        clearError();
        refreshCityInput();
    });

    // Enter inside either text box should add, not submit the whole form.
    [stateInput, cityInput].forEach((input) => {
        input?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                addButton?.click();
            }
        });
    });

    refreshCities();
    refreshEmpty();
}
