<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class AdminModuleController extends Controller
{
    public function index()
    {
        return view('admin.modules.index', [
            'modules' => Module::withCount('lessons')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        $nextNumber = ((int) Module::max('number')) + 1;
        return view('admin.modules.form', [
            'module' => new Module(['number' => $nextNumber, 'sort_order' => $nextNumber]),
            'nextNumber' => $nextNumber,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $nextNumber = ((int) Module::max('number')) + 1;
        $data['number'] = $nextNumber;
        $data['sort_order'] = $nextNumber;

        $module = Module::create($data);

        // Setelah modul dibuat, langsung buka tabel materi modul tersebut.
        return redirect()->route('admin.lessons.index', ['module' => $module->id])
            ->with('success', "Modul {$module->number} berhasil dibuat. Silakan tambahkan materi.");
    }

    public function edit(Module $module)
    {
        return view('admin.modules.form', [
            'module' => $module,
            'nextNumber' => $module->number,
        ]);
    }

    public function update(Request $request, Module $module)
    {
        $data = $this->validateData($request);
        // Nomor modul menjadi identitas urutan, jadi tidak diubah dari form edit.
        $data['number'] = $module->number;
        $module->update($data);

        return redirect()->route('admin.modules.index')->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroy(Module $module)
    {
        $module->delete();
        return redirect()->route('admin.modules.index')->with('success', 'Modul dan materi di dalamnya berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slogan' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'badge' => ['nullable', 'string', 'max:180'],
        ]);
    }
}
