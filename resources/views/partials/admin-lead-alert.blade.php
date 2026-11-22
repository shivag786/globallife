@php $latestLeadId = (int) \App\Models\Lead::max('id'); @endphp
{{-- New-lead alert for anyone who can see leads (Super Admin, Admin, Sub Admins
     with the leads module): polls every 15s, plays an alert sound, shows a red
     toast and keeps the red "Leads" badge in the sidebar up to date. --}}
<div data-lead-alert
     data-poll-url="{{ route('admin.leads.poll') }}"
     data-leads-url="{{ route('admin.leads.index') }}"
     data-latest-id="{{ $latestLeadId }}"
     class="d-none"></div>
<script>
(function () {
    var root = document.querySelector('[data-lead-alert]');
    if (!root) return;

    var pollUrl = root.dataset.pollUrl;
    var leadsUrl = root.dataset.leadsUrl;
    var lastSeen = parseInt(root.dataset.latestId || '0', 10);
    var audioCtx = null;
    var POLL_MS = 15000;

    // Browsers only allow sound after the user has clicked / pressed a key on
    // the page, so the audio engine is unlocked on the first interaction.
    function unlockAudio() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch (e) { /* no Web Audio: the toast and badge still work */ }
    }
    ['pointerdown', 'keydown', 'touchstart'].forEach(function (ev) {
        document.addEventListener(ev, unlockAudio, { passive: true });
    });

    // Three quick rising beeps, repeated twice: distinct from the order chime.
    function alertSound() {
        if (!audioCtx || audioCtx.state !== 'running') return;
        var t0 = audioCtx.currentTime;
        [0, 0.9].forEach(function (offset) {
            [[784, 0], [988, 0.15], [1319, 0.3]].forEach(function (n) {
                var osc = audioCtx.createOscillator();
                var gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.value = n[0];
                var start = t0 + offset + n[1];
                gain.gain.setValueAtTime(0.0001, start);
                gain.gain.exponentialRampToValueAtTime(0.5, start + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.22);
                osc.connect(gain).connect(audioCtx.destination);
                osc.start(start);
                osc.stop(start + 0.24);
            });
        });
    }

    function updateBadge(unseen) {
        document.querySelectorAll('[data-lead-badge]').forEach(function (b) {
            b.textContent = unseen > 99 ? '99+' : String(unseen);
            b.classList.toggle('d-none', unseen <= 0);
        });
    }

    function toast(count, names) {
        var el = document.createElement('a');
        el.href = leadsUrl;
        el.textContent = '🔔 NEW LEAD' + (count > 1 ? 'S (' + count + ')' : '') + ': ' + names.join(', ') + ' — view';
        el.style.cssText = 'position:fixed;top:16px;right:16px;z-index:2000;background:#dc2626;color:#fff;' +
            'font-size:14px;font-weight:700;padding:12px 18px;border-radius:12px;text-decoration:none;max-width:90vw;' +
            'box-shadow:0 10px 30px rgba(220,38,38,.45);opacity:0;transform:translateY(-8px);' +
            'transition:opacity .25s,transform .25s;';
        document.body.appendChild(el);
        requestAnimationFrame(function () { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
        setTimeout(function () {
            el.style.opacity = '0'; el.style.transform = 'translateY(-8px)';
            setTimeout(function () { el.remove(); }, 300);
        }, 10000);
    }

    // On the dashboard, swap in the fresh red "New Leads" list without a page
    // reload (a reload would lose the browser's permission to play sound).
    function refreshLeadPanel() {
        var slot = document.querySelector('[data-reload-on-lead]');
        if (!slot) return;
        fetch(window.location.href, { headers: { Accept: 'text/html' } })
            .then(function (r) { return r.ok ? r.text() : Promise.reject(r); })
            .then(function (html) {
                var fresh = new DOMParser().parseFromString(html, 'text/html').querySelector('[data-reload-on-lead]');
                if (fresh) slot.innerHTML = fresh.innerHTML;
            })
            .catch(function () {});
    }

    function poll() {
        fetch(pollUrl + '?since=' + lastSeen, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
            .then(function (data) {
                if (!data) return;
                updateBadge(data.unseen);
                if (data.new > 0) {
                    lastSeen = data.latest_id;
                    alertSound();
                    toast(data.new, data.names || []);
                    if (document.title.indexOf('🔔') === -1) document.title = '🔔 ' + document.title;
                    refreshLeadPanel();
                }
            })
            .catch(function () { /* transient error — try again next tick */ });
    }

    setInterval(poll, POLL_MS);
})();
</script>
