@extends('layouts.app')
@section('content')
<div class="learning-screen">
    <aside class="learning-nav">
        <div class="learning-nav-head">
            <div class="eyebrow">ELDERWISE</div><h2>Learning Map</h2>
            <div class="tiny-progress"><span>{{ collect($completed)->count() }} selesai</span><i style="width:{{ max(5,min(100, count($completed)*4)) }}%"></i></div>
        </div>
        <div class="module-switcher">
            @foreach($modules as $m)
                @php $moduleLessonIds = $m->lessons->pluck('id')->all(); $done = count(array_intersect($moduleLessonIds, $completed)); $pct = count($moduleLessonIds) ? round($done / count($moduleLessonIds) * 100) : 0; @endphp
                <div class="module-group {{ $module->id === $m->id ? 'open' : '' }}">
                    <a class="module-group-title" href="{{ route('learning.show', $m) }}">
                        <span class="circle-progress small" data-value="{{ $pct }}"></span>
                        <b>Modul {{ $m->number }}</b><span class="module-group-name">{{ $m->title }}</span>
                    </a>
                    @if($module->id === $m->id)
                        <div class="lesson-list">
                            @foreach($m->lessons as $l)
                                <a href="{{ route('learning.show',[$m,$l]) }}" class="lesson-item {{ $lesson?->id===$l->id?'current':'' }} {{ in_array($l->id,$completed)?'done':'' }}">
                                    <span class="status-dot">{{ in_array($l->id,$completed)?'✓':$l->code }}</span><span>{{ $l->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </aside>

    <section class="lesson-content">
        <div class="lesson-toolbar"><a href="{{ route('dashboard') }}" class="back">← Dashboard</a><span>Modul {{ $module->number }} · {{ $module->title }}</span></div>
        @if($lesson)
            <div class="lesson-header"><div class="eyebrow">{{ $lesson->code }}</div><h1>{{ $lesson->title }}</h1><p>{{ $module->slogan }}</p></div>
            <div class="content-card">
                @if($lesson->type==='pdf' && $lesson->pdf_path)
                    <iframe class="pdf-frame" src="{{ Storage::url($lesson->pdf_path) }}"></iframe>
                @else
                    <div class="lesson-text">{!! $lesson->content !!}</div>
                @endif

                @if($lesson->quiz)
                    <div class="quiz-box"><div class="eyebrow">CHECKPOINT</div><h2>Sudah bisa?</h2>
                        <form method="POST" action="{{ route('learning.quiz',$lesson) }}">@csrf
                            @foreach($lesson->quiz as $i=>$q)
                                <div class="question"><h3>{{ $i+1 }}. {{ $q['question'] }}</h3>
                                    @foreach($q['options'] as $oi=>$opt)<label class="option"><input type="radio" name="answers[{{ $i }}]" value="{{ $oi }}" required><span>{{ $opt }}</span></label>@endforeach
                                </div>
                            @endforeach
                            <button class="btn btn-primary">Periksa Jawaban</button>
                        </form>
                        @if(session('quiz_result'))<div class="quiz-result {{ session('quiz_result.passed')?'passed':'retry' }}">Skor Anda: <b>{{ session('quiz_result.score') }}/{{ session('quiz_result.total') }}</b>. {{ session('quiz_result.passed')?'Materi ini tercatat selesai!':'Coba lagi pelan-pelan.' }}</div>@endif
                    </div>
                @endif
            </div>
            <div class="lesson-actions">@if(in_array($lesson->id,$completed))<div class="completed-pill">✓ Lesson selesai</div>@else<form method="POST" action="{{ route('learning.complete',$lesson) }}">@csrf<button class="btn btn-primary">Tandai selesai →</button></form>@endif</div>
        @else
            <div class="empty-state"><h2>Pilih materi untuk mulai.</h2></div>
        @endif
    </section>
</div>
@endsection
