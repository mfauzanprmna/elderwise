<?php
namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateSetting;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Cache\LockTimeoutException;

class CertificateController extends Controller
{
    /**
     * Pastikan peserta benar-benar sudah menyelesaikan semua materi
     * sebelum boleh membuat atau mengunduh sertifikat.
     */
    private function assertEligible(): void
    {
        $total = Lesson::count();
        $completed = Auth::user()->lessonProgress()->whereNotNull('completed_at')->count();

        abort_unless($total > 0 && $completed >= $total, 403, 'Selesaikan semua materi terlebih dahulu untuk membuat sertifikat.');
    }

    /**
     * Terbitkan sertifikat (sekali per peserta) lalu arahkan ke halaman unduh.
     */
    public function store()
    {
        $this->assertEligible();

        $user = Auth::user();

        try {
            $certificate = $user->certificate ?? Certificate::issueFor($user);
        } catch (LockTimeoutException $e) {
            return redirect()->route('dashboard')->with('error', 'Sertifikat belum bisa dibuat karena sistem sedang sibuk. Silakan coba lagi sebentar lagi.');
        }

        if ($certificate->isApproved()) {
            return redirect()->route('dashboard')->with('success', 'Sertifikat Anda sudah disetujui dan siap diunduh.');
        }

        return redirect()->route('dashboard')->with('success', 'Permohonan sertifikat terkirim. Menunggu persetujuan admin untuk bisa diunduh.');
    }

    /**
     * Unduh sertifikat sebagai PDF.
     */
    public function download()
    {
        $this->assertEligible();

        $user = Auth::user();
        $certificate = $user->certificate;
        abort_unless($certificate, 404, 'Sertifikat belum dibuat.');
        abort_unless($certificate->isApproved(), 403, 'Sertifikat belum disetujui admin.');

        $setting = CertificateSetting::current();

        $backgroundPath = $setting->background_path
            ? Storage::disk('public')->path($setting->background_path)
            : null;

        $values = [
            'user_name' => $user->name,
            'issued_date' => $certificate->issued_at->translatedFormat('d F Y'),
            'certificate_number' => $certificate->displayNumber($setting),
            'program_name' => null, // diisi dari field custom bila ada, atau default field
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf', [
            'setting' => $setting,
            'backgroundPath' => $backgroundPath,
            'fields' => $setting->fillFields($setting->fields ?? [], (int) $certificate->issued_at->year, (int) ($certificate->sequence ?? $certificate->id)),
            'values' => $values,
            'transcript' => CertificateSetting::transcript(),
        ]);

        // Ukuran kertas mengikuti rasio gambar background bila tersedia (landscape umumnya).
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Sertifikat-' . Str::slug($user->name) . '.pdf';

        return $pdf->download($filename);
    }
}
