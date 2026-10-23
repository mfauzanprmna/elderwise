# ElderWiSE — Laravel 12

Starter platform belajar ElderWiSE berbasis Laravel 12, dengan:
- Landing page modern biru-putih.
- Login/register manual berbasis Laravel Auth session.
- Masuk/daftar dengan akun Google (Laravel Socialite).
- Lupa kata sandi lewat email (tautan berlaku 60 menit).
- Verifikasi email untuk pendaftar baru (akun harus diverifikasi sebelum bisa belajar).
- Belajar berurutan: Modul 1 → 2 → dst. Materi/modul berikutnya terkunci sampai yang sebelumnya selesai.
- Progres materi otomatis lewat checkpoint (tanpa tombol "tandai selesai"), dengan tombol Sebelumnya/Berikutnya.
- Alert SweetAlert saat satu modul selesai.
- Dashboard journey Level 1–6.
- Learning view full-screen: menu module/submodule di kiri, konten di kanan.
- Circle progress untuk level.
- Materi teks, PDF reader via iframe, dan materi + quiz.
- Progress/XP sederhana per pengguna.
- Admin CRUD materi untuk upload PDF dan quiz JSON.
- Seed konten Level 1 dan Level 2 berdasarkan dokumen ElderWiSE yang diberikan.

## Instalasi

1. Pastikan PHP >= 8.2 dan Composer tersedia.
2. Buat project Laravel 12 kosong atau jalankan `composer install` pada folder ini.
3. Salin `.env.example` menjadi `.env`.
4. Buat SQLite: `touch database/database.sqlite`.
5. Jalankan `php artisan key:generate`.
6. Jalankan `php artisan migrate --seed`.
7. Jalankan `php artisan serve`.

(`php artisan storage:link` **tidak diperlukan**: file upload disimpan langsung di `public/uploads`. Lihat bagian "Hosting tanpa terminal".)

Akun admin seed:
- Email: admin@elderwise.test
- Password: password123

> Ganti password admin setelah instalasi. Untuk produksi, gunakan HTTPS, rate limiting, password policy, validasi upload yang lebih ketat, dan penyimpanan file yang sesuai.

## Login dengan Google

Tombol "Masuk dengan Google" ada di halaman Masuk dan Daftar. Perilakunya:
- Sudah pernah masuk lewat Google → langsung masuk.
- Email Google sama dengan akun yang sudah ada → akun ditautkan, lalu masuk. Bila akun itu emailnya **belum pernah diverifikasi**, kata sandi lamanya dicabut (mencegah orang lain yang mendaftar lebih dulu dengan email yang sama ikut memegang akun); pemiliknya bisa membuat kata sandi baru lewat "Lupa kata sandi".
- Email belum terdaftar → akun peserta baru dibuat otomatis (tanpa perlu mengisi formulir).
- Hanya email Google yang **sudah terverifikasi** yang diterima.

Cara mengaktifkan:
1. Buka <https://console.cloud.google.com/apis/credentials> → pilih/buat project.
2. **OAuth consent screen**: isi nama aplikasi & email dukungan. Selama status masih *Testing*, hanya akun yang didaftarkan di **Test users** yang bisa masuk — klik *Publish app* agar semua orang bisa memakainya.
3. **Create Credentials → OAuth client ID → Web application**.
4. Pada **Authorized redirect URIs** isi alamat callback, misalnya `http://127.0.0.1:8000/auth/google/callback`
   (produksi: `https://domain-anda.com/auth/google/callback`). Alamat ini harus **sama persis** dengan `GOOGLE_REDIRECT_URI` — `localhost` dan `127.0.0.1` dianggap berbeda, jadi buka situs lewat alamat yang sama dengan `APP_URL`.
5. Salin Client ID & Client Secret ke `.env`:
   ```
   GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=xxxx
   GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
   ```
6. Jalankan `composer install` (ada paket baru `laravel/socialite`) dan `php artisan migrate` (menambah kolom `users.google_id`), lalu `php artisan config:clear`.

Jika kunci Google belum diisi, tombol tetap tampil tetapi menampilkan pesan bahwa fitur belum diaktifkan.

## Lupa kata sandi

