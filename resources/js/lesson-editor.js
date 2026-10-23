// CKEditor 5 untuk isi materi (halaman tambah/edit materi di admin).
// Hanya dimuat di halaman form materi supaya halaman lain tetap ringan.
import {
    ClassicEditor, Essentials, Paragraph, Heading, Bold, Italic, Underline, Strikethrough,
    Link, AutoLink, List, Indent, IndentBlock, BlockQuote, Table, TableToolbar,
    HorizontalLine, Alignment, Highlight, RemoveFormat, PasteFromOffice, Autoformat, Image,
    ImageToolbar,
    ImageCaption,
    ImageStyle,
    ImageResize,
    ImageUpload,
    SimpleUploadAdapter,
} from 'ckeditor5';
import idTranslations from 'ckeditor5/translations/id.js';
import 'ckeditor5/ckeditor5.css';

/**
 * Materi lama yang tersimpan sebagai teks polos (tanpa tag HTML, dengan baris baru biasa atau
 * "\n" literal) diubah jadi paragraf supaya tampil rapi saat dibuka di editor.
 */
function legacyTextToHtml(value) {
    if (!value || /<[a-z][\s\S]*>/i.test(value)) return value;
    const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return value
        .split(/(?:\\n|\r?\n){2,}/)
        .map((p) => p.trim())
        .filter(Boolean)
        .map((p) => '<p>' + esc(p).replace(/(?:\\n|\r?\n)/g, '<br>') + '</p>')
        .join('');
}

const textarea = document.querySelector('textarea[data-ckeditor]');

if (textarea) {
    textarea.value = legacyTextToHtml(textarea.value);

    ClassicEditor.create(textarea, {
        licenseKey: 'GPL',
        language: 'id',
        translations: [idTranslations],
        plugins: [
            Essentials, Paragraph, Heading, Bold, Italic, Underline, Strikethrough,
            Link, AutoLink, List, Indent, IndentBlock, BlockQuote, Table, TableToolbar,
            HorizontalLine, Alignment, Highlight, RemoveFormat, PasteFromOffice, Autoformat, Image,
            ImageToolbar,
            ImageCaption,
            ImageStyle,
            ImageResize,
            ImageUpload,
            SimpleUploadAdapter,
        ],
        toolbar: {
            items: [
                'undo', 'redo', '|',
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', 'highlight', 'removeFormat', '|',
                'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                'alignment', 'link', 'uploadImage', 'blockQuote', 'insertTable', 'horizontalLine',
            ],
            shouldNotGroupWhenFull: true,
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph' },
                { model: 'heading2', view: 'h2', title: 'Judul Besar', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Sub Judul', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Judul Kecil', class: 'ck-heading_heading4' },
            ],
        },
        table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
        },
        link: {
            defaultProtocol: 'https://',
            decorators: {
                openInNewTab: {
                    mode: 'manual',
                    label: 'Buka di tab baru',
                    attributes: { target: '_blank', rel: 'noopener noreferrer' },
                },
            },
        },
        image: {
            toolbar: [
                'imageStyle:inline',
                'imageStyle:block',
                'imageStyle:side',
                '|',
                'toggleImageCaption',
                'imageTextAlternative',
            ],
        },

        simpleUpload: {
            uploadUrl: textarea.dataset.uploadUrl,
            headers: {
                'X-CSRF-TOKEN': textarea.closest('form').querySelector('input[name="_token"]').value,
            },
        },
        highlight: {
            options: [
                { model: 'yellowMarker', class: 'marker-yellow', title: 'Stabilo kuning', color: '#fff3a3', type: 'marker' },
                { model: 'greenMarker', class: 'marker-green', title: 'Stabilo hijau', color: '#bdf2c8', type: 'marker' },
                { model: 'pinkMarker', class: 'marker-pink', title: 'Stabilo merah muda', color: '#ffd1e0', type: 'marker' },
                { model: 'blueMarker', class: 'marker-blue', title: 'Stabilo biru', color: '#cfe4ff', type: 'marker' },
            ],
        },
        placeholder: 'Tulis isi materi di sini. Gunakan toolbar untuk judul, tebal, miring, daftar, dan tabel.',
    })
        .then((editor) => {
            window.lessonEditor = editor; // dipakai juga untuk pengujian otomatis

            // Pastikan isi editor tersalin ke textarea tepat sebelum form dikirim.
            const form = textarea.closest('form');
            if (form) {
                let waitingForUploads = false;

                form.addEventListener('submit', (event) => {
                    const pendingActions = editor.plugins.get('PendingActions');
                    if (!pendingActions.hasAny) {
                        editor.updateSourceElement();
                        return;
                    }

                    event.preventDefault();
                    if (waitingForUploads) return;
                    waitingForUploads = true;

                    const submitter = event.submitter;
                    const resumeSubmit = () => {
                        if (pendingActions.hasAny) return;

                        pendingActions.off('change:hasAny', resumeSubmit);
                        waitingForUploads = false;
                        editor.updateSourceElement();
                        if (submitter) {
                            form.requestSubmit(submitter);
                        } else {
                            form.requestSubmit();
                        }
                    };

                    pendingActions.on('change:hasAny', resumeSubmit);
                });
            }
        })
        .catch((error) => {
            console.error('CKEditor gagal dimuat:', error);
            // Jika editor gagal dimuat, textarea biasa tetap bisa dipakai (HTML mentah).
            textarea.hidden = false;
            textarea.rows = 14;
        });
}
