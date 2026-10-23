<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\Lesson;

class Certificate extends Model
{
    /**
     * number   = kode internal yang unik. Setelah admin menyetujui, akan berubah jadi nomor sertifikat yang ditampilkan.
     * sequence = nomor urut yang tampil di sertifikat, hanya diisi saat admin menyetujui.
     * approved_at = timestamp persetujuan admin. Sertifikat baru bisa didownload setelah status ini ada.
     */
    protected $fillable = ['user_id', 'number', 'sequence', 'issued_at', 'approved_at'];
    protected $casts = ['issued_at' => 'datetime', 'approved_at' => 'datetime', 'sequence' => 'integer'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool
    {
        return ! empty($this->approved_at) && $this->sequence !== null;
    }

    /** Nomor sertifikat sesuai format di Pengaturan Sertifikat: {TAHUN} = tahun lulus, {URUT} = nomor urut kelulusan. */
    public function displayNumber(?CertificateSetting $setting = null): string
    {
        if (! $this->isApproved()) {
            return 'Menunggu persetujuan';
        }

        return ($setting ?? CertificateSetting::current())->numberFor($this);
    }

    /** Nomor urut yang akan didapat peserta berikutnya. */
    public static function nextSequence(CertificateSetting $setting, ?int $year = null): int
    {
        $query = static::query()->whereNotNull('approved_at');

        if ($setting->number_reset_yearly) {
            $query->whereYear('approved_at', $year ?? now()->year);
        }

        $max = (int) $query->max('sequence');

        return min($max + 1, 999);
    }

    /**
     * Terbitkan sertifikat untuk peserta (sekali per peserta) dan menunggu persetujuan admin.
     * Dikunci agar dua peserta yang menekan tombol bersamaan tidak mendapat entri ganda.
     *
     * @throws \Illuminate\Contracts\Cache\LockTimeoutException bila server sedang sangat sibuk
     */
    public static function issueFor(User $user): self
    {
        return Cache::lock('ew-certificate-sequence', 10)->block(5, function () use ($user) {
            if ($existing = static::where('user_id', $user->id)->first()) {
                return $existing;
            }

            $issuedAt = now();

            return static::create([
                'user_id' => $user->id,
                'number' => sprintf('PENDING/%d/%d', $issuedAt->year, $user->id),
                'issued_at' => $issuedAt,
                'approved_at' => null,
                'sequence' => null,
            ]);
        });
    }

    /**
     * Ajukan sertifikat otomatis setelah seluruh materi selesai.
     *
     * @throws \Illuminate\Contracts\Cache\LockTimeoutException bila server sedang sangat sibuk
     */
    public static function requestIfEligible(User $user): ?self
    {
        $total = Lesson::count();
        $completed = $user->lessonProgress()->whereNotNull('completed_at')->count();

        if ($total === 0 || $completed < $total) {
            return null;
        }

        return $user->certificate ?? static::issueFor($user);
    }

    public static function approveForUser(User $user): self
    {
        $certificate = static::where('user_id', $user->id)->firstOrFail();

        if ($certificate->isApproved()) {
            return $certificate;
        }

        $setting = CertificateSetting::current();
        $sequence = static::nextSequence($setting, now()->year);

        if ($sequence >= 999 && static::query()->whereNotNull('approved_at')->where('sequence', 999)->exists()) {
            throw new \RuntimeException('Batas nomor sertifikat sudah mencapai 999.');
        }

        $certificate->approved_at = now();
        $certificate->sequence = $sequence;
        $certificate->number = sprintf('EW/%d/%03d', $certificate->approved_at->year, $sequence);
        $certificate->save();

        return $certificate;
    }
}
