@extends('layouts.admin')
@section('content')
    <div class="max-w-4xl mx-auto px-5 md:px-10 py-10 md:py-14">
        <div class="eyebrow">Admin · {{ $lesson->exists ? 'Edit Materi' : 'Materi Baru' }}</div>
        <h1 class="text-3xl md:text-4xl mt-2 mb-1">{{ $lesson->exists ? 'Edit materi' : 'Tambah materi' }}</h1>
        <p class="text-muted text-lg mb-7">Kode materi dan urutan dibuat otomatis. Quiz bisa diaktifkan atau dimatikan.</p>

        @if($errors->any())
            <div class="bg-red-tint text-red rounded-xl px-5 py-4 mb-6">
                <strong>Periksa kembali:</strong>
                <ul class="mt-2 ml-5 list-disc">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ $lesson->exists ? route('admin.lessons.update', $lesson) : route('admin.lessons.store') }}" class="grid gap-5" id="lesson-form">
            @csrf
            @if($lesson->exists) @method('PUT') @endif

            <div class="grid sm:grid-cols-2 gap-5">
                <label class="form-label">
                    Modul
                    <select name="module_id" id="module_id" required class="form-input" @disabled($lesson->exists)>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" @selected((int) old('module_id', $lesson->module_id) === $module->id)>
                                Modul {{ $module->number }} — {{ $module->title }}
                            </option>
                        @endforeach
                    </select>
                    @if($lesson->exists)<input type="hidden" name="module_id" value="{{ $lesson->module_id }}">@endif
                </label>
                <label class="form-label">
                    Kode Materi
                    <input value="{{ old('code', $lesson->code) }}" readonly class="form-input bg-bg cursor-not-allowed" id="lesson-code">
                    <small class="text-muted font-normal">Otomatis mengikuti nomor modul: 11.1, 11.2, dst.</small>
                </label>
            </div>

            <label class="form-label">Judul Materi
                <input class="form-input" name="title" value="{{ old('title', $lesson->title) }}" required>
            </label>

            <label class="form-label">
                Jenis Isi Materi
                <select name="type" id="content-type" required class="form-input">
                    <option value="text" @selected(old('type', $lesson->type ?: 'text') === 'text')>Teks / Artikel</option>
                    <option value="pdf" @selected(old('type', $lesson->type) === 'pdf')>Upload PDF</option>
                </select>
            </label>

            <div id="text-editor-section">
                <label class="form-label mb-2" for="content-field">Isi Materi</label>
                <div class="lesson-editor-wrap">
                    <textarea name="content" id="content-field" data-ckeditor data-upload-url="{{ route('admin.lessons.upload-image') }}" rows="14" class="form-input">{{ old('content', $lesson->content) }}</textarea>
                </div>
                <small class="text-muted block mt-2">Gunakan toolbar untuk judul, format teks, tabel, link, dan upload gambar (JPG, PNG, WebP, atau GIF; maks. 2 MB). Teks dari Word bisa langsung ditempel.</small>
            </div>

            <div id="pdf-section" style="display:none">
                <label class="form-label">
                    File PDF Materi
                    @if($lesson->pdf_path)<small class="text-muted font-normal">PDF saat ini tersedia. Upload file baru jika ingin menggantinya.</small>@endif
                    <input type="file" name="pdf" accept="application/pdf" class="form-input">
                </label>
                @if($lesson->pdf_path)
                    <div class="mt-2 px-3.5 py-2.5 rounded-lg bg-green-tint text-green font-semibold inline-block">✓ PDF sudah tersimpan</div>
                @endif
            </div>

            <div class="border-2 border-line-strong rounded-2xl p-5 bg-blue-50" style="background:#f8fbff">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="quiz_enabled" value="1" id="quiz-enabled" class="mt-1.5 w-5 h-5" @checked(old('quiz_enabled', $lesson->quiz ? 1 : 0))>
                    <span class="flex flex-col gap-1">
                        <strong class="text-navy text-lg">Tambahkan Quiz pada submodul ini</strong>
                        <small class="text-muted">Opsional. Jika dicentang, quiz akan tampil setelah materi.</small>
                    </span>
                </label>
            </div>

            <div id="quiz-section" class="border-2 border-line-strong rounded-2xl p-5 bg-white" style="display:none">
                <div class="flex justify-between items-center mb-1">
                    <label class="form-label mb-0">Soal Quiz</label>
                    <button type="button" id="add-question-btn" class="btn btn-ghost btn-sm">+ Tambah Soal</button>
                </div>
                <p class="text-muted text-base mt-1 mb-4">Tulis pertanyaan, isi pilihan jawaban, lalu tandai bulatan di pilihan yang benar.</p>

                <div id="quiz-questions" class="grid gap-4"></div>

                <template id="question-template">
                    <div class="quiz-question border-2 border-line rounded-2xl p-5 bg-bg">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <span class="eyebrow question-number">Soal 1</span>
                            <button type="button" class="remove-question border-0 bg-transparent text-red font-bold text-sm cursor-pointer">Hapus Soal</button>
                        </div>

                        <label class="form-label">
                            Pertanyaan
                            <input type="text" class="form-input question-text" placeholder="Contoh: Ikon apa yang digunakan untuk melampirkan file?">
                        </label>

                        <div class="grid gap-2.5 mt-4 options-list"></div>

                        <button type="button" class="add-option btn btn-ghost btn-sm mt-3">+ Tambah Pilihan</button>
                    </div>
                </template>

                <template id="option-template">
                    <label class="flex items-center gap-3 bg-white px-4 py-3 border-2 border-line rounded-xl cursor-pointer option-row">
                        <input type="radio" class="option-correct w-5 h-5 shrink-0" title="Tandai sebagai jawaban benar">
                        <input type="text" class="form-input option-text flex-1" placeholder="Isi pilihan jawaban">
                        <button type="button" class="remove-option border-0 bg-transparent text-red font-bold text-sm cursor-pointer shrink-0">✕</button>
                    </label>
                </template>

                <div id="quiz-hidden-fields"></div>
            </div>

            <div class="flex justify-end gap-3 mt-2">
                <a class="btn btn-ghost" href="{{ route('admin.lessons.index', ['module' => $lesson->module_id]) }}">Batal</a>
                <button class="btn btn-primary">Simpan Materi</button>
            </div>
        </form>
    </div>

