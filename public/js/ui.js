/*
 * ElderWiSE — antarmuka bersama (dipakai layout peserta & admin).
 *
 *  1. Notifikasi (toast): berhasil / gagal / perhatian / informasi. Pesan dari server dibaca dari #ew-flash.
 *     Skrip lain dapat memanggil  window.ewNotify('success' | 'error' | 'warning' | 'info', 'pesan' | ['pesan1', ...], {title, duration})
 *  2. Animasi loading: bilah di atas halaman + tombol berubah jadi "Memproses…" + lapisan "sedang memproses"
 *     yang baru muncul bila prosesnya lebih dari ~0,6 detik (supaya tidak berkedip pada proses yang cepat).
 *     Tidak aktif untuk: [data-no-loading], link ke tab baru / unduhan / anchor, atau klik dengan Ctrl/Cmd/Shift.
 *     Link unduhan yang tidak meninggalkan halaman: beri atribut data-loading-max="6000" (milidetik).
 *  3. Menu profil (dropdown) menutup saat klik di luar atau menekan Esc.
 *  4. Pilihan file: validasi ukuran sebelum diunggah (data-max-kb) dan pratinjau foto profil (data-avatar-preview).
 */
(function () {
    'use strict';

    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

    // ================================================================ 1. Notifikasi

    var TYPES = {
        success: { title: 'Berhasil', role: 'status', duration: 8000 },
        error:   { title: 'Gagal', role: 'alert', duration: 14000 },
        warning: { title: 'Perhatian', role: 'status', duration: 11000 },
        info:    { title: 'Informasi', role: 'status', duration: 8000 }
    };

    var SVG_OPEN = '<svg class="ew-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
    var ICONS = {
        success: SVG_OPEN + '<circle cx="12" cy="12" r="9.5"/><path d="M7.5 12.5l3 3 6-6.5"/></svg>',
        error:   SVG_OPEN + '<circle cx="12" cy="12" r="9.5"/><path d="M12 7.5v5.5M12 16.5h.01"/></svg>',
        warning: SVG_OPEN + '<path d="M12 3.5l9.5 16.5h-19L12 3.5z"/><path d="M12 10v4.5M12 17.5h.01"/></svg>',
        info:    SVG_OPEN + '<circle cx="12" cy="12" r="9.5"/><path d="M12 11v5.5M12 7.5h.01"/></svg>'
    };

    function toastBox() {
        var box = document.getElementById('ew-toasts');
        if (!box) {
            box = document.createElement('div');
            box.id = 'ew-toasts';
            box.setAttribute('aria-label', 'Notifikasi');
            document.body.appendChild(box);
        }
        return box;
    }

    function notify(type, message, opts) {
        opts = opts || {};
        type = TYPES[type] ? type : 'info';
        var meta = TYPES[type];

        var messages = (Array.isArray(message) ? message : [message])
            .filter(function (m) { return m !== null && m !== undefined && String(m).trim() !== ''; })
            .map(String);
        if (!messages.length) { return null; }

        var box = toastBox();
        var key = type + '|' + messages.join('|');

        // Jangan menumpuk notifikasi yang persis sama.
        for (var i = 0; i < box.children.length; i++) {
            if (box.children[i].getAttribute('data-key') === key) { return box.children[i]; }
        }
        // Tampilkan paling banyak 4 sekaligus.
        while (box.children.length >= 4) { box.removeChild(box.firstChild); }

        var el = document.createElement('div');
        el.className = 'ew-toast ew-toast-' + type;
        el.setAttribute('role', meta.role);
        el.setAttribute('data-key', key);
        el.insertAdjacentHTML('afterbegin', ICONS[type]);

        var body = document.createElement('div');
        var title = document.createElement('strong');
        title.className = 'ew-toast-title';
        title.textContent = opts.title || meta.title;
        body.appendChild(title);

        if (messages.length === 1) {
            var p = document.createElement('div');
            p.className = 'ew-toast-msg';
            p.textContent = messages[0];
            body.appendChild(p);
        } else {
            var ul = document.createElement('ul');
            ul.className = 'ew-toast-list';
            messages.forEach(function (m) {
                var li = document.createElement('li');
                li.textContent = m;
                ul.appendChild(li);
            });
            body.appendChild(ul);
        }
        el.appendChild(body);

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'ew-toast-close';
        close.setAttribute('aria-label', 'Tutup notifikasi');
        close.innerHTML = '&times;';
        el.appendChild(close);

        var remaining = opts.duration || meta.duration;
        var started = 0;
        var timer = null;

        function dismiss() {
            window.clearTimeout(timer);
            if (!el.parentNode) { return; }
            el.classList.add('is-leaving');
            window.setTimeout(function () { if (el.parentNode) { el.parentNode.removeChild(el); } }, reduceMotion ? 0 : 220);
        }
        function start() { started = Date.now(); timer = window.setTimeout(dismiss, Math.max(remaining, 1000)); }
        function pause() { window.clearTimeout(timer); remaining -= Date.now() - started; }

        close.addEventListener('click', dismiss);
        el.addEventListener('mouseenter', pause);
        el.addEventListener('mouseleave', start);
        el.addEventListener('focusin', pause);
        el.addEventListener('focusout', start);

        box.appendChild(el);
        start();
        return el;
    }

    window.ewNotify = notify;

    // Pesan dari server (session flash & error validasi), disiapkan oleh partials/flash.blade.php
    var flashEl = document.getElementById('ew-flash');
    if (flashEl) {
        try {
            JSON.parse(flashEl.textContent || '[]').forEach(function (item) {
                notify(item.type, item.messages, { title: item.title || undefined, duration: item.duration || undefined });
            });
        } catch (e) { /* abaikan data yang rusak */ }
    }

    // ================================================================ 2. Animasi loading

    var topbar = null;
    var overlay = null;
    var overlayText = null;
    var overlaySub = null;
    var loadingTimers = { overlay: null, safety: null, reset: null };
    var busyButtons = [];
    var busyForms = [];
    var loadingActive = false;

    function ensureTopbar() {
        if (!topbar) {
            topbar = document.createElement('div');
            topbar.id = 'ew-topbar';
            topbar.setAttribute('aria-hidden', 'true');
            document.body.appendChild(topbar);
        }
        return topbar;
    }

    function ensureOverlay() {
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'ew-overlay';
            overlay.setAttribute('role', 'status');
            overlay.setAttribute('aria-live', 'polite');

            var card = document.createElement('div');
            card.className = 'ew-overlay-card';
            var spin = document.createElement('span');
            spin.className = 'ew-spinner ew-spinner-lg';
            spin.setAttribute('aria-hidden', 'true');
            var wrap = document.createElement('p');
            overlayText = document.createElement('span');
            overlaySub = document.createElement('small');
            wrap.appendChild(overlayText);
            wrap.appendChild(overlaySub);
            card.appendChild(spin);
            card.appendChild(wrap);
            overlay.appendChild(card);
            document.body.appendChild(overlay);
        }
        return overlay;
    }

    function startLoading(opts) {
        opts = opts || {};
        window.clearTimeout(loadingTimers.overlay);
        window.clearTimeout(loadingTimers.safety);
        window.clearTimeout(loadingTimers.reset);

        var bar = ensureTopbar();
        bar.className = '';
        void bar.offsetWidth; // mulai animasi dari awal
        bar.className = 'is-running';

        ensureOverlay();
        overlayText.textContent = opts.message || 'Memuat…';
        overlaySub.textContent = opts.sub || '';

        loadingTimers.overlay = window.setTimeout(function () {
            if (loadingActive) { overlay.classList.add('is-visible'); }
        }, opts.delay === undefined ? 600 : opts.delay);

        // Pengaman: bila halaman tidak berpindah (mis. unduhan file), animasi dihentikan sendiri.
        loadingTimers.safety = window.setTimeout(stopLoading, opts.max || 40000);

        loadingActive = true;
        document.documentElement.setAttribute('aria-busy', 'true');
    }

    function restoreButtons() {
        busyButtons.forEach(function (b) {
            if (b.hasAttribute('data-ew-html')) { b.innerHTML = b.getAttribute('data-ew-html'); }
            b.style.minWidth = b.getAttribute('data-ew-minw') || '';
            b.classList.remove('is-busy');
            b.removeAttribute('aria-busy');
            b.removeAttribute('data-ew-html');
            b.removeAttribute('data-ew-minw');
            b.removeAttribute('data-ew-busy');
        });
        busyButtons = [];
        busyForms.forEach(function (f) {
            f.form.removeAttribute('data-ew-submitting');
            f.disabled.forEach(function (b) { b.disabled = false; });
        });
        busyForms = [];
    }

    function stopLoading() {
        window.clearTimeout(loadingTimers.overlay);
        window.clearTimeout(loadingTimers.safety);
        loadingActive = false;
        document.documentElement.removeAttribute('aria-busy');

        if (overlay) { overlay.classList.remove('is-visible'); }
        if (topbar && topbar.className === 'is-running') {
            topbar.className = 'is-done';
            loadingTimers.reset = window.setTimeout(function () { topbar.className = ''; }, 700);
        }
        restoreButtons();
    }

    function setBusy(btn, text) {
        if (!btn || btn.getAttribute('data-ew-busy')) { return; }
        btn.setAttribute('data-ew-busy', '1');
        btn.setAttribute('data-ew-html', btn.innerHTML);
        btn.setAttribute('data-ew-minw', btn.style.minWidth || '');
        btn.style.minWidth = btn.offsetWidth + 'px'; // lebar tetap agar tata letak tidak bergeser
        btn.classList.add('is-busy');
        btn.setAttribute('aria-busy', 'true');

        var spin = document.createElement('span');
        spin.className = 'ew-spinner';
        spin.setAttribute('aria-hidden', 'true');
        var label = document.createElement('span');
        label.textContent = text;
        btn.textContent = '';
        btn.appendChild(spin);
        btn.appendChild(label);
        busyButtons.push(btn);
    }

    // Klik tautan -> pindah halaman
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
        var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!a || a.hasAttribute('data-no-loading') || a.hasAttribute('download')) { return; }
        if (a.getAttribute('aria-disabled') === 'true') { return; }

        var target = a.getAttribute('target');
        if (target && target !== '_self') { return; }

        var href = a.getAttribute('href') || '';
        if (href === '' || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) { return; }
        if (a.origin && a.origin !== window.location.origin) { return; }
        if (a.pathname === window.location.pathname && a.search === window.location.search && a.hash) { return; }

        startLoading({ message: 'Memuat halaman…', max: parseInt(a.getAttribute('data-loading-max'), 10) || 40000 });
    }, false);

    // Kirim form
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.nodeName !== 'FORM') { return; }
        if (form.hasAttribute('data-no-loading') || form.target === '_blank') { return; }

        if (form.getAttribute('data-ew-submitting')) { // cegah kirim ganda (klik/Enter berulang)
            e.preventDefault();
            return;
        }
        if (e.defaultPrevented) { return; } // dibatalkan, mis. oleh konfirmasi "Yakin hapus?"

        var submitter = e.submitter || form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
        var hasFile = [].some.call(form.querySelectorAll('input[type="file"]'), function (i) { return i.files && i.files.length > 0; });

        form.setAttribute('data-ew-submitting', '1');
        var entry = { form: form, disabled: [] };
        busyForms.push(entry);

        var label = (submitter && submitter.getAttribute && submitter.getAttribute('data-loading-text'))
            || (hasFile ? 'Mengunggah…' : 'Memproses…');
        if (submitter && submitter.tagName === 'BUTTON') { setBusy(submitter, label); }

        startLoading({
            message: hasFile ? 'Sedang mengunggah file, mohon tunggu…' : 'Sedang memproses, mohon tunggu…',
            sub: hasFile ? 'Jangan menutup halaman ini.' : '',
            max: hasFile ? 180000 : 60000
        });

        // Nonaktifkan tombol kirim lain SETELAH pengiriman dimulai agar data tombol tetap terbawa.
        window.setTimeout(function () {
            [].forEach.call(form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])'), function (b) {
                if (b !== submitter && !b.disabled) { b.disabled = true; entry.disabled.push(b); }
            });
        }, 0);
    }, false);

    // Tombol "Kembali" di browser memulihkan halaman dari cache: jangan biarkan tampilan loading tertinggal.
    window.addEventListener('pageshow', function (e) { if (e.persisted) { stopLoading(); } });

    window.ewLoading = { start: startLoading, stop: stopLoading };

    // ================================================================ 3. Menu profil

    function closeProfileMenus(except) {
        [].forEach.call(document.querySelectorAll('details.ew-profile[open]'), function (d) {
            if (d !== except) { d.removeAttribute('open'); }
        });
    }

    document.addEventListener('click', function (e) {
        var inside = e.target && e.target.closest ? e.target.closest('details.ew-profile') : null;
        closeProfileMenus(inside);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') { return; }
        var open = document.querySelector('details.ew-profile[open]');
        if (open) {
            open.removeAttribute('open');
            var summary = open.querySelector('summary');
            if (summary) { summary.focus(); }
        }
    });

    // ================================================================ 4. Pilihan file

    function readableSize(kb) {
        return kb >= 1024 ? (Math.round((kb / 1024) * 10) / 10) + ' MB' : kb + ' KB';
    }

    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input || input.type !== 'file') { return; }

        var file = input.files && input.files[0];
        var maxKb = parseInt(input.getAttribute('data-max-kb'), 10);

        if (file && maxKb && file.size > maxKb * 1024) {
            notify('error', 'Ukuran file ' + readableSize(Math.ceil(file.size / 1024)) + ' terlalu besar. Maksimal ' + readableSize(maxKb) + '. Silakan pilih file yang lebih kecil.', { title: 'File tidak bisa dipakai' });
            input.value = '';
            return;
        }

        var previewSel = input.getAttribute('data-avatar-preview');
        var holder = previewSel ? document.querySelector(previewSel) : null;
        if (holder && file && /^image\//.test(file.type) && window.URL && window.URL.createObjectURL) {
            var img = holder.querySelector('img');
            if (!img) {
                img = document.createElement('img');
                img.alt = '';
                holder.appendChild(img);
            }
            img.src = window.URL.createObjectURL(file);
        }
    });
})();
