<?php

namespace App\Support;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Jalur belajar seorang peserta: urutan materi dari Modul 1 sampai akhir.
 *
 * Aturan utama: sebuah materi hanya bisa dibuka bila SEMUA materi sebelumnya
 * (di modul yang sama maupun modul sebelumnya) sudah selesai. Materi yang sudah selesai
 * selalu boleh dibuka lagi untuk ditinjau. Admin boleh membuka semuanya untuk pratinjau.
 */
class LearningPath
{
    /** @var array<int, self> cache per request untuk mencegah re-query yang sama di satu sesi. */
    private static array $instances = [];

    /** @var Collection<int, Module> modul berurutan, lessons sudah dimuat */
    public Collection $modules;

    /** @var Collection<int, Lesson> seluruh materi dalam urutan belajar */
    public Collection $lessons;

    /** @var array<int, true> id materi yang sudah selesai */
    private array $completed;

    /** @var array<int, int> posisi materi berdasarkan ID */
    private array $lessonPositions = [];

    /** @var array<int, Module> modul berdasarkan ID */
    private array $modulesById = [];

    /** @var array<int, array{total: int, completed: int}> ringkasan progres per modul */
    private array $moduleProgress = [];

    private int $completedCount = 0;

    private ?int $firstIncompletePosition = null;

    private bool $bypass;

    public static function forUser(User $user): self
    {
        $id = $user->getKey();

        if (! isset(self::$instances[$id])) {
            self::$instances[$id] = new self($user);
        }

        return self::$instances[$id];
    }

    public static function forgetUser(User $user): void
    {
        unset(self::$instances[$user->getKey()]);
    }

    public function __construct(private User $user)
    {
        // Kolom isi (content/quiz) sengaja tidak dimuat: kerangka urutan belajar tidak membutuhkannya.
        $this->modules = Module::with('lessons:id,module_id,code,title,type,sort_order')->orderBy('sort_order')->get();
        $this->lessons = $this->modules->flatMap(fn (Module $m) => $m->lessons)->values();
        $this->completed = array_fill_keys(
            $user->lessonProgress()->whereNotNull('completed_at')->pluck('lesson_id')->all(),
            true
        );
        $this->modulesById = $this->modules->keyBy('id')->all();
        foreach ($this->modules as $module) {
            $this->moduleProgress[$module->id] = [
                'total' => $module->lessons->count(),
                'completed' => 0,
            ];
        }

        foreach ($this->lessons as $position => $lesson) {
            $this->lessonPositions[$lesson->id] = $position;
            if ($this->isCompleted($lesson)) {
                $this->completedCount++;
                $this->moduleProgress[$lesson->module_id]['completed']++;
            } elseif ($this->firstIncompletePosition === null) {
                $this->firstIncompletePosition = $position;
            }
        }
        $this->bypass = $user->role === 'admin';
    }

    public function isCompleted(Lesson|int $lesson): bool
    {
        return isset($this->completed[$lesson instanceof Lesson ? $lesson->id : $lesson]);
    }

    /** @return array<int, int> */
    public function completedIds(): array
    {
        return array_keys($this->completed);
    }

    public function totalCount(): int
    {
        return $this->lessons->count();
    }

    /** Jumlah materi selesai yang masih ada di kurikulum. */
    public function completedCount(): int
    {
        return $this->completedCount;
    }

    public function overallPercent(): int
    {
        return $this->totalCount() > 0 ? (int) round($this->completedCount() / $this->totalCount() * 100) : 0;
    }

    public function isUnlocked(Lesson $lesson): bool
    {
        if ($this->bypass || $this->isCompleted($lesson)) {
            return true;
        }

        $position = $this->lessonPositions[$lesson->id] ?? null;

        return $position !== null
            && ($this->firstIncompletePosition === null || $position <= $this->firstIncompletePosition);
    }

