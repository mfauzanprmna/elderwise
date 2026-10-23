@php
    /*
     | Satu-satunya tempat yang mengubah pesan dari server menjadi notifikasi (toast) untuk SEMUA form:
     | pesan sukses/gagal dari session, hasil kuis, dan error validasi. Dibaca oleh public/js/ui.js.
     */
    $ewFlash = [];
    $ewPush = function (string $type, $messages, ?string $title = null) use (&$ewFlash) {
        $messages = array_values(array_filter((array) $messages, fn ($m) => filled($m)));
        if ($messages) {
            $ewFlash[] = ['type' => $type, 'messages' => $messages, 'title' => $title];
        }
    };

    $ewPush('success', session('success'));

    if ($ewStatus = session('status')) {
        $ewStatusText = [
            'verification-link-sent' => 'Tautan verifikasi baru sudah dikirim. Silakan periksa email Anda, termasuk folder spam.',
        ];
        $ewPush('success', $ewStatusText[$ewStatus] ?? $ewStatus);
    }

    $ewPush('info', session('info'));
    $ewPush('warning', session('warning'));
    $ewPush('warning', session('locked_notice'), 'Belum bisa dibuka');
    $ewPush('warning', session('quiz_locked'), 'Latihan belum terbuka');
    $ewPush('error', session('error'));
    $ewPush('error', session('mail_error'), 'Email belum terkirim');

    if ($ewQuiz = session('quiz_result')) {
        if (! empty($ewQuiz['passed'])) {
            $ewPush('success', "Jawaban Anda benar semua ({$ewQuiz['score']} dari {$ewQuiz['total']}). Materi ini tercatat selesai!", 'Latihan lulus');
        } else {
            $ewPush('warning', "Skor Anda {$ewQuiz['score']} dari {$ewQuiz['total']}. Belum semua jawaban benar, silakan coba lagi pelan-pelan.", 'Latihan belum lulus');
        }
    }

    if ($errors->any()) {
        $ewAll = array_values(array_unique($errors->all()));
        $ewShown = array_slice($ewAll, 0, 4);
        if (count($ewAll) > 4) {
            $ewShown[] = 'Dan ' . (count($ewAll) - 4) . ' hal lain. Periksa kolom yang bertanda merah.';
        }
        $ewPush('error', $ewShown, 'Data belum bisa diproses');
    }
@endphp

@if($ewFlash)
    <script type="application/json" id="ew-flash">@json($ewFlash)</script>
    {{-- Cadangan bila JavaScript dimatikan: pesan tetap terlihat. --}}
    <noscript>
        <div style="max-width:42rem;margin:1rem auto;padding:0 1rem;font-weight:700;line-height:1.5">
            @foreach($ewFlash as $ewItem)
                <div style="margin-bottom:.6rem;padding:.9rem 1.1rem;border-radius:.8rem;border:2px solid #c9d6e6;background:#fff;color:#16233a">
                    @foreach($ewItem['messages'] as $ewMsg)<div>{{ $ewMsg }}</div>@endforeach
                </div>
            @endforeach
        </div>
    </noscript>
@endif