Halaman Masuk → **Lupa kata sandi?** → masukkan email → buka tautan di email → buat kata sandi baru.
Pengguna yang mendaftar lewat Google juga bisa membuat kata sandi lewat alur ini.

Pengaturan email di `.env`:
- **Uji lokal** (bawaan): `MAIL_MAILER=log`. Email tidak benar-benar terkirim; tautan reset dicatat di `storage/logs/laravel.log`
  (cari baris `[Lupa kata sandi] Tautan untuk ...`).
- **Email sungguhan**: gunakan SMTP, misalnya
  ```
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.gmail.com
  MAIL_PORT=587
  MAIL_USERNAME=alamat@gmail.com
  MAIL_PASSWORD="sandi-aplikasi-16-karakter"   # Gmail: buat "App password", bukan sandi biasa
  MAIL_FROM_ADDRESS="alamat@gmail.com"
  ```
  Port 465 → tambahkan `MAIL_SCHEME=smtps`. Setelah mengubah `.env`, jalankan `php artisan config:clear`.

Catatan keamanan: halaman "Lupa kata sandi" selalu menampilkan pesan yang sama baik email terdaftar maupun tidak, tautan hanya berlaku satu kali dan 60 menit, dan permintaan dibatasi 6 kali per menit.

## Verifikasi email

Pendaftar baru (lewat formulir) otomatis dikirimi email berisi tautan verifikasi dan diarahkan ke halaman "Satu langkah lagi".
Selama belum diverifikasi, dashboard dan materi tidak bisa dibuka; peserta bisa meminta kirim ulang dari halaman itu.

- Tautan bertanda tangan (signed URL), berlaku 60 menit (`config/auth.php` → `verification.expire`).
- Pengaturan email sama dengan fitur "Lupa kata sandi" (lihat bagian di bawah). Dengan `MAIL_MAILER=log`, tautan
  dicatat di `storage/logs/laravel.log` (cari baris `[Verifikasi email] Tautan untuk ...`).
- Otomatis dianggap terverifikasi: masuk dengan Google (Google sudah memverifikasi emailnya), akun yang dibuat admin,
  dan pengguna yang berhasil mengatur ulang kata sandi lewat tautan email.
- **Akun yang sudah ada sebelum fitur ini** ditandai terverifikasi oleh migration `2026_10_18_000003_mark_existing_users_email_verified`
  supaya peserta lama tidak terkunci. Ingin memaksa verifikasi ulang? Hapus file migration itu sebelum `php artisan migrate`.
- Admin yang mengganti email pengguna lain: email baru ditandai belum terverifikasi dan pengguna itu dikirimi tautan verifikasi.

## Alur belajar: berurutan, checkpoint otomatis, alert selesai modul

**Belajar berurutan.** Materi hanya bisa dibuka bila semua materi sebelumnya (di modul yang sama maupun modul sebelumnya) sudah selesai.
Modul/materi yang terkunci tampil dengan ikon gembok; mencoba membukanya lewat alamat langsung akan diarahkan kembali ke posisi
belajar peserta. Materi yang sudah selesai selalu boleh dibuka lagi. Admin dapat membuka semuanya untuk pratinjau.

**Checkpoint otomatis (tanpa tombol "tandai selesai").** Isi materi dibagi menjadi beberapa bagian (sekitar 140 kata per bagian, maksimal 8).
Di akhir tiap bagian ada penanda "Bagian 2 dari 5". Sebuah bagian dihitung lewat bila:
1. penandanya benar-benar sempat tampil di layar (melompat langsung ke bawah tidak cukup),
2. ada waktu baca minimal (batas bawah 6 kata/detik, jauh lebih cepat dari pembaca biasa), dan
3. berurutan: bagian 1, lalu 2, dst.

Bagian terakhir tercapai → materi otomatis selesai. Server memeriksa ulang urutan dan waktunya (`ProgressController@checkpoint`), jadi
tidak cukup hanya mengutak-atik browser. Materi yang punya kuis: kuis baru terbuka setelah materi dibaca sampai akhir, dan materi selesai
setelah lulus kuis. Materi PDF: progres bertambah seiring waktu membaca selama dokumennya terlihat di layar
(isi PDF tidak bisa dipantau dari halaman, jadi ini perkiraan; jumlah bagian mengikuti perkiraan jumlah halaman, minimal 3).
Materi yang kosong (tanpa teks dan tanpa kuis) langsung dianggap selesai agar tidak menahan urutan belajar.

