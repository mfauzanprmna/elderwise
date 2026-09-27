# ElderWiSE — Laravel 12

Starter platform belajar ElderWiSE berbasis Laravel 12, dengan:
- Landing page modern biru-putih.
- Login/register manual berbasis Laravel Auth session.
- Dashboard journey Level 1–6.
- Learning view full-screen: menu module/submodule di kiri, konten di kanan.
- Circle progress untuk level.
- Lesson text, PDF reader via iframe, dan materi + quiz.
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
7. Jalankan `php artisan storage:link`.
8. Jalankan `php artisan serve`.

Akun admin seed:
- Email: admin@elderwise.test
- Password: password123

> Ganti password admin setelah instalasi. Untuk produksi, gunakan HTTPS, rate limiting, password policy, validasi upload yang lebih ketat, dan penyimpanan file yang sesuai.

## Menambah PDF + Quiz

Masuk sebagai admin → Admin Materi → Tambah Materi. Tipe `PDF` akan menampilkan PDF di panel kanan tanpa sidebar. Tipe `Materi + Quiz` menyimpan pertanyaan dalam JSON seperti:

```json
[
  {"question":"Apa arti upload?","options":["Mengirim file","Mencetak file"],"answer":0}
]
```
