<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Seeder;

/**
 * Seed aman untuk Module 4-6.
 *
 * Seeder ini TIDAK:
 * - menghapus users
 * - menghapus progress
 * - truncate tabel
 * - membuat akun baru
 *
 * Jalankan dengan:
 * php artisan db:seed --class=Modules456Seeder
 */
class Modules456Seeder extends Seeder
{
    public function run(): void
    {
        $modules = json_decode(<<<'JSON'
[
  {
    "number": 4,
    "title": "My First Digital Teaching Material",
    "slogan": "I Can Create",
    "description": "Membuat karya digital pertama dengan PowerPoint atau Canva.",
    "badge": "Digital Creator",
    "lessons": [
      [
        "4.1",
        "Basic PowerPoint",
        "Mengenal tampilan dasar PowerPoint. Slide adalah seperti halaman dalam buku tulis digital. Kenali Slide, Panel Slide, Toolbar/Ribbon, dan Tab Menu. Latihan: buat 2 slide, hapus 1 slide, simpan dengan nama yang mudah diingat, lalu buka kembali file tersebut.\n\nTakeaway: kuasai dulu New Slide (Ctrl + M), Layout, dan Save (Ctrl + S).",
        [
          [
            "Apa fungsi slide di PowerPoint?",
            [
              "Tempat menulis dan mengisi materi",
              "Tempat menyimpan password",
              "Tempat menghapus aplikasi"
            ],
            0
          ],
          [
            "Shortcut untuk menambah slide baru adalah?",
            [
              "Ctrl + P",
              "Ctrl + M",
              "Ctrl + S"
            ],
            1
          ]
        ]
      ],
      [
        "4.2",
        "Basic Canva",
        "Canva menyediakan template siap pakai untuk membuat materi ajar tanpa harus mendesain dari nol. Cari template seperti “presentasi pendidikan”, pilih desain yang tidak terlalu ramai, lalu sesuaikan teks dan gambar. Hasil dapat diunduh sebagai PDF, PNG, atau PPTX.\n\nTakeaway: pilih, isi, dan sesuaikan sedikit sesuai kebutuhan mengajar.",
        [
          [
            "Apa fungsi template di Canva?",
            [
              "Membuat desain siap pakai yang dapat disesuaikan",
              "Menghapus semua desain",
              "Mengubah password"
            ],
            0
          ],
          [
            "Format yang dapat digunakan untuk mengunduh hasil Canva adalah?",
            [
              "PDF",
              "TXT saja",
              "EXE"
            ],
            0
          ]
        ]
      ],
      [
        "4.3",
        "Images",
        "Gambar membantu murid memahami materi lebih cepat. Gunakan gambar yang relevan dan perhatikan izin penggunaannya. Sumber yang disebut dalam modul antara lain Canva Elements, Unsplash, dan Pixabay. Atur posisi dan ukuran gambar agar tetap rapi.\n\nTakeaway: pilih gambar yang relevan, gratis, dan sesuai konteks mengajar.",
        [
          [
            "Apa yang perlu diperiksa sebelum menggunakan gambar dari internet?",
            [
              "Izin atau lisensinya",
              "Jumlah komentar",
              "Nama folder komputer"
            ],
            0
          ],
          [
            "Apa yang sebaiknya dihindari?",
            [
              "Menggunakan gambar tanpa mengecek izin",
              "Mengatur ukuran gambar",
              "Memilih gambar yang relevan"
            ],
            0
          ]
        ]
      ],
      [
        "4.4",
        "Text",
        "Teks pada slide sebaiknya singkat, jelas, dan mudah dibaca dari jarak jauh. Gunakan kontras yang cukup dan prinsip “Less is More”. Batasi sekitar 5–6 baris per slide agar slide menjadi alat bantu, bukan naskah lengkap.\n\nTakeaway: slide adalah alat bantu, bukan naskah lengkap yang dibacakan.",
        [
          [
            "Berapa baris maksimal yang disarankan per slide?",
            [
              "5–6 baris",
              "20–30 baris",
              "Tidak terbatas"
            ],
            0
          ],
          [
            "Mengapa teks perlu dibuat singkat?",
            [
              "Agar audiens mudah fokus pada pesan utama",
              "Agar file lebih besar",
              "Agar slide sulit dibaca"
            ],
            0
          ]
        ]
      ],
      [
        "4.5",
        "Templates",
        "Template membantu menghemat waktu dan membuat materi terlihat rapi. Pilih template sesuai mata pelajaran, lalu ganti teks dan gambar tanpa memindahkan elemen secara sembarangan agar tata letak tetap terjaga.\n\nTakeaway: template adalah jalan pintas menuju tampilan yang rapi.",
        [
          [
            "Apa manfaat utama template?",
            [
              "Menghemat waktu dan menjaga kerapian",
              "Menghapus isi materi",
              "Mengunci komputer"
            ],
            0
          ],
          [
            "Saat mengganti isi template, sebaiknya?",
            [
              "Menyesuaikan isi tanpa merusak tata letak",
              "Memindahkan semua elemen sembarangan",
              "Menghapus seluruh desain"
            ],
            0
          ]
        ]
      ],
      [
        "4.6",
        "Simple Presentation",
        "Susun presentasi sederhana dengan alur Judul → Tujuan → Isi → Rangkuman. Untuk latihan cukup 3–5 slide. Setelah selesai, coba presentasikan secara singkat kepada rekan sejawat atau keluarga.\n\nFinal Mission: buat satu materi ajar digital sederhana 3–5 slide menggunakan PowerPoint atau Canva, dengan judul, tujuan, minimal 2 gambar, teks singkat, dan rangkuman.",
        [
          [
            "Urutan struktur presentasi sederhana yang dianjurkan adalah?",
            [
              "Judul → Tujuan → Isi → Rangkuman",
              "Gambar → Password → Login",
              "Rangkuman → Judul → Login → Keluar"
            ],
            0
          ],
          [
            "Berapa jumlah slide yang cukup untuk latihan?",
            [
              "3–5 slide",
              "30–50 slide",
              "1.000 slide"
            ],
            0
          ]
        ]
      ]
    ]
  },
  {
    "number": 5,
    "title": "Introduction to AI",
    "slogan": "AI Without Fear",
    "description": "Mengenal dan mencoba AI secara sederhana, aman, dan bertanggung jawab.",
    "badge": "AI Explorer",
    "lessons": [
      [
        "5.1",
        "Welcome to Module 5",
        "AI sudah dekat dengan kehidupan sehari-hari, misalnya rekomendasi video, penerjemah, pengenalan wajah, navigasi, filter kamera, dan chatbot. Modul ini mengajak peserta mengenal AI perlahan dan berani mencoba.\n\nPesan utama: “Saya tidak perlu takut pada AI. Saya bisa belajar menggunakannya sedikit demi sedikit.”",
        [
          [
            "Apa pesan utama Module 5?",
            [
              "Saya tidak perlu takut pada AI dan bisa belajar sedikit demi sedikit",
              "AI harus selalu menggantikan guru",
              "AI hanya untuk ahli komputer"
            ],
            0
          ],
          [
            "Manakah contoh penggunaan AI yang disebut dalam modul?",
            [
              "Rekomendasi video",
              "Buku tulis biasa",
              "Penggaris"
            ],
            0
          ]
        ]
      ],
      [
        "5.2",
        "What Will I Learn?",
        "Perjalanan Module 5: Kenali → Pahami → Coba → Periksa → Gunakan. AI dapat menjadi alat bantu, tetapi bukan pengganti guru. Target pertama adalah berani bertanya kepada AI berdasarkan kebutuhan nyata.",
        [
          [
            "Urutan perjalanan belajar AI dalam modul adalah?",
            [
              "Kenali → Pahami → Coba → Periksa → Gunakan",
              "Gunakan → Percaya → Bagikan → Selesai",
              "Login → Logout → Hapus"
            ],
            0
          ],
          [
            "AI dalam pembelajaran sebaiknya dipandang sebagai?",
            [
              "Alat bantu",
              "Pengganti guru",
              "Penentu semua keputusan"
            ],
            0
          ]
        ]
      ],
      [
        "5.3",
        "What Is Artificial Intelligence?",
        "AI adalah teknologi yang membuat komputer dapat melakukan tugas yang biasanya membutuhkan kemampuan manusia, seperti mengenali gambar, memahami bahasa, mengenali suara, menemukan pola, membuat prediksi, dan memberi rekomendasi. AI bekerja berdasarkan data, pola, model, dan aturan untuk tugas tertentu.",
        [
          [
            "Apa yang dimaksud AI secara sederhana?",
            [
              "Teknologi yang memungkinkan komputer melakukan tugas tertentu yang biasanya membutuhkan kemampuan manusia",
              "Aplikasi untuk mencetak dokumen",
              "Jaringan listrik"
            ],
            0
          ],
          [
            "Manakah contoh AI di sekitar kita?",
            [
              "Penerjemah otomatis",
              "Kertas kosong",
              "Kabel charger"
            ],
            0
          ]
        ]
      ],
      [
        "5.4",
        "What Is Generative AI?",
        "Generative AI dapat membantu menghasilkan konten baru berdasarkan instruksi atau masukan pengguna, seperti teks, ide, ringkasan, gambar, dan kode. AI biasa dapat mengenali pola atau memberi prediksi; Generative AI dapat menghasilkan konten. Hasilnya tetap perlu dipilih, diperbaiki, dan disesuaikan oleh manusia.",
        [
          [
            "Apa yang dapat dibantu oleh Generative AI?",
            [
              "Menghasilkan ide atau draft teks",
              "Menggantikan seluruh keputusan guru",
              "Menjamin semua jawaban benar"
            ],
            0
          ],
          [
            "Apa yang harus dilakukan terhadap hasil Generative AI?",
            [
              "Diperiksa dan disesuaikan oleh manusia",
              "Langsung dipercaya",
              "Selalu dibagikan"
            ],
            0
          ]
        ]
      ],
      [
        "5.5",
        "AI as a Teaching Assistant",
        "AI dapat membantu membuat tujuan pembelajaran, pertanyaan, contoh, variasi latihan, ide aktivitas, ringkasan, draft instruksi, dan menyesuaikan bahasa. Guru tetap memahami kebutuhan peserta didik, menentukan tujuan, memilih informasi, memeriksa hasil, dan mengambil keputusan pembelajaran.",
        [
          [
            "Manakah yang dapat dibantu AI sebagai teaching assistant?",
            [
              "Membuat ide aktivitas",
              "Mengambil semua keputusan pembelajaran",
              "Menggantikan guru"
            ],
            0
          ],
          [
            "Siapa yang tetap mengambil keputusan pembelajaran?",
            [
              "Guru",
              "AI saja",
              "Aplikasi"
            ],
            0
          ]
        ]
      ],
      [
        "5.6",
        "Asking AI: Basic Prompting",
        "Prompt adalah pertanyaan atau instruksi yang diberikan kepada AI. Formula sederhana: Saya ingin + tugas + konteks + hasil yang diinginkan. Contoh yang lebih jelas menyebutkan peran, tugas, konteks, dan bentuk hasil.",
        [
          [
            "Apa itu prompt?",
            [
              "Pertanyaan atau instruksi yang diberikan kepada AI",
              "Password akun",
              "Nama aplikasi"
            ],
            0
          ],
          [
            "Bagian mana yang dapat ditambahkan agar prompt lebih jelas?",
            [
              "Peran, tugas, konteks, dan bentuk hasil",
              "Hanya emoji",
              "Hanya nama pengguna"
            ],
            0
          ]
        ]
      ],
      [
        "5.7",
        "Responsible Use of AI",
        "Gunakan AI dengan aman dan bertanggung jawab. Jangan memasukkan password, nomor identitas, data kesehatan pribadi, informasi keuangan, data pribadi siswa, atau dokumen rahasia sekolah. Jangan langsung percaya hasil AI dan tetap perhatikan hak cipta serta konteks peserta didik.",
        [
          [
            "Mana yang aman diberikan sebagai prompt?",
            [
              "Saya ingin 5 ide kegiatan IPA kelas 5",
              "Password akun sekolah",
              "Nomor identitas siswa"
            ],
            0
          ],
          [
            "Apa prinsip penting penggunaan AI?",
            [
              "Jangan bagikan data sensitif dan periksa hasil AI",
              "Masukkan semua data pribadi",
              "Percaya semua jawaban AI"
            ],
            0
          ]
        ]
      ],
      [
        "5.8",
        "Can I Trust AI? — Fact Checking AI Output",
        "AI dapat menghasilkan jawaban yang terdengar yakin tetapi belum tentu benar. Gunakan prinsip 3C: CHECK (periksa masuk akal), CROSS-CHECK (bandingkan dengan sumber terpercaya), dan CONTEXT (pastikan sesuai kebutuhan dan konteks).",
        [
          [
            "Apa arti CROSS-CHECK?",
            [
              "Membandingkan dengan sumber lain yang dapat dipercaya",
              "Menghapus jawaban AI",
              "Mengganti password"
            ],
            0
          ],
          [
            "Mengapa hasil AI perlu diperiksa?",
            [
              "AI dapat salah atau memberi informasi yang tidak sesuai konteks",
              "AI selalu benar",
              "AI tidak pernah menghasilkan jawaban"
            ],
            0
          ]
        ]
      ],
      [
        "5.9",
        "My First AI Experiment",
        "Eksperimen pertama: tentukan kebutuhan, buat prompt, kirim kepada AI, periksa apakah sesuai/sebagian sesuai/tidak sesuai, perbaiki prompt, lalu simpan hasil yang bermanfaat. Eksperimen tidak harus sempurna; yang penting berani bertanya, membaca, dan memeriksa.",
        [
          [
            "Langkah setelah mengirim prompt kepada AI adalah?",
            [
              "Periksa hasil",
              "Langsung hapus akun",
              "Bagikan semua data pribadi"
            ],
            0
          ],
          [
            "Apa tujuan eksperimen pertama?",
            [
              "Mencoba bertanya, membaca, dan memeriksa hasil AI",
              "Menjadi ahli AI dalam sehari",
              "Menggantikan guru"
            ],
            0
          ]
        ]
      ],
      [
        "5.10",
        "Mission — Ask AI One Question",
        "Final Mission: ajukan satu pertanyaan atau instruksi kepada AI yang berkaitan dengan kegiatan mengajar. Langkahnya: Pilih Kebutuhan → Buat Prompt → Ask AI → Check → Improve → Catat My First AI Result.",
        [
          [
            "Langkah pertama Final Mission adalah?",
            [
              "Pilih Kebutuhan",
              "Langsung membagikan hasil",
              "Memasukkan data sensitif"
            ],
            0
          ],
          [
            "Apa yang harus dilakukan setelah mencoba AI?",
            [
              "Check dan improve jika diperlukan",
              "Langsung percaya",
              "Mengabaikan hasil"
            ],
            0
          ]
        ]
      ]
    ]
  },
  {
    "number": 6,
    "title": "My Digital Confidence",
    "slogan": "Look How Far I’ve Come!",
    "description": "Merefleksikan perkembangan ICT Skills, Digital Confidence, dan AI Awareness serta pencapaian selama program.",
    "badge": "ElderWiSE Achievement",
    "lessons": [
      [
        "6.1",
        "ICT Skills",
        "ICT Skills adalah keterampilan dasar menggunakan perangkat digital seperti membuka aplikasi, menyimpan file, mengetik, dan menyisipkan gambar. Peserta melakukan self-assessment 1–5 dan menuliskan satu keterampilan yang paling terasa berkembang.\n\nTakeaway: ICT Skills berkembang lewat kebiasaan, bukan bakat.",
        [
          [
            "Apa yang dimaksud ICT Skills dalam modul?",
            [
              "Keterampilan dasar menggunakan perangkat dan aplikasi digital",
              "Nilai ujian",
              "Nama sebuah aplikasi AI"
            ],
            0
          ],
          [
            "Bagaimana ICT Skills berkembang?",
            [
              "Lewat kebiasaan dan sering mencoba",
              "Dengan menunggu sampai mahir",
              "Tanpa pernah berlatih"
            ],
            0
          ]
        ]
      ],
      [
        "6.2",
        "Digital Confidence",
        "Digital Confidence bukan soal seberapa jago secara teknis, tetapi seberapa berani mencoba, tidak takut salah, dan mau bangkit saat mengalami kesulitan kecil. Peserta melakukan self-assessment 1–5 dan menuliskan satu momen ketika merasa lebih percaya diri.",
        [
          [
            "Digital Confidence terutama berkaitan dengan?",
            [
              "Keberanian mencoba dan percaya diri menggunakan teknologi",
              "Jumlah aplikasi yang dimiliki",
              "Kecepatan mengetik saja"
            ],
            0
          ],
          [
            "Apa yang dapat dilakukan untuk menumbuhkan percaya diri?",
            [
              "Mencoba sedikit demi sedikit",
              "Menunggu sampai tidak pernah salah",
              "Menghindari teknologi"
            ],
            0
          ]
        ]
      ],
      [
        "6.3",
        "AI Awareness",
        "AI Awareness adalah pemahaman dasar tentang apa itu AI, cara menggunakannya dengan aman, dan kapan hasil AI perlu diperiksa kembali. Self-assessment mencakup pemahaman AI, Generative AI, prompt dasar, pemeriksaan hasil, dan privasi data siswa.",
        [
          [
            "Apa yang termasuk AI Awareness?",
            [
              "Memahami AI dan kapan hasilnya perlu diperiksa",
              "Menghafal semua kode pemrograman",
              "Membuat robot"
            ],
            0
          ],
          [
            "Apa yang penting ketika memakai AI dalam pembelajaran?",
            [
              "Menjaga privasi data siswa",
              "Membagikan data siswa",
              "Mempercayai semua output"
            ],
            0
          ]
        ]
      ],
      [
        "6.4",
        "My Badges",
        "Badge bukan nilai atau ujian, melainkan pengingat bahwa peserta sudah berani melangkah. Tiga lencana yang disebut: Digital Explorer, Digital Creator, dan AI Explorer. Lencana yang belum diperoleh bukan kegagalan, tetapi langkah berikutnya.",
        [
          [
            "Apa makna badge dalam ElderWiSE?",
            [
              "Penanda pencapaian selama perjalanan belajar",
              "Nilai ujian",
              "Hukuman"
            ],
            0
          ],
          [
            "Badge apa yang terkait dengan mencoba alat AI sederhana?",
            [
              "AI Explorer",
              "Digital Creator",
              "Digital Explorer"
            ],
            0
          ]
        ]
      ]
    ]
  }
]
JSON
        , true, 512, JSON_THROW_ON_ERROR);

        foreach ($modules as $moduleData) {
            $module = Module::updateOrCreate(
                ['number' => $moduleData['number']],
                [
                    'title' => $moduleData['title'],
                    'slogan' => $moduleData['slogan'],
                    'description' => $moduleData['description'],
                    'badge' => $moduleData['badge'],
                    'sort_order' => $moduleData['number'],
                ]
            );

            foreach ($moduleData['lessons'] as $index => $lessonData) {
                [$code, $title, $content, $questions] = $lessonData;

                $quiz = array_map(
                    fn ($question) => [
                        'question' => $question[0],
                        'options' => $question[1],
                        'answer' => $question[2],
                    ],
                    $questions
                );

                Lesson::updateOrCreate(
                    [
                        'module_id' => $module->id,
                        'code' => $code,
                    ],
                    [
                        'title' => $title,
                        'type' => 'text',
                        'content' => $content,
                        'quiz' => $quiz,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }

        $this->command?->info('Module 4-6 berhasil di-seed tanpa menghapus akun atau progress.');
    }
}
