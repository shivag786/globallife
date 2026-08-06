@php $latestOrderId = (int) \App\Models\Order::max('id'); @endphp
{{-- New-order sound alert for admins: polls for orders and plays a chime + toast. --}}
<div data-order-alert
     data-poll-url="{{ route('admin.orders.poll') }}"
     data-orders-url="{{ route('admin.orders.index') }}"
     data-latest-id="{{ $latestOrderId }}"
     class="hidden"></div>
<script>
(function () {
    var root = document.querySelector('[data-order-alert]');
    if (!root) return;

    var pollUrl = root.dataset.pollUrl;
    var ordersUrl = root.dataset.ordersUrl;
    var lastSeen = parseInt(root.dataset.latestId || '0', 10);
    var newCount = 0;
    var audioCtx = null;
    var POLL_MS = 20000;

    // Browsers block audio until the user has interacted with the page, so build
    // (and resume) the AudioContext on the first gesture.
    function unlockAudio() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch (e) { /* Web Audio unsupported — toast still shows */ }
    }
    ['pointerdown', 'keydown', 'touchstart'].forEach(function (ev) {
        document.addEventListener(ev, unlockAudio, { passive: true });
    });

    // A short two-note "ding-dong" chime, synthesized (no audio file needed).
    function chime() {
        if (!audioCtx || audioCtx.state !== 'running') return;
        var t0 = audioCtx.currentTime;
        [[880, 0], [1174.66, 0.18]].forEach(function (n) {
            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.value = n[0];
            var start = t0 + n[1];
            gain.gain.setValueAtTime(0.0001, start);
            gain.gain.exponentialRampToValueAtTime(0.35, start + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.4);
            osc.connect(gain).connect(audioCtx.destination);
            osc.start(start);
            osc.stop(start + 0.42);
        });
    }

    function updateBadge() {
        document.querySelectorAll('[data-order-badge]').forEach(function (b) {
            b.textContent = newCount > 99 ? '99+' : String(newCount);
            b.classList.toggle('hidden', newCount <= 0);
        });
    }

    function toast(count) {
        var el = document.createElement('a');
        el.href = ordersUrl;
        el.textContent = '🛎️ ' + count + ' new order' + (count > 1 ? 's' : '') + ' — view';
        el.style.cssText = 'position:fixed;top:16px;right:16px;z-index:80;background:#0f766e;color:#fff;' +
            'font-size:14px;font-weight:600;padding:10px 16px;border-radius:9999px;text-decoration:none;' +
            'box-shadow:0 10px 30px rgba(0,0,0,.25);opacity:0;transform:translateY(-8px);' +
            'transition:opacity .25s,transform .25s;';
        document.body.appendChild(el);
        requestAnimationFrame(function () { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
        setTimeout(function () {
            el.style.opacity = '0'; el.style.transform = 'translateY(-8px)';
            setTimeout(function () { el.remove(); }, 300);
        }, 6000);
    }

    function poll() {
        fetch(pollUrl + '?since=' + lastSeen, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
            .then(function (data) {
                if (data && data.new > 0) {
                    newCount += data.new;
                    lastSeen = data.latest_id;
                    chime();
                    toast(data.new);
                    updateBadge();
                    if (document.title.indexOf('🛎️') === -1) document.title = '🛎️ ' + document.title;
                }
            })
            .catch(function () { /* transient error — try again next tick */ });
    }

    setInterval(poll, POLL_MS);
})();
</script>
