<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Module extends Model
{
    protected $fillable = ['number','title','slogan','description','badge','sort_order'];
    public function lessons(){ return $this->hasMany(Lesson::class)->orderBy('sort_order'); }
}
