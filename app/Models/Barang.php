<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barang extends Model
{
    protected $fillable = [
        'nama',
        'kategori',
        'mode',
        'harga',
        'deskripsi',
        'kota_kabupaten',
        'kecamatan',
        'foto',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disukaiOleh()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'barang_favorit',
            'barang_id',
            'user_id'
        )->withTimestamps();
    }
}
