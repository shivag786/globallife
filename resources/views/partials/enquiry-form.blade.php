@php
    $source = $source ?? 'contact_page';
    $plans = $plans ?? collect();
    $preselectedPlan = $preselectedPlan ?? null;
    $vipMicrositeId = $vipMicrositeId ?? null;
    $prefillCity = $prefillCity ?? null;
    $prefillMessage = $prefillMessage ?? null;
@endphp

@if ($errors->any())
    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded p-3">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ route('enquiry.store') }}"
      class="space-y-4"
      id="enquiryForm-{{ $source }}">

    @csrf

    <input type="hidden" name="source" value="{{ $source }}">

    @if ($vipMicrositeId)
        <input type="hidden" name="vip_microsite_id" value="{{ $vipMicrositeId }}">
    @endif

    {{-- Honeypot --}}
    <div class="hidden" aria-hidden="true">
        <label for="website-{{ $source }}">Website</label>
        <input type="text"
               id="website-{{ $source }}"
               name="website"
               tabindex="-1"
               autocomplete="off">
    </div>

    <div class="grid sm:grid-cols-2 gap-4">

        {{-- Name --}}
        <div>
            <label for="name-{{ $source }}"
                   class="block text-sm font-medium text-slate-700">
                Name
            </label>

            <input id="name-{{ $source }}"
                   type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   minlength="2"
                   maxlength="100"
                   autocomplete="name"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">

            <p class="name-error mt-1 text-sm text-red-600 hidden"></p>
        </div>

        {{-- Email --}}
        <div>
            <label for="email-{{ $source }}"
                   class="block text-sm font-medium text-slate-700">
                Email
            </label>

            <input id="email-{{ $source }}"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   maxlength="254"
                   autocomplete="email"
                   inputmode="email"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">

            <p class="email-error mt-1 text-sm text-red-600 hidden"></p>
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone-{{ $source }}"
                   class="block text-sm font-medium text-slate-700">
                Phone
            </label>

            <input id="phone-{{ $source }}"
                   type="tel"
                   name="phone"
                   value="{{ old('phone') }}"
                   maxlength="10"
                   minlength="10"
                   pattern="[6-9][0-9]{9}"
                   inputmode="numeric"
                   autocomplete="tel"
                   placeholder="Enter 10 digit mobile number"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">

            <p class="phone-error mt-1 text-sm text-red-600 hidden"></p>
        </div>

        {{-- City --}}
        <div>
            <label for="city-{{ $source }}"
                   class="block text-sm font-medium text-slate-700">
                City
            </label>

            <input id="city-{{ $source }}"
                   type="text"
                   name="city"
                   value="{{ old('city', $prefillCity) }}"
                   maxlength="100"
                   autocomplete="address-level2"
                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

    </div>

    {{-- Interested Plan --}}
    @if ($plans->isNotEmpty())
        <div>
            <label for="interested_plan_id-{{ $source }}"
                   class="block text-sm font-medium text-slate-700">
                Interested Plan
                <span class="text-slate-400">(optional)</span>
            </label>

            <select id="interested_plan_id-{{ $source }}"
                    name="interested_plan_id"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">

                <option value="">None / General Enquiry</option>

                @foreach ($plans as $plan)
                    @php
                        $isSelected = old('interested_plan_id')
                            ? (string) old('interested_plan_id') === (string) $plan->id
                            : $preselectedPlan === $plan->slug;
                    @endphp

                    <option value="{{ $plan->id }}" @selected($isSelected)>
                        {{ $plan->name }}
                    </option>
                @endforeach

            </select>
        </div>
    @endif

    {{-- Message --}}
    <div>
        <label for="message-{{ $source }}"
               class="block text-sm font-medium text-slate-700">
            Message
        </label>

        <textarea id="message-{{ $source }}"
                  name="message"
                  rows="4"
                  maxlength="2000"
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('message', $prefillMessage) }}</textarea>
    </div>

    <button type="submit"
            class="bg-brand-700 text-white px-6 py-3 rounded-full font-medium hover:bg-brand-800 transition">
        Send Enquiry
    </button>

