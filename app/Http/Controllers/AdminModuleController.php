<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $data = $this->applyBadgeImage($request, $this->validateData($request), null);
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
        $data = $this->applyBadgeImage($request, $this->validateData($request), $module);
        // Nomor modul menjadi identitas urutan, jadi tidak diubah dari form edit.
        $data['number'] = $module->number;
        $module->update($data);

        return redirect()->route('admin.modules.index')->with('success', 'Modul berhasil diperbarui.');
    }

    public function destroy(Module $module)
    {
        if ($module->badge_image) {
            Storage::disk('public')->delete($module->badge_image);
        }
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
            'badge_icon' => ['nullable', 'string', 'max:16'],
            'badge_color' => ['nullable', 'in:' . implode(',', array_keys(Module::BADGE_COLORS))],
            'badge_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_badge_image' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Simpan/ganti/hapus logo lencana. File lama dibersihkan supaya storage tidak menumpuk.
     */
    private function applyBadgeImage(Request $request, array $data, ?Module $module): array
    {
        unset($data['badge_image'], $data['remove_badge_image']);
        $data['badge_color'] = $data['badge_color'] ?? 'gold';
        $old = $module?->badge_image;

        if ($request->hasFile('badge_image')) {
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $data['badge_image'] = $request->file('badge_image')->store('badges', 'public');
        } elseif ($request->boolean('remove_badge_image') && $old) {
            Storage::disk('public')->delete($old);
            $data['badge_image'] = null;
        }

        return $data;
    }
}
