<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['module_id','code','title','type','content','pdf_path','quiz','sort_order'];
    protected $casts = ['quiz' => 'array'];

    public function module(){ return $this->belongsTo(Module::class); }
    public function progress(){ return $this->hasMany(LessonProgress::class); }

    public function isPdf(): bool
    {
        return $this->type === 'pdf' && filled($this->pdf_path);
    }

    public function hasQuiz(): bool
    {
        return is_array($this->quiz) && count($this->quiz) > 0;
    }

    /**
     * Isi materi siap tampil. Materi hasil editor (HTML) dipakai apa adanya; materi lama yang
     * tersimpan sebagai teks polos (baris baru biasa atau tulisan \n literal) diubah jadi paragraf.
     */
    public function renderedContent(): string
    {
        $content = (string) $this->content;
        if ($content === '' || preg_match('/<[a-z][\s\S]*>/i', $content)) {
            return $content;
        }

        $paragraphs = preg_split('/(?:\\\\n|\r?\n){2,}/', $content);

        return collect($paragraphs)
            ->map(fn ($p) => trim($p))
            ->filter()
            ->map(fn ($p) => '<p>' . preg_replace('/(?:\\\\n|\r?\n)/', '<br>', e($p)) . '</p>')
            ->implode('');
    }

    /**
     * Apakah ada sesuatu yang perlu dibaca (teks, gambar/tabel, atau PDF)?
     * Materi yang kosong tidak memiliki "akhir" untuk dicapai, jadi tidak dihitung sebagai bacaan.
     */
    public function hasReadableContent(): bool
    {
        if ($this->isPdf()) {
            return true;
        }

        $html = $this->renderedContent();

        return trim(html_entity_decode(strip_tags($html))) !== ''
            || (bool) preg_match('/<(img|table|iframe|video|figure)\b/i', $html);
    }
}