@push('scripts')
@vite('resources/js/lesson-editor.js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('content-type');
    const textSection = document.getElementById('text-editor-section');
    const pdfSection = document.getElementById('pdf-section');
    const quizToggle = document.getElementById('quiz-enabled');
    const quizSection = document.getElementById('quiz-section');

    function syncType() {
        const isPdf = type.value === 'pdf';
        textSection.style.display = isPdf ? 'none' : 'block';
        pdfSection.style.display = isPdf ? 'block' : 'none';
    }
    function syncQuiz() {
        quizSection.style.display = quizToggle.checked ? 'block' : 'none';
    }
    type.addEventListener('change', syncType);
    quizToggle.addEventListener('change', syncQuiz);
    syncType(); syncQuiz();

    // ===================== QUIZ BUILDER =====================
    const questionsWrap = document.getElementById('quiz-questions');
    const questionTpl = document.getElementById('question-template');
    const optionTpl = document.getElementById('option-template');
    const addQuestionBtn = document.getElementById('add-question-btn');
    const hiddenFieldsWrap = document.getElementById('quiz-hidden-fields');

    function renumberQuestions() {
        questionsWrap.querySelectorAll('.quiz-question').forEach((qEl, i) => {
            qEl.querySelector('.question-number').textContent = 'Soal ' + (i + 1);
            // name unik per soal supaya radio "jawaban benar" tidak bentrok antar soal
            qEl.querySelectorAll('.option-correct').forEach(r => r.name = 'correct-option-' + i);
        });
    }

    function addOption(questionEl, text, isCorrect) {
        const optionsList = questionEl.querySelector('.options-list');
        const node = optionTpl.content.cloneNode(true);
        const row = node.querySelector('.option-row');
        row.querySelector('.option-text').value = text || '';
        row.querySelector('.option-correct').checked = !!isCorrect;
        row.querySelector('.remove-option').addEventListener('click', () => {
            if (optionsList.children.length <= 2) {
                alert('Minimal harus ada 2 pilihan jawaban.');
                return;
            }
            row.remove();
        });
        optionsList.appendChild(node);
    }

    function addQuestion(data) {
        const node = questionTpl.content.cloneNode(true);
        const qEl = node.querySelector('.quiz-question');
        qEl.querySelector('.question-text').value = data?.question || '';
        qEl.querySelector('.remove-question').addEventListener('click', () => {
            if (!confirm('Hapus soal ini?')) return;
            qEl.remove();
            renumberQuestions();
        });
        qEl.querySelector('.add-option').addEventListener('click', () => addOption(qEl, '', false));
        questionsWrap.appendChild(qEl);

        const options = data?.options?.length ? data.options : ['', ''];
        options.forEach((opt, i) => addOption(qEl, opt, i === (data?.answer ?? -1)));

        renumberQuestions();
    }

    addQuestionBtn.addEventListener('click', () => addQuestion());

    // Muat data quiz yang sudah ada (mode edit)
    const existingQuiz = @json($lesson->quiz ?? []);
    if (existingQuiz && existingQuiz.length) {
        existingQuiz.forEach(q => addQuestion(q));
    }

    // Serialisasi ke hidden input terstruktur sebelum submit
    document.getElementById('lesson-form').addEventListener('submit', function () {
        hiddenFieldsWrap.innerHTML = '';
        if (!quizToggle.checked) return;

        questionsWrap.querySelectorAll('.quiz-question').forEach((qEl, qi) => {
            const questionText = qEl.querySelector('.question-text').value;
            addHidden(`quiz[${qi}][question]`, questionText);

            const optionRows = Array.from(qEl.querySelectorAll('.option-row'));
            let answerIndex = 0;
            optionRows.forEach((row, oi) => {
                addHidden(`quiz[${qi}][options][${oi}]`, row.querySelector('.option-text').value);
                if (row.querySelector('.option-correct').checked) answerIndex = oi;
            });
            addHidden(`quiz[${qi}][answer]`, answerIndex);
        });
    });

    function addHidden(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        hiddenFieldsWrap.appendChild(input);
    }

    // Soal awal kalau quiz diaktifkan tapi belum ada soal sama sekali (lesson baru)
    if (quizToggle.checked && questionsWrap.children.length === 0) {
        addQuestion();
    }
    quizToggle.addEventListener('change', () => {
        if (quizToggle.checked && questionsWrap.children.length === 0) {
            addQuestion();
        }
    });
});
</script>
@endpush
@endsection
