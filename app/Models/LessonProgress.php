<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LessonProgress extends Model
{
    protected $fillable = ['user_id','lesson_id','completed_at','xp'];
    protected $casts = ['completed_at' => 'datetime'];
    public function lesson(){ return $this->belongsTo(Lesson::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
