// VIP page builder.
//
// Renders the owner's page as an editable copy of itself: sections in page
// order, each with its heading typed in place and its cards laid out the way the
// public page shows them. Everything edits one in-memory document (`state`),
// which is saved in a single PUT — the server applies it in one transaction.
//
//  - drag the dotted handle to reorder sections and cards (SortableJS, so touch
//    works too);
//  - any change marks the page dirty: the status turns amber, the Save button
//    lights up, and leaving the page asks first;
//  - photos are cropped in the browser and uploaded straight away, but only
//    become part of the page on Save;
//  - deleting asks for confirmation.

import Sortable from 'sortablejs';
import Swal from 'sweetalert2';
import { cropImage } from './image-cropper';

const BRAND = '#245a3f';
const DANGER = '#dc2626';

const svg = (path, size = 16) =>
    `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${path}</svg>`;

const ICON = {
    grip: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="5" r="1.6"/><circle cx="15" cy="5" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="19" r="1.6"/><circle cx="15" cy="19" r="1.6"/></svg>`,
    trash: svg('<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/>'),
    plus: svg('<path d="M12 5v14M5 12h14"/>', 22),
    camera: svg('<path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/>', 26),
    x: svg('<path d="M6 6l12 12M18 6L6 18"/>', 14),
    more: svg('<path d="M4 6h16M4 12h16M4 18h10"/>', 14),
    link: svg('<path d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>', 14),
};

const ASPECT = { services: 4 / 3, products: 1, cards: 4 / 3, people: 1 };

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

// Tiny element builder: h('div', { class, data: {...}, onclick, ... }, ...children)
function h(tag, props = {}, ...children) {
    const node = document.createElement(tag);
    Object.entries(props || {}).forEach(([key, value]) => {
        if (value === undefined || value === null || value === false) return;
        if (key === 'class') node.className = value;
        else if (key === 'data') Object.entries(value).forEach(([k, v]) => { node.dataset[k] = v; });
        else if (key === 'html') node.innerHTML = value;
        else if (key === 'value') node.value = value;
        else if (key === 'checked') node.checked = true;
        else if (key.startsWith('on')) node.addEventListener(key.slice(2), value);
        else node.setAttribute(key, value === true ? '' : value);
    });
    children.flat().forEach((child) => {
        if (child === null || child === undefined || child === false) return;
        node.append(child instanceof Node ? child : document.createTextNode(String(child)));
    });
    return node;
}

function move(list, from, to) {
    if (from === to || from === undefined || to === undefined) return;
    const [item] = list.splice(from, 1);
    list.splice(to, 0, item);
}

export function initPageBuilder() {
    const root = document.querySelector('[data-page-builder]');
    const source = document.getElementById('page-builder-state');
    if (!root || !source) return;

    const saveUrl = root.dataset.saveUrl;
    const uploadUrl = root.dataset.uploadUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const sectionsEl = root.querySelector('[data-pb-sections]');
    const statusEl = root.querySelector('[data-pb-status]');
    const statusText = root.querySelector('[data-pb-status-text]');
    const saveBtn = root.querySelector('[data-pb-save]');
    const saveLabel = root.querySelector('[data-pb-save-label]');
    const discardBtn = root.querySelector('[data-pb-discard]');
    const errorsEl = root.querySelector('[data-pb-errors]');

    let state = JSON.parse(source.textContent);
    let dirty = false;
    let saving = false;
    let seq = 0;
    let sortables = [];

    const uid = () => `u${++seq}`;

    function tag(doc) {
        doc.sections.forEach((section) => {
            section._uid = uid();
            (section.items || []).forEach((item) => { item._uid = uid(); });
        });
        return doc;
    }

    // ---------------------------------------------------------------- dirty bar

    function refreshBar() {
        root.classList.toggle('pb-dirty', dirty);
        statusEl.classList.toggle('is-dirty', dirty);
        statusText.textContent = saving
            ? 'Saving…'
            : (dirty ? 'You have unsaved changes' : 'All changes saved');
        saveBtn.disabled = !dirty || saving;
        saveLabel.textContent = saving ? 'Saving…' : (dirty ? 'Save changes' : 'Saved');
        discardBtn.classList.toggle('d-none', !dirty || saving);
    }

    function markDirty() {
        if (!dirty) {
            dirty = true;
            refreshBar();
        }
    }

    window.addEventListener('beforeunload', (event) => {
        if (!dirty) return;
        event.preventDefault();
        event.returnValue = '';
    });

    // -------------------------------------------------------------- field binds

    // An input that writes straight into `obj[field]`.
    function field(obj, name, props = {}, kind = 'input') {
        const node = h(kind, {
            class: `pb-field ${props.class || ''}`,
            placeholder: props.placeholder || '',
            type: kind === 'input' ? (props.type || 'text') : undefined,
            rows: props.rows,
            min: props.min,
            step: props.step,
            inputmode: props.inputmode,
            maxlength: props.maxlength,
            'aria-label': props.label || props.placeholder || name,
            data: { field: name },
        });
        node.value = obj[name] ?? '';
        node.addEventListener('input', () => {
            obj[name] = props.type === 'number' ? (node.value === '' ? null : node.value) : node.value;
            node.classList.remove('is-invalid');
            props.onInput?.(node.value);
            markDirty();
        });
        return node;
    }

    function toggle(obj, name, labels, onChange) {
        const input = h('input', { type: 'checkbox', class: 'form-check-input', role: 'switch', checked: !!obj[name] });
        const text = h('span', { class: 'fs-xs text-secondary' }, obj[name] ? labels[0] : labels[1]);
        input.addEventListener('change', () => {
            obj[name] = input.checked;
            text.textContent = input.checked ? labels[0] : labels[1];
            onChange?.(input.checked);
            markDirty();
        });
        return h('label', { class: 'form-check form-switch d-inline-flex align-items-center gap-2 mb-0 pb-switch', title: 'Show or hide on your page' },
            input, text);
    }

    function iconButton(icon, title, onclick, extra = '') {
        return h('button', { type: 'button', class: `pb-icon-btn ${extra}`, title, 'aria-label': title, html: icon, onclick });
    }

    async function confirmDelete(title, text) {
        const result = await Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: DANGER,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        });
        return result.isConfirmed;
    }

    // ------------------------------------------------------------------ render

    function renderAll() {
        sortables.forEach((s) => s.destroy());
        sortables = [];
        sectionsEl.replaceChildren(...state.sections.map(renderSection));

        sortables.push(Sortable.create(sectionsEl, {
            handle: '.pb-section-handle',
            draggable: '.pb-section',
            animation: 180,
            ghostClass: 'pb-ghost',
            onEnd: (event) => {
                move(state.sections, event.oldDraggableIndex, event.newDraggableIndex);
                markDirty();
            },
        }));
    }

    function rerenderSection(section) {
        const old = sectionsEl.querySelector(`[data-uid="${section._uid}"]`);
        if (old) old.replaceWith(renderSection(section));
    }

    function renderSection(section) {
        const body = h('div', { class: `pb-section-body ${section.visible ? '' : 'pb-is-off'}` });
        const labelEl = h('p', { class: 'fw-semibold mb-0 text-truncate' }, section.label);

        const bar = h('div', { class: 'pb-section-bar' },
            h('span', { class: 'pb-handle pb-section-handle', title: 'Drag to move this section', html: ICON.grip }),
            h('div', { class: 'flex-grow-1 min-w-0' },
                labelEl,
                h('p', { class: 'fs-xs text-secondary mb-0 text-truncate' }, section.hint || ''),
            ),
            section.type === 'custom' ? styleSelect(section) : null,
            toggle(section, 'visible', ['Shown', 'Hidden'], (on) => body.classList.toggle('pb-is-off', !on)),
            section.type === 'custom'
                ? iconButton(ICON.trash, 'Delete this section', async () => {
                    const count = (section.items || []).length;
                    if (!(await confirmDelete(`Delete “${section.heading.title || 'this section'}”?`,
                        `The section and its ${count} card${count === 1 ? '' : 's'} will be removed from your page when you save.`))) return;
                    state.sections = state.sections.filter((s) => s !== section);
                    sectionsEl.querySelector(`[data-uid="${section._uid}"]`)?.remove();
                    markDirty();
                }, 'pb-icon-btn--danger')
                : null,
        );

        body.append(headingEditor(section, (title) => {
            if (section.type === 'custom') labelEl.textContent = title || 'Untitled section';
        }));

        switch (section.type) {
            case 'about': body.append(aboutEditor(section)); break;
            case 'stats': body.append(statsEditor(section)); break;
            case 'services':
            case 'products':
            case 'custom': body.append(cardsEditor(section)); break;
            case 'faqs': body.append(faqsEditor(section)); break;
            case 'linked': body.append(linkedEditor(section)); break;
            default: break;
        }

        return h('section', { class: `pb-section pb-section--${section.type}`, data: { uid: section._uid } }, bar, body);
    }

    function styleSelect(section) {
        const select = h('select', { class: 'form-select form-select-sm w-auto', 'aria-label': 'Card style' },
            h('option', { value: 'cards' }, 'Picture cards'),
            h('option', { value: 'people' }, 'People (round photos)'),
        );
        select.value = section.style || 'cards';
        select.addEventListener('change', () => {
            section.style = select.value;
            markDirty();
            rerenderSection(section);
        });
        return select;
    }

    function headingEditor(section, onTitle) {
        const hd = section.heading;
        const wrap = h('div', { class: 'pb-heading text-center mx-auto mb-6' });

        if (section.key !== 'social_media') {
            wrap.append(field(hd, 'eyebrow', { class: 'pb-eyebrow', placeholder: 'Small label above the title (optional)', maxlength: 80 }));
        }
        wrap.append(field(hd, 'title', {
            class: 'pb-title msite-heading',
            placeholder: 'Section title',
            maxlength: 150,
            onInput: onTitle,
        }));
        if (!['social_media', 'about'].includes(section.key)) {
            wrap.append(field(hd, 'subtitle', { class: 'pb-subtitle', placeholder: 'A short line under the title (optional)', maxlength: 255 }));
        }
        return wrap;
    }

    function aboutEditor(section) {
        section.content ||= { short_description: '', description: '' };
        return h('div', { class: 'mw-3xl mx-auto d-flex flex-column gap-3' },
            h('label', { class: 'pb-label' }, 'One-line introduction'),
            field(section.content, 'short_description', {
                class: 'pb-lead', placeholder: 'e.g. Jhansi’s trusted multi-speciality hospital, caring for families since 2006.', maxlength: 255,
            }, 'textarea'),
            h('label', { class: 'pb-label' }, 'Your story'),
            field(section.content, 'description', {
                rows: 6, placeholder: 'Who you are, what you do best, and why customers choose you.', maxlength: 5000,
            }, 'textarea'),
        );
    }

    // --------------------------------------------------------------- highlights

    function statsEditor(section) {
        section.items ||= [];
        const grid = h('div', { class: 'd-grid grid-cols-2 grid-cols-md-4 gap-3 pb-grid' });

        const draw = () => {
            grid.replaceChildren(...section.items.map((stat) => statCard(section, stat)));
            if (section.items.length < 8) {
                grid.append(addTile('Add a number', () => {
                    section.items.push({ _uid: uid(), label: '', value: '', suffix: '+', is_active: true });
                    markDirty();
                    draw();
                    grid.querySelector('.pb-stat:last-of-type [data-field="value"]')?.focus();
                }));
            }
        };
        draw();
        sortables.push(sortableGrid(grid, section.items));

        return h('div', {},
            h('p', { class: 'fs-sm text-secondary text-center mb-4' }, 'Big numbers customers trust. Type the number, an optional sign like + or %, and what it counts.'),
            grid,
        );
    }

    function statCard(section, stat) {
        const card = h('div', { class: `pb-stat pb-card ${stat.is_active ? '' : 'pb-card--off'}`, data: { uid: stat._uid } });
        card.append(
            h('div', { class: 'pb-card-tools' },
                h('span', { class: 'pb-handle pb-card-handle', title: 'Drag to move', html: ICON.grip }),
                h('span', { class: 'flex-grow-1' }),
                toggle(stat, 'is_active', ['', ''], (on) => card.classList.toggle('pb-card--off', !on)),
                iconButton(ICON.trash, 'Delete', async () => {
                    if (!(await confirmDelete('Delete this number?', 'It will be removed from your page when you save.'))) return;
                    section.items.splice(section.items.indexOf(stat), 1);
                    markDirty();
                    rerenderSection(section);
                }, 'pb-icon-btn--danger'),
            ),
            h('div', { class: 'd-flex align-items-baseline justify-content-center gap-1 px-3' },
                field(stat, 'value', { class: 'pb-stat-value', placeholder: '250', maxlength: 20, inputmode: 'numeric' }),
                field(stat, 'suffix', { class: 'pb-stat-suffix', placeholder: '+', maxlength: 10 }),
            ),
            field(stat, 'label', { class: 'pb-stat-label', placeholder: 'e.g. Beds', maxlength: 60 }),
        );
        return card;
    }

    // ------------------------------------------------------------------ cards

    function cardsEditor(section) {
        section.items ||= [];
        const kind = section.type === 'custom' ? (section.style || 'cards') : section.type;
        const quota = state.quota?.[section.type];
        const people = kind === 'people';

        const grid = h('div', { class: `d-grid grid-cols-1 grid-cols-sm-2 ${people ? 'grid-cols-lg-4' : 'grid-cols-lg-3'} gap-4 pb-grid` });
        const quotaEl = quota ? h('p', { class: 'fs-xs text-secondary text-center mb-3' }) : null;

        const draw = () => {
            grid.replaceChildren(...section.items.map((item) => itemCard(section, item, kind)));

            const full = quota && section.items.length >= quota.limit;
            if (quotaEl) {
                quotaEl.textContent = `${section.items.length} of ${quota.limit} used on your plan`;
                quotaEl.classList.toggle('text-danger', !!full);
            }
            grid.append(addTile(
                full ? 'Plan limit reached' : addLabel(section, kind),
                full ? null : () => {
                    section.items.push(blankItem(section.type));
                    markDirty();
                    draw();
                    grid.querySelectorAll('.pb-card')[section.items.length - 1]?.querySelector('[data-field="name"], [data-field="title"]')?.focus();
                },
            ));
        };
        draw();
        sortables.push(sortableGrid(grid, section.items));

        return h('div', {}, quotaEl, grid);
    }

    function addLabel(section, kind) {
        if (section.type === 'services') return 'Add a service';
        if (section.type === 'products') return 'Add a product';
        return kind === 'people' ? 'Add a person' : 'Add a card';
    }

    function blankItem(type) {
        const base = { _uid: uid(), id: null, short_description: '', long_description: '', image_path: null, image_url: null, is_active: true };
        if (type === 'services') return { ...base, name: '', category: '', mrp: null, offer_price: null, show_book_now: true };
        if (type === 'products') return { ...base, name: '', category: '', mrp: null, offer_price: null, stock: null };
        return { ...base, title: '', subtitle: '', button_label: '', button_url: '' };
    }

    function itemCard(section, item, kind) {
        const isCustom = section.type === 'custom';
        const people = kind === 'people';
        const card = h('div', { class: `pb-card msite-card ${item.is_active ? '' : 'pb-card--off'} ${people ? 'pb-card--people' : ''}`, data: { uid: item._uid } });

        const media = h('button', {
            type: 'button',
            class: `pb-media ${people ? 'pb-media--round' : ''}`,
            style: people ? '' : `aspect-ratio: ${ASPECT[kind] || 4 / 3}`,
            title: 'Click to add or change the photo',
        });
        const drawMedia = () => {
            media.replaceChildren(
                item.image_url
                    ? h('img', { src: item.image_url, alt: '' })
                    : h('span', { class: 'pb-media-empty', html: `${ICON.camera}<span>Add photo</span>` }),
                h('span', { class: 'pb-media-hint' }, item.image_url ? 'Change photo' : 'Upload & crop'),
            );
        };
        drawMedia();
        media.addEventListener('click', () => pickImage(item, ASPECT[kind] || 4 / 3, media, drawMedia));

        const removeImage = item.image_url
            ? iconButton(ICON.x, 'Remove photo', () => {
                item.image_path = null;
                item.image_url = null;
                markDirty();
                rerenderSection(section);
            }, 'pb-media-remove')
            : null;

        const body = h('div', { class: `p-3 d-flex flex-column gap-2 ${people ? 'text-center' : ''}` });

        if (isCustom) {
            body.append(
                field(item, 'title', { class: 'pb-card-title', placeholder: people ? 'Name, e.g. Dr. Anjali Mehra' : 'Title', maxlength: 150 }),
                field(item, 'subtitle', { class: 'pb-card-subtitle', placeholder: people ? 'Role, e.g. MD – Cardiology' : 'Tag line (optional)', maxlength: 150 }),
            );
        } else {
            body.append(
                field(item, 'category', { class: 'pb-card-subtitle', placeholder: 'Category (optional)', maxlength: 100 }),
                field(item, 'name', { class: 'pb-card-title', placeholder: section.type === 'services' ? 'Service name' : 'Product name', maxlength: 150 }),
            );
        }

        body.append(field(item, 'short_description', {
            class: 'pb-card-text', rows: 2, maxlength: 300,
            placeholder: 'Short description (shown on the card)',
        }, 'textarea'));

        if (!isCustom) {
            const prices = h('div', { class: 'd-grid gap-2', style: `grid-template-columns: repeat(${section.type === 'products' ? 3 : 2}, minmax(0, 1fr))` },
                h('label', { class: 'pb-mini' }, h('span', {}, 'MRP ₹'),
                    field(item, 'mrp', { type: 'number', min: 0, step: '0.01', inputmode: 'decimal', placeholder: '—' })),
                h('label', { class: 'pb-mini' }, h('span', {}, 'Sale ₹'),
                    field(item, 'offer_price', { type: 'number', min: 0, step: '0.01', inputmode: 'decimal', placeholder: '—' })),
                section.type === 'products'
                    ? h('label', { class: 'pb-mini' }, h('span', {}, 'Stock'),
                        field(item, 'stock', { type: 'number', min: 0, step: '1', inputmode: 'numeric', placeholder: 'Any' }))
                    : null,
            );
            body.append(prices);
        }

        body.append(h('button', {
            type: 'button',
            class: 'btn btn-link p-0 fs-xs text-brand text-decoration-none d-inline-flex align-items-center gap-1 align-self-start',
            html: `${ICON.more} Long description & more`,
            onclick: () => openDetails(section, item),
        }));

        card.append(
            h('div', { class: 'pb-card-tools' },
                h('span', { class: 'pb-handle pb-card-handle', title: 'Drag to move', html: ICON.grip }),
                h('span', { class: 'pb-off-badge' }, 'Hidden'),
                h('span', { class: 'flex-grow-1' }),
                toggle(item, 'is_active', ['', ''], (on) => card.classList.toggle('pb-card--off', !on)),
                iconButton(ICON.trash, 'Delete', async () => {
                    const name = item.name || item.title || 'this card';
                    if (!(await confirmDelete(`Delete “${name}”?`, 'It will be removed from your page when you save.'))) return;
                    section.items.splice(section.items.indexOf(item), 1);
                    markDirty();
                    rerenderSection(section);
                }, 'pb-icon-btn--danger'),
            ),
            h('div', { class: 'position-relative' }, media, removeImage),
            body,
        );
        return card;
    }

    function addTile(label, onclick) {
        return h('button', {
            type: 'button',
            class: `pb-add-tile ${onclick ? '' : 'is-disabled'}`,
            disabled: !onclick,
            onclick: onclick || undefined,
        }, h('span', { html: ICON.plus }), h('span', {}, label));
    }

    function sortableGrid(grid, list) {
        return Sortable.create(grid, {
            handle: '.pb-card-handle',
            draggable: '.pb-card',
            animation: 160,
            ghostClass: 'pb-ghost',
            onEnd: (event) => {
                move(list, event.oldDraggableIndex, event.newDraggableIndex);
                markDirty();
            },
        });
    }

    // --------------------------------------------------------------- FAQs

    function faqsEditor(section) {
        section.items ||= [];
        const list = h('div', { class: 'mw-3xl mx-auto d-flex flex-column gap-3 pb-grid' });

        const draw = () => {
            list.replaceChildren(...section.items.map((faq) => {
                const row = h('div', { class: `pb-card pb-faq ${faq.is_active ? '' : 'pb-card--off'}`, data: { uid: faq._uid } },
                    h('div', { class: 'pb-card-tools' },
                        h('span', { class: 'pb-handle pb-card-handle', title: 'Drag to move', html: ICON.grip }),
                        h('span', { class: 'pb-off-badge' }, 'Hidden'),
                        h('span', { class: 'flex-grow-1' }),
                        toggle(faq, 'is_active', ['', ''], (on) => row.classList.toggle('pb-card--off', !on)),
                        iconButton(ICON.trash, 'Delete', async () => {
                            if (!(await confirmDelete('Delete this question?', 'It will be removed from your page when you save.'))) return;
                            section.items.splice(section.items.indexOf(faq), 1);
                            markDirty();
                            draw();
                        }, 'pb-icon-btn--danger'),
                    ),
                    h('div', { class: 'px-3 pb-3 d-flex flex-column gap-2' },
                        field(faq, 'question', { class: 'pb-card-title', placeholder: 'Question customers ask', maxlength: 255 }),
                        field(faq, 'answer', { class: 'pb-card-text', rows: 2, placeholder: 'Your answer', maxlength: 2000 }, 'textarea'),
                    ),
                );
                return row;
            }));
            list.append(addTile('Add a question', () => {
                section.items.push({ _uid: uid(), id: null, question: '', answer: '', is_active: true });
                markDirty();
                draw();
                list.querySelectorAll('.pb-faq')[section.items.length - 1]?.querySelector('[data-field="question"]')?.focus();
            }));
        };
        draw();
        sortables.push(sortableGrid(list, section.items));
        return list;
    }

    function linkedEditor(section) {
        return h('div', { class: 'd-flex flex-wrap align-items-center justify-content-center gap-3 text-center' },
            h('span', { class: 'fs-sm text-secondary' }, section.summary || ''),
            h('a', { href: section.manage_url, class: 'btn btn-sm btn-outline-brand d-inline-flex align-items-center gap-1', html: `${ICON.link} Manage ${section.label.toLowerCase()}` }),
        );
    }

    // --------------------------------------------------------- details dialog

    function openDetails(section, item) {
        const isCustom = section.type === 'custom';
        const close = () => { backdrop.remove(); document.body.style.overflow = ''; };

        const fields = h('div', { class: 'd-flex flex-column gap-3' },
            h('div', {},
                h('label', { class: 'pb-label' }, 'Long description'),
                h('p', { class: 'fs-xs text-secondary mb-1' }, 'Shown when a customer taps “Read more”. Use new lines for points.'),
                field(item, 'long_description', { class: 'form-control', rows: 8, maxlength: 5000, placeholder: 'Everything a customer should know…' }, 'textarea'),
            ),
        );

        if (isCustom) {
            fields.append(h('div', { class: 'd-grid gap-3', style: 'grid-template-columns: 1fr 2fr' },
                h('div', {}, h('label', { class: 'pb-label' }, 'Button text'),
                    field(item, 'button_label', { class: 'form-control', placeholder: 'e.g. Book appointment', maxlength: 40 })),
                h('div', {}, h('label', { class: 'pb-label' }, 'Button link'),
                    field(item, 'button_url', { class: 'form-control', placeholder: 'https://…  or  tel:9876543210', maxlength: 500 })),
            ));
        }

        if (section.type === 'services') {
            fields.append(toggle(item, 'show_book_now', ['“Book Now” button shown', '“Book Now” button hidden']));
        }

        const backdrop = h('div', { class: 'crop-backdrop', role: 'dialog', 'aria-modal': 'true', onclick: (e) => { if (e.target === backdrop) close(); } },
            h('div', { class: 'crop-dialog bg-body rounded-4 shadow-lg p-4' },
                h('div', { class: 'd-flex align-items-center justify-content-between mb-3' },
                    h('p', { class: 'fw-semibold mb-0' }, item.name || item.title || 'Card details'),
                    iconButton(ICON.x, 'Close', close),
                ),
                fields,
                h('div', { class: 'd-flex justify-content-end mt-4' },
                    h('button', { type: 'button', class: 'btn btn-brand', onclick: close }, 'Done'),
                ),
            ),
        );
        document.body.append(backdrop);
        document.body.style.overflow = 'hidden';
        backdrop.querySelector('textarea')?.focus();
    }

    // ------------------------------------------------------------- photos

    function pickImage(item, aspect, media, redraw) {
        const input = h('input', { type: 'file', accept: 'image/jpeg,image/png,image/webp' });
        input.addEventListener('change', async () => {
            const file = input.files?.[0];
            if (!file) return;

            let blob;
            try {
                blob = await cropImage(file, { aspect });
            } catch (error) {
                Toast.fire({ icon: 'error', title: error.message });
                return;
            }
            if (!blob) return;

            media.classList.add('is-uploading');
            try {
                const body = new FormData();
                body.append('image', blob, blob.type === 'image/webp' ? 'photo.webp' : 'photo.jpg');
                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    body,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'The photo could not be uploaded.');

                item.image_path = data.path;
                item.image_url = data.url;
                markDirty();
                redraw();
                rerenderSection(state.sections.find((s) => (s.items || []).includes(item)));
            } catch (error) {
                Toast.fire({ icon: 'error', title: error.message });
            } finally {
                media.classList.remove('is-uploading');
            }
        });
        input.click();
    }

    // -------------------------------------------------------------- sections

    root.querySelector('[data-pb-add-section]')?.addEventListener('click', async () => {
        const result = await Swal.fire({
            title: 'Add a new section',
            html: `
                <div class="text-start">
                    <label class="form-label fw-semibold">Section title</label>
                    <input id="pb-new-title" class="form-control mb-4" maxlength="150" placeholder="e.g. Meet Our Doctors">
                    <p class="form-label fw-semibold mb-2">How should the cards look?</p>
                    <label class="d-flex gap-2 align-items-start border rounded-3 p-3 mb-2">
                        <input type="radio" name="pb-new-style" value="cards" checked class="form-check-input mt-1">
                        <span><strong>Picture cards</strong><br><small class="text-secondary">Facilities, departments, offers, packages</small></span>
                    </label>
                    <label class="d-flex gap-2 align-items-start border rounded-3 p-3">
                        <input type="radio" name="pb-new-style" value="people" class="form-check-input mt-1">
                        <span><strong>People with round photos</strong><br><small class="text-secondary">Doctors, team members, trainers</small></span>
                    </label>
                </div>`,
            showCancelButton: true,
            confirmButtonColor: BRAND,
            confirmButtonText: 'Add section',
            focusConfirm: false,
            didOpen: () => document.getElementById('pb-new-title')?.focus(),
            preConfirm: () => {
                const title = document.getElementById('pb-new-title').value.trim();
                if (!title) {
                    Swal.showValidationMessage('Give the section a title');
                    return false;
                }
                return { title, style: document.querySelector('input[name="pb-new-style"]:checked')?.value || 'cards' };
            },
        });
        if (!result.isConfirmed) return;

        const section = {
            _uid: uid(),
            key: `new-${Math.random().toString(36).slice(2, 10)}`,
            type: 'custom',
            id: null,
            label: result.value.title,
            hint: 'Your own section. Add a card for each doctor, facility, team member or offer.',
            visible: true,
            style: result.value.style,
            heading: { eyebrow: '', title: result.value.title, subtitle: '' },
            items: [blankItem('custom')],
        };

        // New sections go above Contact, where most owners want them.
        const at = state.sections.findIndex((s) => s.key === 'contact');
        if (at === -1) state.sections.push(section);
        else state.sections.splice(at, 0, section);

        markDirty();
        renderAll();
        sectionsEl.querySelector(`[data-uid="${section._uid}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // ------------------------------------------------------------------ save

    function serialize() {
        const strip = ({ _uid, image_url, ...rest }) => rest;

        return state.sections.map((section) => {
            const out = {
                key: section.key,
                type: section.type,
                visible: !!section.visible,
                heading: section.heading,
            };
            if (section.type === 'custom') {
                out.id = section.id ?? null;
                out.style = section.style || 'cards';
            }
            if (section.type === 'about') out.content = section.content;
            if (['stats', 'services', 'products', 'faqs', 'custom'].includes(section.type)) {
                out.items = (section.items || []).map(strip);
            }
            return out;
        });
    }

    function showErrors(errors) {
        root.querySelectorAll('.is-invalid').forEach((node) => node.classList.remove('is-invalid'));
        root.querySelectorAll('.pb-card--error').forEach((node) => node.classList.remove('pb-card--error'));

        const messages = [...new Set(Object.values(errors).flat())];
        errorsEl.replaceChildren(
            h('p', { class: 'fw-semibold mb-1' }, 'Please fix these before saving:'),
            h('ul', { class: 'mb-0 list-disc' }, messages.map((m) => h('li', {}, m))),
        );
        errorsEl.classList.remove('d-none');

        let first = null;
        Object.keys(errors).forEach((path) => {
            const match = path.match(/^sections\.(\d+)\.(?:items\.(\d+)\.)?(?:heading\.)?(\w+)$/);
            if (!match) return;
            const section = state.sections[Number(match[1])];
            const sectionNode = section && sectionsEl.querySelector(`[data-uid="${section._uid}"]`);
            if (!sectionNode) return;

            let scope = sectionNode;
            if (match[2] !== undefined) {
                const item = section.items?.[Number(match[2])];
                const itemNode = item && sectionNode.querySelector(`[data-uid="${item._uid}"]`);
                if (itemNode) {
                    itemNode.classList.add('pb-card--error');
                    scope = itemNode;
                }
            }
            const input = scope.querySelector(`[data-field="${match[3]}"]`);
            input?.classList.add('is-invalid');
            first ||= input || scope;
        });
        (first || errorsEl).scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    async function save() {
        if (!dirty || saving) return;
        saving = true;
        refreshBar();

        try {
            const response = await fetch(saveUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ sections: serialize() }),
            });
            const data = await response.json().catch(() => ({}));

            if (response.status === 422) {
                showErrors(data.errors || { form: [data.message || 'Please check the highlighted fields.'] });
                Toast.fire({ icon: 'error', title: 'Some details need fixing' });
                return;
            }
            if (!response.ok) throw new Error(data.message || 'Your page could not be saved. Please try again.');

            state = tag(data.state);
            dirty = false;
            errorsEl.classList.add('d-none');
            renderAll();
            Toast.fire({ icon: 'success', title: data.message || 'Saved' });
        } catch (error) {
            Toast.fire({ icon: 'error', title: error.message });
        } finally {
            saving = false;
            refreshBar();
        }
    }

    saveBtn.addEventListener('click', save);
    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            save();
        }
    });

    discardBtn.addEventListener('click', async () => {
        const result = await Swal.fire({
            title: 'Discard your changes?',
            text: 'Everything since your last save will be lost.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: DANGER,
            confirmButtonText: 'Discard',
            reverseButtons: true,
        });
        if (!result.isConfirmed) return;
        dirty = false;
        window.location.reload();
    });

    state = tag(state);
    renderAll();
    refreshBar();
}
