@extends('layouts.app')
@section('content')
<div class="admin-shell narrow-wide">
    <div class="eyebrow">ADMIN · {{ $lesson->exists ? 'EDIT MATERI' : 'MATERI BARU' }}</div>
    <h1>{{ $lesson->exists ? 'Edit materi' : 'Tambah materi' }}</h1>
    <p class="muted">Kode materi dan urutan dibuat otomatis. Quiz bisa diaktifkan atau dimatikan.</p>

    @if($errors->any())
        <div class="alert error-box"><strong>Periksa kembali:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $lesson->exists ? route('admin.lessons.update', $lesson) : route('admin.lessons.store') }}" class="form-stack" id="lesson-form">
        @csrf
        @if($lesson->exists) @method('PUT') @endif

        <div class="form-grid-2">
            <label>Modul
                <select name="module_id" id="module_id" required @disabled($lesson->exists)>
                    @foreach($modules as $module)
                        <option value="{{ $module->id }}" @selected((int)old('module_id', $lesson->module_id) === $module->id)>Modul {{ $module->number }} — {{ $module->title }}</option>
                    @endforeach
                </select>
                @if($lesson->exists)<input type="hidden" name="module_id" value="{{ $lesson->module_id }}">@endif
            </label>
            <label>Kode Materi
                <input value="{{ old('code', $lesson->code) }}" readonly class="input-readonly" id="lesson-code">
                <small>Otomatis mengikuti nomor modul: 11.1, 11.2, dst.</small>
            </label>
        </div>

        <label>Judul Materi<input name="title" value="{{ old('title', $lesson->title) }}" required></label>

        <label>Jenis Isi Materi
            <select name="type" id="content-type" required>
                <option value="text" @selected(old('type', $lesson->type ?: 'text') === 'text')>Teks / Artikel</option>
                <option value="pdf" @selected(old('type', $lesson->type) === 'pdf')>Upload PDF</option>
            </select>
        </label>

        <div id="text-editor-section" class="editor-section">
            <label>Isi Materi</label>
            <div class="rich-editor" data-editor>
                <div class="editor-toolbar">
                    <button type="button" data-command="formatBlock" data-value="P" title="Paragraf">P</button>
                    <button type="button" data-command="formatBlock" data-value="H2">H2</button>
                    <button type="button" data-command="formatBlock" data-value="H3">H3</button>
                    <button type="button" data-command="bold"><strong>B</strong></button>
                    <button type="button" data-command="italic"><em>I</em></button>
                    <button type="button" data-command="insertUnorderedList">• List</button>
                    <button type="button" data-command="insertOrderedList">1. List</button>
                    <button type="button" data-command="createLink">🔗 Link</button>
                    <button type="button" data-command="unlink">Putus Link</button>
                </div>
                <div class="editor-canvas" contenteditable="true" data-editor-canvas>{!! old('content', $lesson->content) !!}</div>
            </div>
            <textarea name="content" id="content-field" hidden></textarea>
            <small>Gunakan Heading, bold, list, dan link seperti menulis artikel/blog.</small>
        </div>

        <div id="pdf-section" class="editor-section" style="display:none">
            <label>File PDF Materi
                @if($lesson->pdf_path)<small>PDF saat ini tersedia. Upload file baru jika ingin menggantinya.</small>@endif
                <input type="file" name="pdf" accept="application/pdf">
            </label>
            @if($lesson->pdf_path)
                <div class="pdf-current">✓ PDF sudah tersimpan</div>
            @endif
        </div>

        <div class="quiz-settings">
            <label class="checkbox-row">
                <input type="checkbox" name="quiz_enabled" value="1" id="quiz-enabled" @checked(old('quiz_enabled', $lesson->quiz ? 1 : 0))>
                <span><strong>Tambahkan Quiz pada submodul ini</strong><small>Opsional. Jika dicentang, quiz akan tampil setelah materi.</small></span>
            </label>
        </div>

        <div id="quiz-section" class="quiz-editor-section" style="display:none">
            <label>Soal Quiz</label>
            <textarea name="quiz_json" id="quiz-json" rows="12" placeholder='[{"question":"Apa fungsi ...?","options":["A","B","C"],"answer":0}]'>{{ old('quiz_json', $lesson->quiz ? json_encode($lesson->quiz, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            <small>Format: <code>[{"question":"Pertanyaan","options":["Pilihan A","Pilihan B","Pilihan C"],"answer":0}]</code>. Nomor answer dimulai dari 0.</small>
        </div>

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('admin.lessons.index', ['module' => $lesson->module_id]) }}">Batal</a>
            <button class="btn btn-primary">Simpan Materi</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('content-type');
    const textSection = document.getElementById('text-editor-section');
    const pdfSection = document.getElementById('pdf-section');
    const quizToggle = document.getElementById('quiz-enabled');
    const quizSection = document.getElementById('quiz-section');
    const canvas = document.querySelector('[data-editor-canvas]');
    const contentField = document.getElementById('content-field');

    function syncType() {
        const isPdf = type.value === 'pdf';
        textSection.style.display = isPdf ? 'none' : 'block';
        pdfSection.style.display = isPdf ? 'block' : 'none';
    }
    function syncQuiz() {
        quizSection.style.display = quizToggle.checked ? 'block' : 'none';
    }
    document.querySelectorAll('[data-command]').forEach(btn => {
        btn.addEventListener('click', function () {
            const command = this.dataset.command;
            if (command === 'createLink') {
                const url = prompt('Masukkan URL link:', 'https://');
                if (url) document.execCommand(command, false, url);
            } else {
                document.execCommand(command, false, this.dataset.value || null);
            }
            canvas.focus();
        });
    });
    document.getElementById('lesson-form').addEventListener('submit', function () {
        contentField.value = canvas.innerHTML;
    });
    type.addEventListener('change', syncType);
    quizToggle.addEventListener('change', syncQuiz);
    syncType(); syncQuiz();
});
</script>
@endpush
@endsection
