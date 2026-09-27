<?php
namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateSetting;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Pastikan peserta benar-benar sudah menyelesaikan semua materi
     * sebelum boleh membuat atau mengunduh sertifikat.
     */
    private function assertEligible(): void
    {
        $modules = Module::with('lessons')->get();
        $total = $modules->sum(fn($m) => $m->lessons->count());
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

        $certificate = $user->certificate ?? Certificate::create([
            'user_id' => $user->id,
            'number' => $this->generateNumber(),
            'issued_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Sertifikat berhasil dibuat! Silakan unduh di bawah.');
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

        $setting = CertificateSetting::current();

        $backgroundPath = $setting->background_path
            ? storage_path('app/public/' . $setting->background_path)
            : null;

        $values = [
            'user_name' => $user->name,
            'issued_date' => $certificate->issued_at->translatedFormat('d F Y'),
            'certificate_number' => $certificate->number,
            'program_name' => null, // diisi dari field custom bila ada, atau default field
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf', [
            'setting' => $setting,
            'backgroundPath' => $backgroundPath,
            'fields' => $setting->fields ?? [],
            'values' => $values,
        ]);

        // Ukuran kertas mengikuti rasio gambar background bila tersedia (landscape umumnya).
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Sertifikat-' . Str::slug($user->name) . '.pdf';

        return $pdf->download($filename);
    }

    private function generateNumber(): string
    {
        $year = now()->format('Y');
        $seq = Certificate::whereYear('created_at', now()->year)->count() + 1;
        return sprintf('EW/%s/%04d', $year, $seq);
    }
}
