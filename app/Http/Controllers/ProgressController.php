<?php
namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Certificate;
use App\Support\LearningPath;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Browser melapor: peserta sudah menggulir materi sampai akhir.
     *
     *  - Materi tanpa latihan: langsung selesai (100%).
     *  - Materi dengan latihan: ditandai "sudah dibaca" sehingga latihannya terbuka; selesai setelah lulus latihan.
     * Materi harus sudah terbuka menurut urutan belajar. Aman dipanggil berulang (tidak menggandakan XP).
     */
    public function finish(Request $request, Lesson $lesson): JsonResponse
    {
        $user = Auth::user();
        $path = LearningPath::forUser($user);

        if (! $path->isUnlocked($lesson)) {
            return response()->json(['ok' => false, 'message' => 'Materi ini masih terkunci.'], 403);
        }

        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['checkpoint' => 0]
        );

        $justCompleted = false;
        $certificateRequestPending = false;

        if (! $progress->completed_at) {
            $progress->checkpoint = LessonProgress::READ_TO_END;
            $progress->checkpoint_at = now();

            if (! $lesson->hasQuiz()) {
                $progress->completed_at = now();
                $progress->xp = 1;
                $justCompleted = true;
            }
            $progress->save();
        }

        if ($justCompleted) {
            LearningPath::forgetUser($user);
            $path = LearningPath::forUser($user);
        }
        if ($justCompleted && $path->allModulesComplete()) {
            try {
                Certificate::requestIfEligible($user);
            } catch (LockTimeoutException $e) {
                report($e);
                $certificateRequestPending = true;
            }
        }

        $state = $this->state($lesson, $progress, $path, $justCompleted);
        $state['certificate_request_pending'] = $certificateRequestPending;
        if ($state['module_completed'] && $path->allModulesComplete()) {
            $state['module_completed']['certificate_requested'] = ! $certificateRequestPending;
        }

        return response()->json($state);
    }

    public function quiz(Request $request, Lesson $lesson): RedirectResponse
    {
        $user = Auth::user();
        $path = LearningPath::forUser($user);
        abort_unless($path->isUnlocked($lesson), 403);

        $quiz = is_array($lesson->quiz) ? $lesson->quiz : [];
        if (count($quiz) === 0) {
            return back();
        }

        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['checkpoint' => 0]
        );

        // Latihan baru boleh dikerjakan setelah materinya digulir sampai akhir.
        $readingDone = $progress->completed_at || $progress->hasReadToEnd() || ! $lesson->hasReadableContent();
        if (! $readingDone) {
            return back()->with('quiz_locked', 'Gulir materi sampai akhir terlebih dahulu, baru latihan ini bisa dikerjakan.');
        }

        $answers = $request->input('answers', []);
        $answers = is_array($answers) ? $answers : [];
        $score = 0;
        foreach ($quiz as $i => $q) {
            if (isset($answers[$i]) && (string) $answers[$i] === (string) ($q['answer'] ?? '')) {
                $score++;
            }
        }
        $passed = $score === count($quiz);

        if ($passed && ! $progress->completed_at) {
            $progress->completed_at = now();
            $progress->xp = 3;
            $progress->save();

            LearningPath::forgetUser($user);
            $path = LearningPath::forUser($user);
            $module = $path->moduleOf($lesson);
            if ($module && $path->isModuleComplete($module)) {
                $completion = $path->moduleCompletionPayload($module);
                if ($path->allModulesComplete()) {
                    try {
                        Certificate::requestIfEligible($user);
                        $completion['certificate_requested'] = true;
                    } catch (LockTimeoutException $e) {
                        report($e);
                        $completion['certificate_requested'] = false;
                        session()->flash('error', 'Modul terakhir selesai, tetapi pengajuan sertifikat sedang tertunda. Buka kembali Dashboard untuk mencoba lagi.');
                    }
                }
                session()->flash('module_completed', $completion);
            }
        }

        return back()->with('quiz_result', ['score' => $score, 'total' => count($quiz), 'passed' => $passed]);
    }

    /** Status progres materi untuk dikirim ke browser. */
    private function state(Lesson $lesson, LessonProgress $progress, LearningPath $path, bool $justCompleted): array
    {
        $completed = (bool) $progress->completed_at;
        $module = $path->moduleOf($lesson);

        $state = [
            'ok' => true,
            'reading_done' => $completed || $progress->hasReadToEnd(),
            'completed' => $completed,
            'just_completed' => $justCompleted,
            'module_completed' => null,
            'module_percent' => $module ? $path->modulePercent($module) : null,
            'overall' => [
                'done' => $path->completedCount(),
                'total' => $path->totalCount(),
                'percent' => $path->overallPercent(),
            ],
            'unlocked' => null,
        ];

        if ($justCompleted) {
            if ($module && $path->isModuleComplete($module)) {
                $state['module_completed'] = $path->moduleCompletionPayload($module);
            }
            $next = $path->next($lesson);
            if ($next) {
                $state['unlocked'] = [
                    'lesson_id' => $next->id,
                    'module_id' => $next->module_id,
                    'url' => route('learning.show', [$next->module_id, $next->id]),
                ];
            }
        }

        return $state;
    }
}
