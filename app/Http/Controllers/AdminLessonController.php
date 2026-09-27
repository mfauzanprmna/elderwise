<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminLessonController extends Controller
{
    public function index(Request $request)
    {
        $modules = Module::orderBy('sort_order')->get();
        $query = Lesson::with('module')->orderBy('module_id')->orderBy('sort_order');

        if ($request->filled('module') && is_numeric($request->module)) {
            $query->where('module_id', (int) $request->module);
        }

        return view('admin.lessons.index', [
            'lessons' => $query->get(),
            'modules' => $modules,
            'selectedModule' => $request->module,
        ]);
    }

    public function create(Request $request)
    {
        $modules = Module::orderBy('sort_order')->get();
        $module = $modules->firstWhere('id', (int) $request->module) ?: $modules->first();
        $nextOrder = $module ? ((int) $module->lessons()->max('sort_order') + 1) : 1;
        $nextCode = $module ? $module->number . '.' . $nextOrder : '';

        return view('admin.lessons.form', [
            'modules' => $modules,
            'lesson' => new Lesson([
                'module_id' => $module?->id,
                'code' => $nextCode,
                'sort_order' => $nextOrder,
                'type' => 'text',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $module = Module::findOrFail($data['module_id']);
        $nextOrder = (int) $module->lessons()->max('sort_order') + 1;
        $data['sort_order'] = $nextOrder;
        $data['code'] = $module->number . '.' . $nextOrder;

        if ($request->hasFile('pdf')) {
            $data['pdf_path'] = $request->file('pdf')->store('pdfs', 'public');
        }

        Lesson::create($data);

        return redirect()->route('admin.lessons.index', ['module' => $module->id])
            ->with('success', "Materi {$data['code']} berhasil dibuat.");
    }

    public function edit(Lesson $lesson)
    {
        return view('admin.lessons.form', [
            'modules' => Module::orderBy('sort_order')->get(),
            'lesson' => $lesson,
        ]);
    }

    public function update(Request $request, Lesson $lesson)
    {
        $data = $this->validateData($request);
        $module = Module::findOrFail($data['module_id']);

        // Kode dan urutan selalu mengikuti modul dan posisi materi.
        $currentOrder = $lesson->sort_order;
        $data['sort_order'] = $currentOrder ?: ((int) $module->lessons()->max('sort_order') + 1);
        $data['code'] = $module->number . '.' . $data['sort_order'];

        if ($request->hasFile('pdf')) {
            if ($lesson->pdf_path) {
                Storage::disk('public')->delete($lesson->pdf_path);
            }
            $data['pdf_path'] = $request->file('pdf')->store('pdfs', 'public');
        }

        $lesson->update($data);

        return redirect()->route('admin.lessons.index', ['module' => $module->id])
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Lesson $lesson)
    {
        $moduleId = $lesson->module_id;
        if ($lesson->pdf_path) {
            Storage::disk('public')->delete($lesson->pdf_path);
        }
        $lesson->delete();
        $this->renumberLessons($moduleId);

        return redirect()->route('admin.lessons.index', ['module' => $moduleId])
            ->with('success', 'Materi berhasil dihapus dan nomor materi dirapikan.');
    }

    private function renumberLessons(int $moduleId): void
    {
        $module = Module::find($moduleId);
        if (!$module) return;

        foreach ($module->lessons()->orderBy('sort_order')->get()->values() as $index => $lesson) {
            $order = $index + 1;
            $lesson->update(['sort_order' => $order, 'code' => $module->number . '.' . $order]);
        }
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', 'in:text,pdf'],
            'content' => ['nullable', 'string'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'quiz_enabled' => ['nullable', 'boolean'],
            'quiz_json' => ['nullable', 'string'],
        ]);

        if ($data['type'] === 'pdf' && !$request->hasFile('pdf')) {
            $existingLesson = $request->route('lesson');
            if (!$existingLesson || !$existingLesson->pdf_path) {
                return back()->withErrors(['pdf' => 'Materi PDF membutuhkan file PDF.'])->withInput()->throwResponse();
            }
        }

        $quiz = null;
        if ($request->boolean('quiz_enabled')) {
            if (!filled($data['quiz_json'] ?? null)) {
                return back()->withErrors(['quiz_json' => 'Quiz diaktifkan, tetapi belum ada soal.'])->withInput()->throwResponse();
            }
            $quiz = json_decode($data['quiz_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($quiz)) {
                return back()->withErrors(['quiz_json' => 'Format JSON quiz tidak valid.'])->withInput()->throwResponse();
            }
        }

        $data['quiz'] = $quiz;
        unset($data['quiz_json'], $data['pdf'], $data['quiz_enabled'], $data['existing_pdf_path']);

        return $data;
    }
}
