<?php
namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Certificate;
use App\Support\LearningPath;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    public function landing(){ return view('home'); }

    public function dashboard(){
        $user = Auth::user();
        $path = LearningPath::forUser($user);
        $modules = $path->modules;
        $total = $path->totalCount(); $done = $path->completedCount();
        $allCompleted = $total > 0 && $done === $total;
        $certificateRequestPending = false;
        if ($allCompleted) {
            try {
                Certificate::requestIfEligible($user);
            } catch (LockTimeoutException $e) {
                report($e);
                $certificateRequestPending = true;
            }
        }
        $certificate = $user->certificate()->first();

        $totalXp = (int) $user->lessonProgress()->whereNotNull('completed_at')->sum('xp');
        $modulesCompletedCount = $modules->filter(fn ($m) => $path->isModuleComplete($m))->count();
        $rank = $this->rankForXp($totalXp);

        return view('learning.dashboard', compact(
            'modules','total','done','allCompleted','certificate',
            'totalXp','modulesCompletedCount','rank','path','certificateRequestPending'
        ));
    }

    /**
     * Sistem rank sederhana berbasis total XP yang terkumpul.
     * Setiap lesson biasa = 1 XP, lesson dengan quiz lulus = 3 XP.
     */
    private function rankForXp(int $xp): array
    {
        $ranks = [
            ['name' => 'Pemula Digital',     'min' => 0,   'icon' => '🌱'],
            ['name' => 'Penjelajah Digital',  'min' => 15,  'icon' => '🧭'],
            ['name' => 'Digital Explorer',    'min' => 35,  'icon' => '⭐'],
            ['name' => 'Digital Achiever',    'min' => 60,  'icon' => '🏅'],
            ['name' => 'ElderWiSE Master',    'min' => 90,  'icon' => '👑'],
        ];

        $current = $ranks[0];
        $next = null;
        foreach ($ranks as $i => $r) {
            if ($xp >= $r['min']) {
                $current = $r;
                $next = $ranks[$i + 1] ?? null;
            }
        }

        $progressPct = 100;
        if ($next) {
            $span = $next['min'] - $current['min'];
            $progressPct = $span > 0 ? min(100, round((($xp - $current['min']) / $span) * 100)) : 100;
        }

        return [
            'name' => $current['name'],
            'icon' => $current['icon'],
            'next_name' => $next['name'] ?? null,
            'next_min' => $next['min'] ?? null,
            'progress_pct' => $progressPct,
        ];
    }

    public function show(Module $module, ?Lesson $lesson = null){
        $user = Auth::user();
        $path = LearningPath::forUser($user);

        if (! $lesson) {
            // Masuk lewat modul: arahkan ke materi yang belum selesai pertama (atau yang pertama).
            if (! $path->isModuleUnlocked($module)) {
                return $this->redirectToFrontier($path, 'Modul ini masih terkunci. Selesaikan modul sebelumnya terlebih dahulu, ya.');
            }
            $resume = $path->resumeLessonFor($module);
            if ($resume) {
                return redirect()->route('learning.show', [$module->id, $resume->id]);
            }
        } else {
            abort_if($lesson->module_id !== $module->id, 404);
            if (! $path->isUnlocked($lesson)) {
                return $this->redirectToFrontier($path, 'Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu, ya.');
            }
        }

        $progress = null; $completedThis = false; $readingDone = false; $needsReading = false;
        $moduleCompleted = null; $autoCompleted = false;

        if ($lesson) {
            $progress = LessonProgress::firstOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['checkpoint' => 0]
            );
            $completedThis = (bool) $progress->completed_at;
            $readable = $lesson->hasReadableContent();

            // Materi kosong (tanpa teks, PDF, maupun latihan): tidak ada yang perlu dikerjakan, selesai otomatis
            // agar peserta tidak tertahan di urutan belajar.
            if (! $completedThis && ! $readable && ! $lesson->hasQuiz()) {
                $progress->completed_at = now();
                $progress->xp = 1;
                $progress->save();
                $completedThis = true;
                $autoCompleted = true;
            }

            if ($autoCompleted) {
                LearningPath::forgetUser($user);
                $path = LearningPath::forUser($user); // segarkan status
                $m = $path->moduleOf($lesson);
                if ($m && $path->isModuleComplete($m)) {
                    $moduleCompleted = $path->moduleCompletionPayload($m);
                    if ($path->allModulesComplete()) {
                        try {
                            Certificate::requestIfEligible($user);
                            $moduleCompleted['certificate_requested'] = true;
                        } catch (LockTimeoutException $e) {
                            report($e);
                            $moduleCompleted['certificate_requested'] = false;
                        }
                    }
                }
            }

            $readingDone = $completedThis || $progress->hasReadToEnd() || ! $readable;
            // Browser perlu memantau "sudah sampai akhir" hanya bila masih ada bacaan yang menentukan kemajuan:
            //  - tanpa latihan: sampai akhir = selesai;  - dengan latihan: sampai akhir = latihan terbuka (cukup sekali).
            $needsReading = $readable && ! $completedThis
                && ($lesson->hasQuiz() ? ! $progress->hasReadToEnd() : true);
        }

        $prevLesson = $lesson ? $path->previous($lesson) : null;
        $nextLesson = $lesson ? $path->next($lesson) : null;
        $nextModule = $nextLesson ? $path->moduleOf($nextLesson) : null;
        $nextUnlocked = $nextLesson ? $path->isUnlocked($nextLesson) : true;
        $completed = $path->completedIds();
        $modules = $path->modules;

        return view('learning.show', compact(
            'module','lesson','completed','modules','path','needsReading','completedThis','readingDone',
            'moduleCompleted','prevLesson','nextLesson','nextModule','nextUnlocked'
        ));
    }

    /** Arahkan peserta ke posisi belajarnya saat ini ketika ia mencoba membuka materi/modul yang masih terkunci. */
    private function redirectToFrontier(LearningPath $path, string $message): RedirectResponse
    {
        $target = $path->frontier();

        if (! $target) {
            return redirect()->route('dashboard')->with('locked_notice', $message);
        }

        return redirect()->route('learning.show', [$target->module_id, $target->id])->with('locked_notice', $message);
    }
}