    /** Materi pertama yang belum selesai ("posisi belajar" peserta saat ini). */
    public function frontier(): ?Lesson
    {
        return $this->firstIncompletePosition === null
            ? $this->lessons->last()
            : $this->lessons[$this->firstIncompletePosition];
    }

    public function modulePercent(Module $module): int
    {
        $progress = $this->progressFor($module);
        if ($progress['total'] === 0) {
            return 0;
        }

        return (int) round($progress['completed'] / $progress['total'] * 100);
    }

    public function moduleCompletedCount(Module $module): int
    {
        return $this->progressFor($module)['completed'];
    }

    public function isModuleComplete(Module $module): bool
    {
        $progress = $this->progressFor($module);

        return $progress['total'] > 0 && $progress['completed'] === $progress['total'];
    }

    public function isModuleUnlocked(Module $module): bool
    {
        $m = $this->find($module);
        $first = $m->lessons->first();
        if ($first) {
            return $this->isUnlocked($first);
        }

        // Modul tanpa materi: terbuka bila semua modul sebelumnya tuntas.
        foreach ($this->modules as $other) {
            if ($other->id === $m->id) {
                return true;
            }
            if ($other->lessons->isNotEmpty() && ! $this->isModuleComplete($other)) {
                return false;
            }
        }

        return true;
    }

    /** Materi yang dibuka saat peserta masuk ke sebuah modul: yang belum selesai pertama, atau yang pertama. */
    public function resumeLessonFor(Module $module): ?Lesson
    {
        $m = $this->find($module);

        return $m->lessons->first(fn (Lesson $l) => ! $this->isCompleted($l)) ?? $m->lessons->first();
    }

    public function previous(Lesson $lesson): ?Lesson
    {
        $i = $this->lessonPositions[$lesson->id] ?? null;

        return ($i !== null && $i > 0) ? $this->lessons[$i - 1] : null;
    }

    public function next(Lesson $lesson): ?Lesson
    {
        $i = $this->lessonPositions[$lesson->id] ?? null;

        return ($i !== null && $i < $this->lessons->count() - 1) ? $this->lessons[$i + 1] : null;
    }

    public function moduleOf(Lesson $lesson): ?Module
    {
        return $this->modulesById[$lesson->module_id] ?? null;
    }

    /** Apakah seluruh modul (yang berisi materi) sudah tuntas. */
    public function allModulesComplete(): bool
    {
        return $this->lessons->isNotEmpty()
            && $this->modules->every(fn (Module $m) => $m->lessons->isEmpty() || $this->isModuleComplete($m));
    }

    /** Data untuk alert "Selamat, Modul X selesai" (dipakai SweetAlert di browser). */
    public function moduleCompletionPayload(Module $module): array
    {
        $m = $this->find($module);
        $index = $this->modules->search(fn (Module $x) => $x->id === $m->id);
        $nextModule = $this->modules->slice(($index === false ? 0 : $index) + 1)->first(fn (Module $x) => $x->lessons->isNotEmpty());

        return [
            'number' => $m->number,
            'title' => $m->title,
            'badge' => $m->badge,
            'badge_icon' => $m->badgeIcon(),
            'badge_image' => $m->badgeImageUrl(),
            'next' => $nextModule ? [
                'number' => $nextModule->number,
                'title' => $nextModule->title,
                'url' => route('learning.show', ['module' => $nextModule->id]),
            ] : null,
            'all_done' => $this->allModulesComplete(),
            'dashboard_url' => route('dashboard'),
        ];
    }

    private function find(Module $module): Module
    {
        return $this->modules->firstWhere('id', $module->id) ?? $module->loadMissing('lessons');
    }

    /** @return array{total: int, completed: int} */
    private function progressFor(Module $module): array
    {
        if (isset($this->moduleProgress[$module->id])) {
            return $this->moduleProgress[$module->id];
        }

        $lessons = $this->find($module)->lessons;

        return [
            'total' => $lessons->count(),
            'completed' => $lessons->filter(fn (Lesson $lesson) => $this->isCompleted($lesson))->count(),
        ];
    }
}
