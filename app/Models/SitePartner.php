<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/** Logo penyelenggara / mitra yang tampil di footer situs. */
class SitePartner extends Model
{
    protected $fillable = ['name', 'logo_path', 'url', 'sort_order'];

    public function logoUrl(): string
    {
        return Storage::disk('public')->url($this->logo_path);
    }
}
