<?php
namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function complete(Lesson $lesson){
        LessonProgress::updateOrCreate(['user_id'=>Auth::id(),'lesson_id'=>$lesson->id],['completed_at'=>now(),'xp'=>1]);
        return back()->with('success','Progress tersimpan. Bagus, satu langkah lagi!');
    }
    public function quiz(Request $request, Lesson $lesson){
        $quiz=$lesson->quiz ?? []; $answers=$request->input('answers',[]); $score=0;
        foreach($quiz as $i=>$q){ if(isset($answers[$i]) && (string)$answers[$i] === (string)($q['answer'] ?? '')) $score++; }
        if($score === count($quiz) && count($quiz)>0) LessonProgress::updateOrCreate(['user_id'=>Auth::id(),'lesson_id'=>$lesson->id],['completed_at'=>now(),'xp'=>3]);
        return back()->with('quiz_result', ['score'=>$score,'total'=>count($quiz),'passed'=>$score===count($quiz) && count($quiz)>0]);
    }
}
