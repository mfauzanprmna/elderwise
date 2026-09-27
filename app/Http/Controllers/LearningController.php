<?php
namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    public function landing(){ return view('home'); }

    public function dashboard(){
        $modules=Module::with('lessons')->orderBy('sort_order')->get();
        $completed=Auth::user()->lessonProgress()->whereNotNull('completed_at')->pluck('lesson_id')->all();
        $total=$modules->sum(fn($m)=>$m->lessons->count()); $done=count($completed);
        $allCompleted = $total > 0 && $done === $total;
        $certificate = Auth::user()->certificate;
        return view('learning.dashboard', compact('modules','completed','total','done','allCompleted','certificate'));
    }

    public function show(Module $module, Lesson $lesson=null){
        $modules=Module::with('lessons')->orderBy('sort_order')->get();
        $module->load('lessons');
        if($lesson && $lesson->module_id !== $module->id) abort(404);
        $lesson ??= $module->lessons->first();
        $completed=Auth::user()->lessonProgress()->whereNotNull('completed_at')->pluck('lesson_id')->all();
        return view('learning.show', compact('module','lesson','completed','modules'));
    }
}
