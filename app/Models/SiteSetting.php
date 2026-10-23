<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

/**
 * Pengaturan situs (satu baris): nama web, tagline, logo, favicon, teks footer.
 */
class SiteSetting extends Model
{
    protected $fillable = ['site_name', 'tagline', 'description', 'footer_text', 'logo_path', 'favicon_path', 'show_name_with_logo'];

    protected $casts = ['show_name_with_logo' => 'boolean'];

    // Nilai awal diambil dari poster acara ElderWiSE.
    public const DEFAULTS = [
        'site_name' => 'ElderWiSE',
        'tagline' => 'Belajar Bersama, Tumbuh Bersama',
        'description' => 'Tingkatkan literasi digital dan AI dengan cara yang praktis, bertahap, dan menyenangkan untuk mendukung pembelajaran yang lebih bermakna.',
        'footer_text' => null,
        'show_name_with_logo' => true,
    ];

    private static ?self $cached = null;
    private static ?Collection $partners = null;

    /**
     * Ambil pengaturan (dibuat otomatis dengan nilai bawaan jika belum ada). Hasil disimpan
     * selama satu request supaya tidak query berulang untuk tiap view. Jika tabel belum
     * dimigrasi, tampilan memakai nilai bawaan alih-alih error.
     */
    public static function current(): self
    {
        if (self::$cached) {
            return self::$cached;
        }

        try {
            return self::$cached = static::firstOrCreate(['id' => 1], self::DEFAULTS);
        } catch (\Throwable) {
            return self::$cached = new static(self::DEFAULTS);
        }
    }

    /** Daftar penyelenggara/mitra (urut sesuai sort_order). Kosong jika tabel belum dimigrasi. */
    public static function partners(): Collection
    {
        if (self::$partners) {
            return self::$partners;
        }

        try {
            return self::$partners = SitePartner::orderBy('sort_order')->orderBy('id')->get();
        } catch (\Throwable) {
            return self::$partners = collect();
        }
    }

    public static function forgetCache(): void
    {
        self::$cached = null;
        self::$partners = null;
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon_path ? Storage::disk('public')->url($this->favicon_path) : null;
    }

    /** Huruf pertama nama situs, dipakai sebagai lencana jika belum ada logo. */
    public function initial(): string
    {
        $name = trim((string) $this->site_name);
        return mb_strtoupper(mb_substr($name !== '' ? $name : 'E', 0, 1));
    }
}
