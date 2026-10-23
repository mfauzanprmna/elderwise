<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Atur ulang kata sandi {{ $namaSitus }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f7fb;font-family:'Segoe UI',Arial,Helvetica,sans-serif;color:#16233a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:2px solid #c9d6e6;border-radius:16px;">
                    <tr>
                        <td style="padding:28px 36px 0 36px;font-size:22px;font-weight:800;color:#0f2f52;">{{ $namaSitus }}</td>
                    </tr>
                    <tr>
                        <td style="padding:20px 36px 0 36px;">
                            <h1 style="margin:0 0 14px 0;font-size:26px;line-height:1.3;color:#0f2f52;">Atur ulang kata sandi</h1>
                            <p style="margin:0 0 14px 0;font-size:18px;line-height:1.7;">Halo, kami menerima permintaan untuk membuat kata sandi baru bagi akun Anda.</p>
                            <p style="margin:0 0 26px 0;font-size:18px;line-height:1.7;">Silakan tekan tombol di bawah ini:</p>
                            <p style="margin:0 0 26px 0;">
                                <a href="{{ $url }}" style="display:inline-block;background:#0b57c4;color:#ffffff;text-decoration:none;font-size:19px;font-weight:700;padding:16px 28px;border-radius:12px;">Buat Kata Sandi Baru</a>
                            </p>
                            <p style="margin:0 0 14px 0;font-size:16px;line-height:1.7;color:#41546c;">Tautan ini berlaku selama <strong>{{ $menit }} menit</strong> dan hanya dapat dipakai satu kali.</p>
                            <p style="margin:0 0 14px 0;font-size:16px;line-height:1.7;color:#41546c;">Jika Anda tidak merasa meminta ini, abaikan saja email ini. Kata sandi Anda tidak akan berubah.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 36px 32px 36px;">
                            <p style="margin:0;padding-top:18px;border-top:2px solid #e3eaf3;font-size:14px;line-height:1.7;color:#41546c;">
                                Jika tombol di atas tidak berfungsi, salin dan tempel alamat berikut ke peramban (browser) Anda:<br>
                                <a href="{{ $url }}" style="color:#083f8f;word-break:break-all;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
