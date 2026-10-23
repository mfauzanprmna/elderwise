<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LessonProgress extends Model
{
    protected $fillable = ['user_id','lesson_id','completed_at','xp','checkpoint','checkpoint_at'];
    protected $casts = ['completed_at' => 'datetime', 'checkpoint_at' => 'datetime', 'checkpoint' => 'integer'];
    /**
     * Nilai kolom "checkpoint" bila peserta sudah menggulir materi sampai akhir.
     * (Kolom ini dulu menyimpan nomor bagian; kini hanya penanda "sudah dibaca sampai akhir".
     *  Angka lama yang kecil, 1 sampai 8, sengaja tidak dianggap selesai dibaca.)
     */
    public const READ_TO_END = 255;

    public function hasReadToEnd(): bool
    {
        return (int) $this->checkpoint >= self::READ_TO_END;
    }

    public function lesson(){ return $this->belongsTo(Lesson::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
