<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CertificateSetting extends Model
{
    protected $fillable = [
        'background_path',
        'background_width',
        'background_height',
        'fields',
        'title',
        'number_template',
        'number_reset_yearly',
    ];

    protected $casts = [
        'fields' => 'array',
        'number_reset_yearly' => 'boolean',
    ];

    /** Format nomor bawaan, sama dengan penomoran sebelum ada pengaturan ini. */
    public const DEFAULT_NUMBER_TEMPLATE = 'EW/{TAHUN}/{URUT}';

    /** Kode otomatis yang dikenali pada format nomor dan pada isi tulisan sertifikat. */
    public const NUMBER_TOKENS = ['TAHUN', 'URUT'];

    /**
     * Isi kode otomatis: {TAHUN} -> tahun lulus (tahun sertifikat terbit), {URUT} -> nomor urut 3 angka (001, 002, ... 999).
     * Huruf besar/kecil dan spasi di dalam kurung diabaikan, mis. {tahun} atau { URUT }.
     */
    public static function fillTemplate(string $template, int $year, int $sequence): string
    {
        return preg_replace_callback('/\{\s*(TAHUN|URUT)\s*\}/i', function (array $m) use ($year, $sequence) {
            $sequence = max(1, min((int) $sequence, 999));
            return strtoupper($m[1]) === 'TAHUN' ? (string) $year : str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
        }, $template) ?? $template;
    }

    /** Nomor yang tampil di sertifikat milik peserta. */
    public function numberFor(Certificate $certificate): string
    {
        return self::fillTemplate(
            $this->number_template ?: self::DEFAULT_NUMBER_TEMPLATE,
            (int) $certificate->issued_at->year,
            (int) ($certificate->sequence ?? $certificate->id)
        );
    }

    /** Terapkan kode otomatis pada isi semua tulisan di sertifikat (mis. tulisan tambahan "No. {URUT}/{TAHUN}"). */
    public function fillFields(array $fields, int $year, int $sequence): array
    {
        return array_map(function ($field) use ($year, $sequence) {
            if (isset($field['value']) && is_string($field['value'])) {
                $field['value'] = self::fillTemplate($field['value'], $year, $sequence);
            }

            return $field;
        }, $fields);
    }

    /** Pesan kesalahan bila format nomor tidak valid, atau null bila valid. */
    public static function templateError(string $template): ?string
    {
        if (substr_count($template, '{') !== substr_count($template, '}')) {
            return 'Tanda kurung kurawal { } belum berpasangan. Tulis kode otomatis persis seperti {TAHUN} dan {URUT}.';
        }

        if (preg_match_all('/\{([^{}]*)\}/', $template, $found)) {
            foreach ($found[1] as $token) {
                if (! in_array(strtoupper(trim($token)), self::NUMBER_TOKENS, true)) {
                    return sprintf('Kode {%s} tidak dikenali. Kode yang tersedia: {TAHUN} dan {URUT}.', $token);
                }
            }
        }

        $rest = preg_replace('/\{\s*(TAHUN|URUT)\s*\}/i', '', $template);
        if (str_contains($rest, '{') || str_contains($rest, '}')) {
            return 'Kode otomatis harus ditulis persis seperti {TAHUN} dan {URUT}, tanpa kurung tambahan.';
        }

        if (! preg_match('/\{\s*URUT\s*\}/i', $template)) {
            return 'Format nomor harus memuat {URUT} agar setiap sertifikat mendapat nomor yang berbeda.';
        }

        return null;
    }

    /**
     * Data halaman 2 sertifikat (rincian materi seperti rapor): daftar modul beserta
     * materinya, JP per modul, total JP, dan ukuran huruf yang disesuaikan jumlah baris
     * supaya tabel selalu muat dalam satu halaman.
     */
    public static function transcript(): array
    {
        $modules = Module::with('lessons:id,module_id,title')->orderBy('sort_order')->orderBy('number')->get();

        $rows = $modules->map(fn ($m) => [
            'number' => $m->number,
            'title' => $m->title,
            'slogan' => $m->slogan,
            'topics' => $m->lessons->pluck('title')->all(),
            'description' => $m->description,
            'jp' => Module::TRAINING_HOURS,
        ])->all();

        $totalRows = count($rows);
        [$fontPx, $mode] = self::transcriptLayout($rows);

        // Mode padat (modul sangat banyak): judul dipotong 1 baris, tanpa slogan; jika masih
        // tidak muat, sisa modul diringkas jadi satu baris "dan N modul lainnya".
        $hidden = 0;
        if ($mode === 'dense') {
            $capacity = (int) floor(self::TABLE_BUDGET / ($fontPx * 1.75 + 10));
            if ($totalRows > $capacity) {
                $hidden = $totalRows - ($capacity - 1);
                $rows = array_slice($rows, 0, $capacity - 1);
            }
            $rows = array_map(fn ($r) => array_merge($r, ['title' => Str::limit($r['title'], 34), 'slogan' => '']), $rows);
        }

        return [
            'rows' => $rows,
            'total_jp' => $totalRows * Module::TRAINING_HOURS,
            'font_px' => $fontPx,
            'compact' => $mode !== 'full',
            'dense' => $mode === 'dense',
            'hidden_count' => $hidden,
        ];
    }

    /** Tinggi area baris tabel (px) di panel halaman 2 (A4 landscape). */
    private const TABLE_BUDGET = 600;

    /**
     * Pilih mode & ukuran huruf terbesar (13..9px) yang membuat seluruh baris muat di panel.
     * Mode: full (daftar materi lengkap) -> compact (hanya jumlah materi) -> dense (judul 1 baris).
     * Angka kalibrasi diukur dari hasil render DomPDF (tinggi baris ≈ 1,75 × ukuran huruf).
     */
    private static function transcriptLayout(array $rows): array
    {
        foreach (['full', 'compact'] as $mode) {
            for ($f = 13; $f >= 9; $f--) {
                $lineH = $f * 1.75;
                $total = 0;
                foreach ($rows as $r) {
                    $topicText = $mode === 'compact'
                        ? count($r['topics']) . ' materi'
                        : ($r['topics'] ? implode(' • ', $r['topics']) : (string) $r['description']);
                    $topicLines = (int) ceil(mb_strlen($topicText) / (540 / ($f * 0.52)));
                    $titleLines = (int) ceil(mb_strlen($r['title']) / (226 / ($f * 0.62)));
                    $sloganLines = (int) ceil(mb_strlen((string) $r['slogan']) / (226 / (($f - 2) * 0.52)));
                    $moduleH = $titleLines * $lineH + $sloganLines * ($f - 2) * 1.75;
                    $total += max($topicLines * $lineH, $moduleH) + 10;
                }
                if ($total <= self::TABLE_BUDGET) {
                    return [$f, $mode];
                }
            }
        }
        return [9, 'dense'];
    }

    /**
     * Ambil satu-satunya baris pengaturan sertifikat, atau buat default kosong.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'title' => 'Sertifikat Kelulusan',
            'fields' => self::defaultFields(),
        ]);
    }

    /**
     * Field bawaan yang selalu tersedia (tidak bisa dihapus lewat builder,
     * tapi posisinya bisa dipindah / disembunyikan).
     */
    public static function defaultFields(): array
    {
        return [
            [
                'key' => 'nama',
                'label' => 'Nama Peserta',
                'kind' => 'dynamic',
                'source' => 'user_name',
                'value' => null,
                'x' => 50, 'y' => 45,
                'font_size' => 36,
                'color' => '#15304d',
                'align' => 'center',
                'bold' => true,
                'visible' => true,
            ],
            [
                'key' => 'tanggal',
                'label' => 'Tanggal Lulus',
                'kind' => 'dynamic',
                'source' => 'issued_date',
                'value' => null,
                'x' => 50, 'y' => 60,
                'font_size' => 16,
                'color' => '#41546c',
                'align' => 'center',
                'bold' => false,
                'visible' => true,
            ],
            [
                'key' => 'nomor',
                'label' => 'Nomor Sertifikat',
                'kind' => 'dynamic',
                'source' => 'certificate_number',
                'value' => null,
                'x' => 50, 'y' => 88,
                'font_size' => 12,
                'color' => '#41546c',
                'align' => 'center',
                'bold' => false,
                'visible' => true,
            ],
            [
                'key' => 'program',
                'label' => 'Nama Program/Modul',
                'kind' => 'dynamic',
                'source' => 'program_name',
                'value' => 'ElderWiSE Digital Confidence Journey',
                'x' => 50, 'y' => 68,
                'font_size' => 16,
                'color' => '#41546c',
                'align' => 'center',
                'bold' => false,
                'visible' => true,
            ],
        ];
    }
}
