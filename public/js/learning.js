/*
 * ElderWiSE — halaman belajar.
 *
 *  1. Progres otomatis, tanpa tombol "tandai selesai": begitu peserta menggulir materi sampai akhir
 *     (penanda #content-end terlihat di layar), progres langsung 100%.
 *       - Materi tanpa latihan: otomatis selesai, tombol "Berikutnya" aktif.
 *       - Materi dengan latihan: latihan terbuka; selesai setelah lulus latihan.
 *  2. Tombol kecil di tepi menu belajar untuk menutup / membuka menu (pilihan diingat di browser).
 *  3. Saat sebuah modul tuntas, ditampilkan alert SweetAlert (cadangan alert biasa bila CDN tidak termuat).
 */
(function () {
    'use strict';

    var cfgEl = document.getElementById('learning-config');
    var cfg = {};
    try { cfg = cfgEl ? JSON.parse(cfgEl.textContent) : {}; } catch (e) { cfg = {}; }

    // ------------------------------------------------------------ util

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function $(id) { return document.getElementById(id); }

    // Notifikasi memakai sistem toast bersama (ui.js); aman bila ui.js tidak termuat.
    function toast(type, message, opts) { if (window.ewNotify) { window.ewNotify(type, message, opts); } }

    // ------------------------------------------------------------ menu belajar: buka / tutup

    (function initMenuToggle() {
        var grid = $('learn-grid');
        var btn = $('learning-toggle');
        if (!grid || !btn) { return; }

        var KEY = 'ew-learn-menu';

        function apply(collapsed, remember) {
            if (collapsed) { grid.classList.add('is-collapsed'); } else { grid.classList.remove('is-collapsed'); }
            var label = collapsed ? 'Buka menu belajar' : 'Tutup menu belajar';
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            btn.setAttribute('aria-label', label);
            btn.setAttribute('title', label);
            if (remember) {
                try { window.localStorage.setItem(KEY, collapsed ? 'closed' : 'open'); } catch (e) { /* diabaikan */ }
            }
        }

        apply(grid.classList.contains('is-collapsed'), false);
        btn.addEventListener('click', function () { apply(!grid.classList.contains('is-collapsed'), true); });
    })();

    // ------------------------------------------------------------ alert

    function celebrate(m) {
        if (!m) { return; }

        var allDone = !!m.all_done;
        var hasNext = !allDone && m.next && m.next.url;
        var title = allDone ? 'Selamat! Semua modul selesai' : 'Selamat! Modul ' + m.number + ' selesai';

        var medal = m.badge_image
            ? '<img class="ew-swal-medal-img" alt="" src="' + esc(m.badge_image) + '">'
            : '<span class="ew-swal-medal-icon" aria-hidden="true">' + esc(m.badge_icon) + '</span>';

        var html = '<div class="ew-swal-medal">' + medal + '</div>'
            + '<p class="ew-swal-lead">Anda telah menyelesaikan <b>' + esc(m.title) + '</b>.</p>'
            + (m.badge ? '<p class="ew-swal-sub">Lencana \u201c' + esc(m.badge) + '\u201d kini masuk ke koleksi Anda.</p>' : '')
            + (allDone ? (m.certificate_requested === false
                ? '<p class="ew-swal-sub">Pengajuan sertifikat belum terkirim. Buka Dashboard untuk mencoba lagi.</p>'
                : '<p class="ew-swal-sub">Pengajuan sertifikat sudah dikirim otomatis dan menunggu persetujuan admin.</p>') : '');

        var confirmText = hasNext ? 'Lanjut ke Modul ' + m.next.number : (allDone ? 'Lihat Dashboard' : 'Ke Dashboard');
        var targetUrl = hasNext ? m.next.url : m.dashboard_url;

        if (window.Swal) {
            window.Swal.fire({
                title: title,
                html: html,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Tutup',
                reverseButtons: true,
                allowOutsideClick: false,
                buttonsStyling: false,
                customClass: {
                    popup: 'ew-swal-popup',
                    title: 'ew-swal-title',
                    htmlContainer: 'ew-swal-html',
                    confirmButton: 'ew-swal-confirm',
                    cancelButton: 'ew-swal-cancel'
                }
            }).then(function (result) {
                if (result.isConfirmed && targetUrl) { window.location.href = targetUrl; }
            });
            return;
        }

        // Cadangan bila SweetAlert gagal dimuat (mis. tanpa internet).
        var message = title + '\n\nAnda telah menyelesaikan ' + m.title + '.\n\n' + confirmText + '?';
        if (window.confirm(message) && targetUrl) { window.location.href = targetUrl; }
    }

    function notifyLocked(message) {
        message = message || 'Selesaikan materi sebelumnya terlebih dahulu, ya.';
        if (window.Swal) {
            window.Swal.fire({
                icon: 'info',
                title: 'Belum bisa dibuka',
                text: message,
                confirmButtonText: 'Mengerti',
                buttonsStyling: false,
                customClass: { popup: 'ew-swal-popup', title: 'ew-swal-title', htmlContainer: 'ew-swal-html', confirmButton: 'ew-swal-confirm' }
            });
        } else {
            window.alert(message);
        }
    }

    // Tautan terkunci (menu & tombol "berikutnya") tidak berpindah halaman; beri penjelasan ramah.
    document.addEventListener('click', function (e) {
        var link = e.target.closest ? e.target.closest('a[aria-disabled="true"]') : null;
        if (!link) { return; }
        e.preventDefault();
        notifyLocked(link.getAttribute('data-locked-msg'));
    });

    // Alert selesai-modul yang dikirim server setelah lulus kuis / materi otomatis selesai.
    if (cfg.celebrate) { celebrate(cfg.celebrate); }

    // Halaman tanpa materi (modul kosong): tidak ada yang dilacak.
    if (!cfg.finishUrl) { return; }

    // ------------------------------------------------------------ progres: sampai akhir = 100%

    var completed = !!cfg.completed;
    var hasQuiz = !!cfg.hasQuiz;

    var el = {
        chip: $('done-chip'), next: $('next-btn'), nextHint: $('next-hint'),
        quizLock: $('quiz-lock'), quizSet: $('quiz-fieldset'), status: $('lesson-status')
    };

    var STATUS_TEXT = {
        done: '100% \u00b7 Materi selesai',
        quiz: 'Tinggal mengerjakan latihan',
        todo: '0% \u00b7 Belum selesai'
    };

    function setStatus(state) {
        if (!el.status) { return; }
        el.status.setAttribute('data-state', state);
        el.status.textContent = STATUS_TEXT[state];
    }

    function enableNext() {
        if (el.next) {
            el.next.classList.remove('is-disabled');
            el.next.removeAttribute('aria-disabled');
            el.next.removeAttribute('tabindex');
        }
        if (el.nextHint) { el.nextHint.hidden = true; }
    }

    function unlockQuiz() {
        if (el.quizSet) { el.quizSet.disabled = false; el.quizSet.classList.remove('is-locked'); }
        if (el.quizLock) { el.quizLock.hidden = true; }
    }

    function unlockLink(link) {
        if (!link) { return; }
        link.classList.remove('is-locked');
        link.removeAttribute('aria-disabled');
        link.removeAttribute('tabindex');
        link.removeAttribute('title');
    }

    function setRing(moduleId, percent) {
        var ring = $('module-ring-' + moduleId);
        if (!ring) { return; }
        ring.style.background = 'conic-gradient(var(--color-blue) ' + percent + '%, var(--color-blue-tint) 0)';
        var label = ring.querySelector('.ew-ring-label');
        if (label) { label.textContent = percent + '%'; }
    }

    function updateSidebar(d) {
        if (d.overall) {
            var count = $('overall-count'), bar = $('overall-bar');
            if (count) { count.textContent = d.overall.done; }
            if (bar) { bar.style.width = d.overall.percent + '%'; }
        }
        if (cfg.moduleId && typeof d.module_percent === 'number') { setRing(cfg.moduleId, d.module_percent); }

        if (d.completed) {
            var current = $('nav-lesson-' + cfg.lessonId);
            if (current) { current.classList.add('is-done'); }
        }
        if (d.unlocked) {
            unlockLink($('nav-lesson-' + d.unlocked.lesson_id));
            var moduleLink = document.querySelector('.ew-module[data-module-id="' + d.unlocked.module_id + '"]');
            if (moduleLink && moduleLink.classList.contains('is-locked')) {
                unlockLink(moduleLink);
                setRing(d.unlocked.module_id, 0);
            }
        }
    }

    function onCompleted(d) {
        completed = true;
        setStatus('done');
        if (el.chip) { el.chip.hidden = false; }
        enableNext();
        if (d.module_completed) {
            window.setTimeout(function () { celebrate(d.module_completed); }, 500);
        } else {
            toast('success', 'Materi ini sudah selesai. Anda dapat melanjutkan ke materi berikutnya.', { title: 'Materi Selesai' });
        }
    }

    // ------------------------------------------------------------ kirim "sudah sampai akhir"

    var sending = false;
    var finished = false;
    var attempts = 0;
    var observer = null;
    var poller = null;

    function stopWatching() {
        if (observer) { observer.disconnect(); observer = null; }
        if (poller) { window.clearInterval(poller); poller = null; }
    }

    function finish() {
        if (sending || finished || completed) { return; }
        sending = true;

        fetch(cfg.finishUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': cfg.csrf
            },
            body: '{}'
        })
        .then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) { return { status: res.status, data: data }; });
        })
        .then(function (r) {
            sending = false;
            var d = r.data || {};

            if (r.status === 200 && d.ok) {
                finished = true;
                attempts = 0;
                stopWatching();
                updateSidebar(d);

                if (d.completed) {
                    onCompleted(d);
                    if (d.certificate_request_pending) {
                        toast('warning', 'Modul terakhir selesai, tetapi pengajuan sertifikat tertunda. Buka Dashboard untuk mencoba lagi.', { title: 'Sertifikat tertunda' });
                    }
                } else if (d.reading_done && hasQuiz) {
                    unlockQuiz();
                    setStatus('quiz');
                    toast('info', 'Materi sudah sampai akhir. Silakan kerjakan latihan di bawah.', { title: 'Latihan terbuka' });
                }
                return;
            }

            // 403 (terkunci), 419 (sesi habis), dll.: hentikan dan beri tahu.
            finished = true;
            stopWatching();
            toast('error', r.status === 419
                ? 'Sesi Anda sudah berakhir. Muat ulang halaman ini lalu masuk kembali bila diminta.'
                : (r.status === 403
                    ? 'Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu.'
                    : 'Progres belum dapat disimpan. Muat ulang halaman ini lalu coba lagi.'),
                { title: 'Progres tidak tersimpan' });
        })
        .catch(function () {
            // Gangguan jaringan: coba lagi beberapa kali.
            sending = false;
            attempts += 1;
            if (attempts === 1) {
                toast('warning', 'Koneksi internet terganggu. Progres akan dicoba disimpan lagi otomatis.', { title: 'Koneksi terganggu' });
            }
            if (attempts <= 3) {
                window.setTimeout(finish, 4000 * attempts);
            } else {
                finished = true;
                stopWatching();
                toast('error', 'Progres belum bisa disimpan. Muat ulang halaman ini lalu gulir materi sampai akhir lagi.', { title: 'Progres tidak tersimpan' });
            }
        });
    }

    // ------------------------------------------------------------ pantau penanda akhir materi

    var endMark = $('content-end');
    if (!cfg.needsReading || completed || !endMark) { return; }

    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver(function (entries) {
            for (var i = 0; i < entries.length; i++) {
                if (entries[i].isIntersecting) { finish(); break; }
            }
        }, { threshold: 0 });
        observer.observe(endMark);
    } else {
        // Peramban lama tanpa IntersectionObserver: periksa posisi secara berkala.
        poller = window.setInterval(function () {
            if (endMark.getBoundingClientRect().top < window.innerHeight) { finish(); }
        }, 700);
    }
})();