Pengaturan angka-angkanya ada di konstanta awal `app/Models/Lesson.php` (`WORDS_PER_SECOND`, `WORDS_PER_SECTION`, `MAX_SECTIONS`, `PDF_SECONDS_PER_SECTION`).

**Alert selesai modul.** Saat modul terakhir-materinya selesai, muncul SweetAlert "Selamat! Modul N selesai" lengkap dengan lencana dan
tombol "Lanjut ke Modul N+1". Setelah modul terakhir, alert berisi ajakan membuat sertifikat. SweetAlert2 dimuat dari cdnjs dengan versi
tetap (`11.26.25`, lihat `resources/views/learning/show.blade.php`); jika tidak termuat (mis. tanpa internet), dipakai alert bawaan browser.

File terkait: `app/Support/LearningPath.php` (urutan & gembok), `public/js/learning.js`, `public/css/learning.css`
(file statis, tidak perlu `npm run build`).

## Hosting tanpa terminal (tanpa `storage:link`)

**File upload (PDF materi, logo, lencana, latar sertifikat) disimpan di `public/uploads`**, bukan `storage/app/public`.
Folder itu sudah bisa diakses web, sehingga tidak perlu symlink sama sekali. Alamat filenya `/uploads/...`
(diatur di `config/filesystems.php`, disk `public`).

1. **Izin folder**: `public/uploads` harus bisa ditulis oleh PHP (di File Manager atur izin `755`, atau `775` bila upload gagal
   dengan pesan izin/permission).
2. **Struktur hosting berbeda?** Bila isi folder `public` disalin ke `public_html` sementara aplikasinya di folder lain,
   tambahkan di `.env` (buat dulu foldernya di `public_html/uploads`):
   ```
   UPLOADS_PATH=/home/NAMAUSER/public_html/uploads
   UPLOADS_URL=/uploads
   ```
   Subfolder (mis. `domain.com/elderwise`)? Isi `UPLOADS_URL=/elderwise/uploads`.
3. **Sudah ada file lama** di `storage/app/public`? Pindahkan **isinya** (folder `pdfs`, `badges`, `site`, `certificates`) ke
   `public/uploads` lewat File Manager. Alamat di database tidak perlu diubah.
4. **Batas ukuran upload** di hosting biasanya kecil (2–8 MB). PDF materi diizinkan sampai 20 MB, jadi naikkan
   `upload_max_filesize` dan `post_max_size` di pengaturan PHP hosting (mis. "MultiPHP INI Editor" di cPanel) bila PDF besar gagal diunggah.
5. Folder `public/uploads/.htaccess` mencegah skrip dijalankan dari folder upload (lapisan pengaman tambahan).

**Tanpa `php artisan ...`:**
- Setelah mengubah `.env`, hapus file `bootstrap/cache/config.php` lewat File Manager (pengganti `config:clear`).
- Perubahan database dari `php artisan migrate`, bila tidak bisa dijalankan, dapat dikerjakan lewat phpMyAdmin (MySQL):
  ```sql
  -- login Google
  ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL, ADD UNIQUE KEY users_google_id_unique (google_id);
  -- checkpoint membaca
  ALTER TABLE lesson_progress ADD COLUMN checkpoint SMALLINT UNSIGNED NOT NULL DEFAULT 0, ADD COLUMN checkpoint_at TIMESTAMP NULL DEFAULT NULL;
  -- akun lama dianggap sudah terverifikasi email
  UPDATE users SET email_verified_at = NOW() WHERE email_verified_at IS NULL;
  ```
  Migration-nya aman dijalankan belakangan (`migrate`) karena sudah memeriksa kolom yang ada.

## Menambah PDF + Quiz

Masuk sebagai admin → Admin Materi → Tambah Materi. Tipe `PDF` akan menampilkan PDF di panel kanan tanpa sidebar. Tipe `Materi + Quiz` menyimpan pertanyaan dalam JSON seperti:

```json
[
  {"question":"Apa arti upload?","options":["Mengirim file","Mencetak file"],"answer":0}
]
```
