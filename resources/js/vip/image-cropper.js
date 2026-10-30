// Small dependency-free image cropper for the page builder.
//
// cropImage(file, { aspect }) opens a dialog where the picture can be dragged
// (mouse or finger) and zoomed (slider or wheel) inside a fixed frame, with a
// choice of frame shapes. It resolves with the cropped image as a Blob, or null
// when cancelled. The image always covers the frame, so a crop never has empty
// bars. Output is WebP where the browser can encode it, JPEG otherwise.

const SHAPES = [
    { label: 'Square', aspect: 1 },
    { label: 'Landscape 4:3', aspect: 4 / 3 },
    { label: 'Wide 16:9', aspect: 16 / 9 },
    { label: 'Portrait 3:4', aspect: 3 / 4 },
];

const OUTPUT_WIDTH = 1200;

function el(tag, attrs = {}, ...children) {
    const node = document.createElement(tag);
    Object.entries(attrs).forEach(([key, value]) => {
        if (key === 'class') node.className = value;
        else if (key.startsWith('on')) node.addEventListener(key.slice(2), value);
        else node.setAttribute(key, value);
    });
    children.flat().forEach((child) => node.append(child));
    return node;
}

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => resolve({ img, url });
        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('That file is not an image we can open.'));
        };
        img.src = url;
    });
}

export async function cropImage(file, { aspect = 4 / 3 } = {}) {
    const { img, url } = await loadImage(file);

    return new Promise((resolve) => {
        let current = aspect;
        let zoom = 1;
        let cover = 1;
        let x = 0;
        let y = 0;
        let frameW = 0;
        let frameH = 0;

        const picture = el('img', { src: url, alt: '', class: 'crop-image', draggable: 'false' });
        const frame = el('div', { class: 'crop-frame' }, picture);
        const slider = el('input', { type: 'range', min: '1', max: '4', step: '0.01', value: '1', class: 'form-range', 'aria-label': 'Zoom' });

        const shapeButtons = SHAPES.map((shape) => el('button', {
            type: 'button',
            class: 'btn btn-sm btn-outline-secondary',
            onclick: () => setShape(shape.aspect),
        }, shape.label));

        const dialog = el('div', { class: 'crop-backdrop', role: 'dialog', 'aria-modal': 'true', 'aria-label': 'Crop photo' },
            el('div', { class: 'crop-dialog bg-body rounded-4 shadow-lg p-4' },
                el('div', { class: 'd-flex align-items-center justify-content-between mb-3' },
                    el('p', { class: 'fw-semibold mb-0' }, 'Crop your photo'),
                    el('span', { class: 'fs-xs text-secondary' }, 'Drag to move · scroll or slide to zoom'),
                ),
                frame,
                el('div', { class: 'd-flex align-items-center gap-3 mt-3' },
                    el('span', { class: 'fs-sm text-secondary' }, '−'), slider, el('span', { class: 'fs-sm text-secondary' }, '+'),
                ),
                el('div', { class: 'd-flex flex-wrap gap-2 mt-3' }, shapeButtons),
                el('div', { class: 'd-flex justify-content-end gap-2 mt-4' },
                    el('button', { type: 'button', class: 'btn btn-light', onclick: () => close(null) }, 'Cancel'),
                    el('button', { type: 'button', class: 'btn btn-brand', onclick: () => finish() }, 'Use this photo'),
                ),
            ),
        );

        function layout() {
            const maxW = Math.min(560, window.innerWidth - 64);
            const maxH = Math.min(420, window.innerHeight - 300);
            frameW = maxW;
            frameH = frameW / current;
            if (frameH > maxH) {
                frameH = maxH;
                frameW = frameH * current;
            }
            frame.style.width = `${frameW}px`;
            frame.style.height = `${frameH}px`;
            cover = Math.max(frameW / img.naturalWidth, frameH / img.naturalHeight);
        }

        function scale() {
            return cover * zoom;
        }

        // Keep the image covering the frame on every side.
        function clamp() {
            const w = img.naturalWidth * scale();
            const h = img.naturalHeight * scale();
            x = Math.min(0, Math.max(frameW - w, x));
            y = Math.min(0, Math.max(frameH - h, y));
        }

        function paint() {
            clamp();
            picture.style.width = `${img.naturalWidth * scale()}px`;
            picture.style.height = `${img.naturalHeight * scale()}px`;
            picture.style.transform = `translate(${x}px, ${y}px)`;
        }

        function centre() {
            x = (frameW - img.naturalWidth * scale()) / 2;
            y = (frameH - img.naturalHeight * scale()) / 2;
        }

        function setShape(next) {
            current = next;
            shapeButtons.forEach((button, i) => {
                const active = Math.abs(SHAPES[i].aspect - next) < 0.001;
                button.classList.toggle('btn-brand', active);
                button.classList.toggle('btn-outline-secondary', !active);
            });
            zoom = 1;
            slider.value = '1';
            layout();
            centre();
            paint();
        }

        // Zoom about the middle of the frame, so what is centred stays centred.
        function setZoom(next) {
            const before = scale();
            const cx = (frameW / 2 - x) / before;
            const cy = (frameH / 2 - y) / before;
            zoom = Math.min(4, Math.max(1, next));
            slider.value = String(zoom);
            x = frameW / 2 - cx * scale();
            y = frameH / 2 - cy * scale();
            paint();
        }

        let drag = null;
        frame.addEventListener('pointerdown', (event) => {
            drag = { startX: event.clientX, startY: event.clientY, x, y };
            frame.setPointerCapture(event.pointerId);
        });
        frame.addEventListener('pointermove', (event) => {
            if (!drag) return;
            x = drag.x + (event.clientX - drag.startX);
            y = drag.y + (event.clientY - drag.startY);
            paint();
        });
        const endDrag = () => { drag = null; };
        frame.addEventListener('pointerup', endDrag);
        frame.addEventListener('pointercancel', endDrag);
        frame.addEventListener('wheel', (event) => {
            event.preventDefault();
            setZoom(zoom * (event.deltaY < 0 ? 1.08 : 1 / 1.08));
        }, { passive: false });
        slider.addEventListener('input', () => setZoom(parseFloat(slider.value)));

        const onKey = (event) => { if (event.key === 'Escape') close(null); };
        document.addEventListener('keydown', onKey);

        function close(result) {
            document.removeEventListener('keydown', onKey);
            dialog.remove();
            URL.revokeObjectURL(url);
            document.body.style.overflow = '';
            resolve(result);
        }

        function finish() {
            const s = scale();
            const sw = frameW / s;
            const sh = frameH / s;
            const outW = Math.round(Math.min(OUTPUT_WIDTH, sw));
            const outH = Math.round(outW / current);

            const canvas = document.createElement('canvas');
            canvas.width = outW;
            canvas.height = outH;
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(img, -x / s, -y / s, sw, sh, 0, 0, outW, outH);

            const webp = canvas.toDataURL('image/webp').startsWith('data:image/webp');
            canvas.toBlob((blob) => close(blob), webp ? 'image/webp' : 'image/jpeg', 0.88);
        }

        document.body.append(dialog);
        document.body.style.overflow = 'hidden';
        setShape(aspect);
    });
}
