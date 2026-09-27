<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = ['user_id', 'number', 'issued_at'];
    protected $casts = ['issued_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
