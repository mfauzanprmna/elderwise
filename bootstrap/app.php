<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [HandleCors::class]);
        $middleware->alias(['admin' => AdminMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Dua kegagalan form yang paling sering membingungkan pengguna: halaman kedaluwarsa (419) dan
        // file terlalu besar untuk batas upload server (413). Kembalikan ke formulir dengan pesan yang jelas.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            $messages = [
                419 => 'Halaman ini sudah terlalu lama dibuka sehingga sesinya berakhir. Silakan kirim ulang formulirnya.',
                413 => 'File yang Anda unggah terlalu besar untuk batas server. Pilih file yang lebih kecil, atau minta penyelenggara menaikkan batas upload.',
            ];

            if (isset($messages[$e->getStatusCode()]) && ! $request->expectsJson()) {
                return redirect()->back()
                    ->withInput($request->except('_token', 'password', 'password_confirmation', 'current_password'))
                    ->with('error', $messages[$e->getStatusCode()]);
            }
        });
    })->create();
