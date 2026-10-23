<?php
namespace App\Http\Controllers;

use App\Models\SitePartner;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', ['setting' => SiteSetting::current()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:300'],
            'footer_text' => ['nullable', 'string', 'max:200'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico', 'max:512'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ], [
            'site_name.required' => 'Nama web wajib diisi.',
            'site_name.max' => 'Nama web terlalu panjang (maksimal 60 karakter).',
            'tagline.max' => 'Tagline terlalu panjang (maksimal 120 karakter).',
            'description.max' => 'Deskripsi terlalu panjang (maksimal 300 karakter).',
            'footer_text.max' => 'Teks footer terlalu panjang (maksimal 200 karakter).',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'favicon.mimes' => 'Favicon harus berformat PNG, ICO, JPG, atau WebP.',
            'favicon.max' => 'Ukuran favicon maksimal 512 KB.',
        ]);

        $setting = SiteSetting::current();

        $values = [
            'site_name' => trim($data['site_name']),
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'footer_text' => $data['footer_text'] ?? null,
            'show_name_with_logo' => $request->boolean('show_name_with_logo'),
        ];

        foreach (['logo' => 'logo_path', 'favicon' => 'favicon_path'] as $field => $column) {
            $old = $setting->{$column};
            if ($request->hasFile($field)) {
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $values[$column] = $request->file($field)->store('site', 'public');
            } elseif ($request->boolean('remove_' . $field) && $old) {
                Storage::disk('public')->delete($old);
                $values[$column] = null;
            }
        }

        $setting->update($values);
        SiteSetting::forgetCache();

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan situs berhasil disimpan.');
    }

    public function storePartner(Request $request)
    {
        $data = $request->validateWithBag('partner', [
            'name' => ['required', 'string', 'max:120'],
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'url' => ['nullable', 'url:http,https', 'max:255'],
        ], [
            'name.required' => 'Nama penyelenggara wajib diisi.',
            'name.max' => 'Nama penyelenggara terlalu panjang (maksimal 120 karakter).',
            'logo.required' => 'Logo penyelenggara wajib diunggah.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'url.url' => 'Tautan harus diawali http:// atau https://.',
        ]);

        SitePartner::create([
            'name' => trim($data['name']),
            'logo_path' => $request->file('logo')->store('site/partners', 'public'),
            'url' => $data['url'] ?? null,
            'sort_order' => ((int) SitePartner::max('sort_order')) + 1,
        ]);

        return redirect()->route('admin.settings.edit')->with('success', 'Logo penyelenggara "' . trim($data['name']) . '" berhasil ditambahkan.');
    }

    public function destroyPartner(SitePartner $partner)
    {
        $name = $partner->name;
        Storage::disk('public')->delete($partner->logo_path);
        $partner->delete();

        return redirect()->route('admin.settings.edit')->with('success', 'Logo penyelenggara "' . $name . '" dihapus.');
    }
}
