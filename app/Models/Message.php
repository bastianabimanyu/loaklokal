<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'media_path',
        'media_mime',
        'location_lat',
        'location_lng',
        'location_name',
        'delivered_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'location_lat' => 'float',
            'location_lng' => 'float',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Percakapan tempat pesan ini berada.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Pengguna yang mengirim pesan.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function userDeletions()
    {
        return $this->hasMany(MessageUserDeletion::class);
    }
}