</form>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('enquiryForm-{{ $source }}');

    if (!form) return;

    const phone = form.querySelector('[name="phone"]');
    const email = form.querySelector('[name="email"]');
    const name = form.querySelector('[name="name"]');

    const phoneError = form.querySelector('.phone-error');
    const emailError = form.querySelector('.email-error');
    const nameError = form.querySelector('.name-error');


    /* =========================
       PHONE VALIDATION
    ========================= */

    function validatePhone() {

        const value = phone.value.trim();

        // Only digits
        if (!/^[0-9]+$/.test(value)) {
            phoneError.textContent = 'Mobile number can contain digits only.';
            phoneError.classList.remove('hidden');
            return false;
        }

        // Exactly 10 digits + Indian mobile starting 6-9
        if (!/^[6-9][0-9]{9}$/.test(value)) {
            phoneError.textContent =
                'Please enter a valid 10 digit mobile number starting with 6, 7, 8 or 9.';
            phoneError.classList.remove('hidden');
            return false;
        }

        phoneError.textContent = '';
        phoneError.classList.add('hidden');

        return true;
    }


    /* =========================
       EMAIL VALIDATION
    ========================= */

    function validateEmail() {

        const value = email.value.trim();

        /*
         * Basic but strict email validation:
         * abc@example.com
         * name.lastname@example.co.in
         */

        const emailPattern =
            /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$/;

        if (!emailPattern.test(value)) {

            emailError.textContent =
                'Please enter a valid email address, for example name@example.com.';

            emailError.classList.remove('hidden');

            return false;
        }

        // Prevent consecutive dots
        if (value.includes('..')) {

            emailError.textContent =
                'Email address cannot contain consecutive dots.';

            emailError.classList.remove('hidden');

            return false;
        }

        emailError.textContent = '';
        emailError.classList.add('hidden');

        return true;
    }


    /* =========================
       NAME VALIDATION
    ========================= */

    function validateName() {

        const value = name.value.trim();

        if (value.length < 2) {

            nameError.textContent =
                'Name must contain at least 2 characters.';

            nameError.classList.remove('hidden');

            return false;
        }

        // Allow letters, spaces, dot, apostrophe and hyphen
        if (!/^[a-zA-Z\s.'-]+$/.test(value)) {

            nameError.textContent =
                'Name can contain letters, spaces, dot, apostrophe and hyphen only.';

            nameError.classList.remove('hidden');

            return false;
        }

        nameError.textContent = '';
        nameError.classList.add('hidden');

        return true;
    }


    /* =========================
       LIVE PHONE INPUT
    ========================= */

    phone.addEventListener('input', function () {

        // Remove everything except digits
        this.value = this.value.replace(/[^0-9]/g, '');

        // Maximum 10 digits
        this.value = this.value.substring(0, 10);

        // Validate after user has entered something
        if (this.value.length > 0) {
            validatePhone();
        } else {
            phoneError.textContent = '';
            phoneError.classList.add('hidden');
        }
    });


    /* =========================
       EMAIL LIVE VALIDATION
    ========================= */

    email.addEventListener('blur', function () {
        validateEmail();
    });


    /* =========================
       NAME LIVE VALIDATION
    ========================= */

    name.addEventListener('blur', function () {
        validateName();
    });


    /* =========================
       FORM SUBMIT VALIDATION
    ========================= */

    form.addEventListener('submit', function (e) {

        const nameValid = validateName();
        const emailValid = validateEmail();

        let phoneValid = true;

        // Phone is optional in your current form.
        // Validate it only when user enters a number.
        if (phone.value.trim() !== '') {
            phoneValid = validatePhone();
        }

        if (!nameValid || !emailValid || !phoneValid) {

            e.preventDefault();

            // Scroll to first validation error
            const firstError =
                form.querySelector('.text-red-600:not(.hidden)');

            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
    });

});
</script>