/**
 * Behaviour for x-member-form (the Branch Manager / Commission Partner forms).
 *
 *  - The rail jumps to a section and opens it; its dot shows whether the
 *    section has anything in it, and turns red if it holds a validation error.
 *  - Sections with errors open themselves on load, so nothing is hidden.
 *  - Date of birth fills the age; Aadhaar is spaced as typed; PAN is upper-cased.
 *  - Mobile fields show a live "7/10" counter.
 *  - Uploads preview immediately, and a large phone photo is shrunk in the
 *    browser before it is sent, so "any size" works within the 8 MB limit.
 *
 * Everything here is progressive: with JavaScript off the form still submits,
 * sections are open by default, and the server validates every field.
 */

const collapseOf = (el) => window.bootstrap?.Collapse.getOrCreateInstance(el, { toggle: false });

export function initMemberForm() {
    document.querySelectorAll('[data-member-form]').forEach(setupForm);
    setupMobileCounters();
    setupDob();
    setupAadhaar();
    setupPan();
    setupUploads();
}

/* ------------------------------------------------------------ rail + dots */

function setupForm(root) {
    const sections = [...root.querySelectorAll('[data-member-section]')];
    const items = [...root.querySelectorAll('[data-rail-target]')];

    const bodyOf = (section) => section.querySelector('.collapse');

    const hasValue = (section) =>
        [...section.querySelectorAll('input, select, textarea')].some((el) => {
            if (el.type === 'hidden' || el.type === 'checkbox' || el.type === 'radio') return false;
            if (el.type === 'file') return el.files && el.files.length > 0;
            if (el.disabled) return false;
            return String(el.value || '').trim() !== '';
        }) || [...section.querySelectorAll('[data-upload-preview]')].some((img) => !img.classList.contains('d-none'));

    const refreshDots = () => {
        sections.forEach((section) => {
            const dot = root.querySelector(`[data-rail-dot="${section.dataset.memberSection}"]`);
            if (!dot) return;
            const error = section.querySelector('.is-invalid, .invalid-feedback') !== null;
            dot.classList.toggle('is-error', error);
            dot.classList.toggle('is-filled', !error && hasValue(section));
        });
    };

    // A section holding a server-side error opens itself and is outlined.
    const erroring = sections.filter((s) => s.querySelector('.is-invalid, .invalid-feedback'));
    erroring.forEach((section) => {
        section.classList.add('has-error');
        collapseOf(bodyOf(section))?.show();
        section.querySelector('.member-section-toggle')?.classList.remove('collapsed');
    });
    if (erroring.length) {
        erroring[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    items.forEach((item) => {
        item.addEventListener('click', () => {
            const section = root.querySelector(`[data-member-section="${item.dataset.railTarget}"]`);
            if (!section) return;
            collapseOf(bodyOf(section))?.show();
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    root.querySelectorAll('[data-sections-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const open = button.dataset.sectionsToggle === 'open';
            sections.forEach((section) => {
                const instance = collapseOf(bodyOf(section));
                if (open) instance?.show();
                else instance?.hide();
            });
        });
    });

    // Highlight the section in view.
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    items.forEach((item) =>
                        item.classList.toggle('is-current', item.dataset.railTarget === entry.target.dataset.memberSection)
                    );
                });
            },
            { rootMargin: '-20% 0px -70% 0px' }
        );
        sections.forEach((section) => observer.observe(section));
    }

    root.addEventListener('input', refreshDots);
    root.addEventListener('change', refreshDots);
    refreshDots();
}

/* ---------------------------------------------------------- mobile counter */

function setupMobileCounters() {
    document.querySelectorAll('.member-mobile [data-mobile-input]').forEach((input) => {
        const counter = input.parentElement.querySelector('[data-mobile-count]');
        if (!counter) return;

        const update = () => {
            const count = (input.value || '').replace(/\D+/g, '').length;
            counter.textContent = `${count}/10`;
            counter.classList.toggle('is-complete', count === 10);
            counter.classList.toggle('is-partial', count > 0 && count < 10);
        };

        // After mobile-input.js has normalised the value.
        ['input', 'change', 'blur'].forEach((event) =>
            input.addEventListener(event, () => requestAnimationFrame(update))
        );
        update();
    });
}

/* ------------------------------------------------------------ date of birth */

