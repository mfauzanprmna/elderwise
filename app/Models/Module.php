<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Module extends Model
{
    protected $fillable = [
        'number', 'title', 'slogan', 'description',
        'badge', 'badge_icon', 'badge_image', 'badge_color',
        'sort_order',
    ];

    /** Pilihan warna medali lencana (dipakai form admin & tampilan peserta). */
    public const BADGE_COLORS = [
        'gold'   => ['label' => 'Emas',   'from' => '#fff3d6', 'to' => '#ffd97a', 'border' => '#f0b429'],
        'blue'   => ['label' => 'Biru',   'from' => '#e7f0ff', 'to' => '#9cc2f5', 'border' => '#0b57c4'],
        'green'  => ['label' => 'Hijau',  'from' => '#e3f6ea', 'to' => '#8fd6ab', 'border' => '#146c43'],
        'purple' => ['label' => 'Ungu',   'from' => '#efe6ff', 'to' => '#c3a6f2', 'border' => '#6b3fc4'],
        'rose'   => ['label' => 'Merah muda', 'from' => '#ffe6ec', 'to' => '#f5a3b8', 'border' => '#c43f67'],
        'teal'   => ['label' => 'Tosca',  'from' => '#dcf5f2', 'to' => '#85d4cb', 'border' => '#0f766e'],
    ];

    /** Ikon bawaan untuk modul yang belum diatur lencananya. */
    private const DEFAULT_ICONS = [1 => '🌱', 2 => '🤝', 3 => '🔍', 4 => '✍️', 5 => '🤖', 6 => '🏆'];

    /** Jam Pelatihan (JP) per modul — 1 modul = 1 JP. */
    public const TRAINING_HOURS = 1;

    public function lessons(){ return $this->hasMany(Lesson::class)->orderBy('sort_order'); }

    public function badgeIcon(): string
    {
        return $this->badge_icon ?: (self::DEFAULT_ICONS[$this->number] ?? '🎯');
    }

    public function badgeImageUrl(): ?string
    {
        return $this->badge_image ? Storage::disk('public')->url($this->badge_image) : null;
    }

    public function badgeColor(): array
    {
        return self::BADGE_COLORS[$this->badge_color] ?? self::BADGE_COLORS['gold'];
    }
}
