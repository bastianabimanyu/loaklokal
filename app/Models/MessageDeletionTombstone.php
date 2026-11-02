<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageDeletionTombstone extends Model
{
    protected $fillable = [
        'original_message_id',
        'conversation_id',
        'sender_id',
        'deleted_by',
        'original_created_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'original_created_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
