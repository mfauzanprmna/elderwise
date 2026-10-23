<?php

return [

    /*
    | Login dengan Google (OAuth 2.0 via Laravel Socialite).
    | Buat kredensial di https://console.cloud.google.com/apis/credentials
    | lalu isi GOOGLE_CLIENT_ID & GOOGLE_CLIENT_SECRET di file .env.
    */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

];
