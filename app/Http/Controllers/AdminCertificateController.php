<?php
namespace App\Http\Controllers;

use App\Models\CertificateSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminCertificateController extends Controller
{
    /**
     * Data contoh dipakai di kanvas builder maupun tombol pratinjau PDF,
     * supaya admin melihat perkiraan tampilan nyata (bukan cuma nama field).
     */
    public static function sampleValues(): array
    {
        return [
            'user_name' => 'Dr. Ahmad Wijaya Kusuma, M.Kom.',
            'issued_date' => now()->translatedFormat('d F Y'),
            'certificate_number' => 'EW/' . now()->format('Y') . '/0001',
            'program_name' => 'ElderWiSE Digital Confidence Journey',
        ];
    }

    public function edit()
    {
        $setting = CertificateSetting::current();

        return view('admin.certificate.edit', [
            'setting' => $setting,
            'fields' => $setting->fields ?? CertificateSetting::defaultFields(),
            'sample' => self::sampleValues(),
        ]);
    }

    /**
     * Pratinjau PDF dengan data contoh — pakai renderer & template PERSIS SAMA
     * dengan sertifikat asli (certificates.pdf), supaya hasilnya 100% akurat,
     * bukan tiruan visual di kanvas builder.
     */
    public function preview()
    {
        $setting = CertificateSetting::current();

        $backgroundPath = $setting->background_path
            ? storage_path('app/public/' . $setting->background_path)
            : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf', [
            'setting' => $setting,
            'backgroundPath' => $backgroundPath,
            'fields' => $setting->fields ?? [],
            'values' => self::sampleValues(),
        ]);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Pratinjau-Sertifikat.pdf');
    }

    /**
     * Upload / ganti gambar latar sertifikat.
     */
    public function uploadBackground(Request $request)
    {
        $request->validate([
            'background' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:8192'],
        ]);

        $setting = CertificateSetting::current();

        if ($setting->background_path) {
            Storage::disk('public')->delete($setting->background_path);
        }

        $path = $request->file('background')->store('certificates', 'public');
        [$width, $height] = getimagesize(storage_path('app/public/' . $path));

        $setting->update([
            'background_path' => $path,
            'background_width' => $width,
            'background_height' => $height,
        ]);

        return redirect()->route('admin.certificate.edit')->with('success', 'Gambar latar sertifikat berhasil diperbarui.');
    }

    /**
     * Simpan seluruh konfigurasi field (posisi hasil drag, gaya teks, dan field custom)
     * dikirim sekaligus sebagai JSON dari builder di halaman admin.
     */
    public function saveFields(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.key' => ['required', 'string', 'max:60'],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.kind' => ['required', 'in:dynamic,custom'],
            'fields.*.source' => ['nullable', 'string', 'max:60'],
            'fields.*.value' => ['nullable', 'string', 'max:500'],
            'fields.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'fields.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
            'fields.*.font_size' => ['required', 'integer', 'min:8', 'max:120'],
            'fields.*.color' => ['required', 'string', 'max:20'],
            'fields.*.align' => ['required', 'in:left,center,right'],
            'fields.*.bold' => ['nullable', 'boolean'],
            'fields.*.visible' => ['nullable', 'boolean'],
        ]);

        $setting = CertificateSetting::current();
        $setting->update([
            'title' => $data['title'],
            'fields' => $data['fields'],
        ]);

        return redirect()->route('admin.certificate.edit')->with('success', 'Pengaturan sertifikat berhasil disimpan.');
    }

    /**
     * Tambah satu field custom baru (dipanggil via form kecil, lalu redirect balik
     * ke builder supaya admin langsung bisa menggeser posisinya).
     */
    public function addCustomField(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'value' => ['nullable', 'string', 'max:500'],
        ]);

        $setting = CertificateSetting::current();
        $fields = $setting->fields ?? [];

        $fields[] = [
            'key' => 'custom_' . Str::random(6),
            'label' => $data['label'],
            'kind' => 'custom',
            'source' => null,
            'value' => $data['value'] ?? '',
            'x' => 50, 'y' => 50,
            'font_size' => 16,
            'color' => '#41546c',
            'align' => 'center',
            'bold' => false,
            'visible' => true,
        ];

        $setting->update(['fields' => $fields]);

        return redirect()->route('admin.certificate.edit')->with('success', 'Field baru ditambahkan. Geser ke posisi yang diinginkan lalu simpan.');
    }

    public function removeField(Request $request, string $key)
    {
        $setting = CertificateSetting::current();
        $fields = collect($setting->fields ?? [])->reject(fn($f) => $f['key'] === $key)->values()->all();
        $setting->update(['fields' => $fields]);

        return redirect()->route('admin.certificate.edit')->with('success', 'Field dihapus.');
    }
}
