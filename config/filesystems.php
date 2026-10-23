<?php

/*
|--------------------------------------------------------------------------
| Penyimpanan file upload TANPA symlink (cocok untuk hosting tanpa terminal)
|--------------------------------------------------------------------------
| Disk "public" menyimpan file langsung di folder yang sudah bisa diakses web: public/uploads.
| Dengan begitu `php artisan storage:link` TIDAK diperlukan.
|
| Hosting dengan folder web terpisah dari folder aplikasi (mis. isi `public` disalin ke `public_html`)?
| Atur di .env:
|   UPLOADS_PATH=/home/NAMAUSER/public_html/uploads   (lokasi fisik folder, harus bisa ditulis)
|   UPLOADS_URL=/uploads                              (alamat di browser; ganti bila situs ada di subfolder)
*/
return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => ['driver' => 'local', 'root' => storage_path('app/private'), 'serve' => true, 'throw' => false],
        'public' => [
            'driver' => 'local',
            'root' => env('UPLOADS_PATH') ?: public_path('uploads'),
            // Relatif ("/uploads") sengaja dipilih agar tetap benar walau APP_URL di .env belum disesuaikan.
            'url' => rtrim(env('UPLOADS_URL') ?: '/uploads', '/'),
            'visibility' => 'public',
            'throw' => false,
        ],
    ],
];
