<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateSetting extends Model
{
    protected $fillable = [
        'background_path',
        'background_width',
        'background_height',
        'fields',
        'title',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

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
