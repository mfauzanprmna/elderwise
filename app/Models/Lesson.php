<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Lesson extends Model
{
    protected $fillable = ['module_id','code','title','type','content','pdf_path','quiz','sort_order'];
    protected $casts = ['quiz' => 'array'];
    public function module(){ return $this->belongsTo(Module::class); }
    public function progress(){ return $this->hasMany(LessonProgress::class); }
}
