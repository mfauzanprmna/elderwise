<?php

namespace Database\Seeders;

use App\Models\SitePartner;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Isian awal pengaturan situs dari poster acara ElderWiSE + logo penyelenggara.
 *
 * Aman dijalankan berulang: tidak menimpa nilai yang sudah diubah admin,
 * dan logo penyelenggara hanya ditambahkan jika daftarnya masih kosong.
 *
 * php artisan db:seed --class=SiteSettingSeeder
 */
class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $setting = SiteSetting::current();
        $updates = [];

        if (blank($setting->description)) {
            $updates['description'] = SiteSetting::DEFAULTS['description'];
        }
        if (blank($setting->tagline) || $setting->tagline === 'Digital Confidence Journey') {
            $updates['tagline'] = SiteSetting::DEFAULTS['tagline'];
        }
        if (blank($setting->footer_text)) {
            $updates['footer_text'] = '© 2026 ElderWiSE · Women Action for Senior Educators Empowerment on Digital Literacy via Gamified Platform';
        }
        if ($updates) {
            $setting->update($updates);
        }

        if (SitePartner::count() === 0) {
            $partners = [
                ['name' => 'IEEE Women in Engineering Indonesia Section', 'file' => 'ieee-wie-indonesia.png'],
                ['name' => 'IEEE Indonesia Section', 'file' => 'ieee-indonesia-section.png'],
            ];

            foreach ($partners as $i => $partner) {
                $source = database_path('seeders/assets/partners/' . $partner['file']);
                if (! is_file($source)) {
                    continue;
                }
                $path = 'site/partners/' . $partner['file'];
                Storage::disk('public')->put($path, file_get_contents($source));
                SitePartner::create(['name' => $partner['name'], 'logo_path' => $path, 'sort_order' => $i + 1]);
            }
        }

        SiteSetting::forgetCache();
    }
}