function setupDob() {
    document.querySelectorAll('[data-dob]').forEach((input) => {
        const out = input.closest('form')?.querySelector('[data-age-out]');
        if (!out) return;

        const update = () => {
            if (!input.value) {
                out.textContent = '—';
                return;
            }
            const dob = new Date(input.value);
            const now = new Date();
            let age = now.getFullYear() - dob.getFullYear();
            const beforeBirthday =
                now.getMonth() < dob.getMonth() || (now.getMonth() === dob.getMonth() && now.getDate() < dob.getDate());
            if (beforeBirthday) age -= 1;
            out.textContent = Number.isFinite(age) && age >= 0 ? String(age) : '—';
        };

        input.addEventListener('change', update);
        input.addEventListener('input', update);
        update();
    });
}

/* ----------------------------------------------------------- Aadhaar & PAN */

function setupAadhaar() {
    document.querySelectorAll('[data-aadhaar]').forEach((input) => {
        const format = () => {
            const digits = input.value.replace(/\D+/g, '').slice(0, 12);
            const spaced = digits.replace(/(\d{4})(?=\d)/g, '$1 ');
            if (input.value !== spaced) input.value = spaced;
        };
        input.addEventListener('input', format);
        format();
    });
}

function setupPan() {
    document.querySelectorAll('[data-pan]').forEach((input) => {
        input.addEventListener('input', () => {
            const clean = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);
            if (input.value !== clean) input.value = clean;
        });
    });
}

/* ---------------------------------------------------------------- uploads */

const MAX_BYTES = 7.5 * 1024 * 1024;

function setupUploads() {
    document.querySelectorAll('[data-upload]').forEach((root) => {
        const input = root.querySelector('[data-upload-input]');
        const drop = root.querySelector('[data-upload-drop]');
        const preview = root.querySelector('[data-upload-preview]');
        const empty = root.querySelector('[data-upload-empty]');
        const status = root.querySelector('[data-upload-status]');
        const maxDimension = Number(root.dataset.maxDimension) || 2000;

        if (!input) return;

        ['dragenter', 'dragover'].forEach((e) => drop?.addEventListener(e, () => drop.classList.add('is-dragover')));
        ['dragleave', 'drop'].forEach((e) => drop?.addEventListener(e, () => drop.classList.remove('is-dragover')));

        input.addEventListener('change', async () => {
            const file = input.files && input.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                if (status) status.textContent = 'Please choose an image (JPG, PNG or WEBP).';
                input.value = '';
                return;
            }

            let chosen = file;
            try {
                chosen = await shrink(file, maxDimension);
                if (chosen !== file && 'DataTransfer' in window) {
                    const transfer = new DataTransfer();
                    transfer.items.add(chosen);
                    input.files = transfer.files;
                }
            } catch (e) {
                // Shrinking is a convenience; the server still enforces the limit.
            }

            if (preview) {
                preview.src = URL.createObjectURL(chosen);
                preview.classList.remove('d-none');
            }
            empty?.classList.add('d-none');

            if (status) {
                const kb = Math.round(chosen.size / 1024);
                const size = kb > 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${kb} KB`;
                status.textContent = `${file.name} · ${size}${chosen !== file ? ' (shrunk for upload)' : ''}`;
            }

            // Choosing a new file overrides "Remove".
            const remove = root.querySelector('input[name="remove_files[]"]');
            if (remove) remove.checked = false;
        });
    });
}

/**
 * Re-draw a large image onto a canvas no bigger than maxDimension and return a
 * JPEG File. Small images are returned untouched.
 */
function shrink(file, maxDimension) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();

        img.onload = () => {
            URL.revokeObjectURL(url);
            const longest = Math.max(img.naturalWidth, img.naturalHeight);

            if (longest <= maxDimension && file.size <= MAX_BYTES) {
                resolve(file);
                return;
            }

            const scale = Math.min(1, maxDimension / longest);
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(img.naturalWidth * scale);
            canvas.height = Math.round(img.naturalHeight * scale);

            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(
                (blob) => {
                    if (!blob) {
                        resolve(file);
                        return;
                    }
                    const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                    resolve(new File([blob], name, { type: 'image/jpeg' }));
                },
                'image/jpeg',
                0.88
            );
        };

        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('unreadable image'));
        };

        img.src = url;
    });
}
