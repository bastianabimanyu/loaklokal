<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'barang_id',
        'buyer_id',
        'seller_id',
    ];

    /**
     * Casting atribut model.
     */
    protected function casts(): array
    {
        return [
            'buyer_deleted_at' => 'datetime',
            'seller_deleted_at' => 'datetime',
            'buyer_last_read_at' => 'datetime',
            'seller_last_read_at' => 'datetime',
        ];
    }

    /**
     * Barang yang menjadi konteks percakapan.
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * Pengguna yang memulai percakapan sebagai pembeli.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Pemilik barang sebagai penjual.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Seluruh pesan dalam percakapan.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Riwayat penanda pesan yang dihapus untuk semua orang.
     */
    public function messageDeletionTombstones(): HasMany
    {
        return $this->hasMany(MessageDeletionTombstone::class);
    }
}
