<?php

namespace App\Http\Controllers;

use Ably\PubSub\Server;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageDeletionTombstone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Mengirim pesan teks, gambar, atau video.
     */
    public function kirim(Request $request, Conversation $conversation)
    {
        $userId = (int) $request->user()->id;

        $this->authorizeConversation($userId, $conversation);

        $validated = $request->validate([
            'type' => ['nullable', 'in:text,image,video'],
            'body' => ['nullable', 'string', 'max:5000'],

            'image' => [
                'required_if:type,image',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:5120',
            ],

            'video' => [
                'required_if:type,video',
                'nullable',
                'file',
                'mimes:mp4,webm,ogg',
                'max:51200',
            ],
        ]);

        $type = $validated['type'] ?? 'text';
        $body = trim($validated['body'] ?? '');

        if ($type === 'text' && $body === '') {
            return response()->json([
                'success' => false,
                'message' => 'Pesan tidak boleh kosong.',
            ], 422);
        }

        if ($type === 'image' && !$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih gambar terlebih dahulu.',
            ], 422);
        }

        if ($type === 'video' && !$request->hasFile('video')) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih video terlebih dahulu.',
            ], 422);
        }

        /*
         * Upload media jika pesan berupa gambar atau video.
         */
        $mediaPath = null;
        $mediaMime = null;

        if (in_array($type, ['image', 'video'], true)) {
            $file = $request->file($type);

            $mediaPath = $file->store(
                "chat/{$conversation->id}",
                'public'
            );

            if (!$mediaPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengunggah media.',
                ], 500);
            }

            $mediaMime = $file->getMimeType();
        }

        try {
            /*
             * Simpan pesan dan pulihkan visibilitas percakapan
             * untuk kedua pihak dalam satu transaksi.
             */
            $message = DB::transaction(function () use (
                $conversation,
                $userId,
                $type,
                $body,
                $mediaPath,
                $mediaMime
            ) {
                $message = $conversation->messages()->create([
                    'sender_id' => $userId,
                    'type' => $type,
                    'body' => $body !== '' ? $body : null,
                    'media_path' => $mediaPath,
                    'media_mime' => $mediaMime,
                ]);

                // Pesan baru mengaktifkan kembali obrolan
                // untuk pembeli maupun penjual.
                $conversation->buyer_deleted_at = null;
                $conversation->seller_deleted_at = null;

                $conversation->save();

                // Memperbarui waktu aktivitas percakapan.
                $conversation->touch();

                return $message;
            });
        } catch (\Throwable $exception) {
            if ($mediaPath) {
                Storage::disk('public')->delete($mediaPath);
            }

            throw $exception;
        }

        /*
         * Payload pesan yang dikirim melalui Ably.
         */
        $messageData = [
            'id' => (int) $message->id,
            'conversation_id' => (int) $message->conversation_id,
            'sender_id' => (int) $message->sender_id,
            'type' => $message->type,
            'body' => $message->body,
            'media_path' => $message->media_path,
            'media_mime' => $message->media_mime,
            'media_url' => $message->media_path
                ? asset('storage/' . $message->media_path)
                : null,
            'created_at' => $message->created_at->toISOString(),
            'delivered_at' => null,
            'read_at' => null,
        ];

        /*
         * Kirim event ke channel percakapan.
         * Digunakan oleh halaman ruang chat.
         */
        $this->publishToAbly(
            $conversation,
            'message',
            $messageData
        );

        /*
         * Kirim event ke channel khusus pembeli.
         */
        $this->publishToUser(
            (int) $conversation->buyer_id,
            'conversation-message',
            $messageData
        );

        /*
         * Kirim event ke channel khusus penjual.
         */
        $this->publishToUser(
            (int) $conversation->seller_id,
            'conversation-message',
            $messageData
        );

        return response()->json([
            'success' => true,
            'message' => $messageData,
        ], 201);
    }

    /**
     * Mencatat bahwa pesan sudah diterima.
     */
    public function delivered(
        Request $request,
        Conversation $conversation,
        Message $message
    ) {
        $userId = (int) $request->user()->id;

        $this->authorizeConversation($userId, $conversation);
        $this->authorizeMessage($conversation, $message);

        abort_if(
            (int) $message->sender_id === $userId,
            403,
            'Pengirim tidak dapat mengonfirmasi pesannya sendiri.'
        );

        if ($message->delivered_at === null) {
            $message->forceFill([
                'delivered_at' => now(),
            ])->save();

            $this->publishToAbly(
                $conversation,
                'message-status',
                [
                    'message_id' => (int) $message->id,
                    'status' => 'delivered',
                    'delivered_at' => $message->delivered_at->toISOString(),
                    'read_at' => $message->read_at?->toISOString(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message_id' => (int) $message->id,
            'status' => $message->read_at ? 'read' : 'delivered',
        ]);
    }

    /**
     * Menandai pesan masuk sebagai sudah dibaca.
     */
    public function read(
        Request $request,
        Conversation $conversation
    ) {
        $userId = (int) $request->user()->id;

        $this->authorizeConversation($userId, $conversation);

        $messages = $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->get();

        if ($messages->isEmpty()) {
            return response()->json([
                'success' => true,
                'message_ids' => [],
            ]);
        }

        $readAt = now();

        $conversation->messages()
            ->whereIn('id', $messages->pluck('id'))
            ->update([
                'delivered_at' => $readAt,
                'read_at' => $readAt,
            ]);

        $messageIds = $messages->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $this->publishToAbly(
            $conversation,
            'messages-read',
            [
                'message_ids' => $messageIds,
                'read_at' => $readAt->toISOString(),
            ]
        );

        return response()->json([
            'success' => true,
            'message_ids' => $messageIds,
        ]);
    }

    /**
     * Menghapus pesan hanya dari tampilan pengguna yang meminta.
     * Pesan asli tetap tersimpan di database.
     */
    public function deleteForMe(
        Request $request,
        Conversation $conversation,
        Message $message
    ) {
        $userId = (int) $request->user()->id;

        $this->authorizeConversation($userId, $conversation);
        $this->authorizeMessage($conversation, $message);

        DB::table('message_user_deletions')->insertOrIgnore([
            'message_id' => $message->id,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dihapus dari tampilan kamu.',
            'message_id' => (int) $message->id,
        ]);
    }

    /**
     * Menghapus pesan untuk semua orang.
     * Hanya pengirim pesan yang boleh melakukan tindakan ini.
     */
    public function deleteForEveryone(
        Request $request,
        Conversation $conversation,
        Message $message
    ) {
        $userId = (int) $request->user()->id;

        $this->authorizeConversation($userId, $conversation);
        $this->authorizeMessage($conversation, $message);

        abort_unless(
            (int) $message->sender_id === $userId,
            403,
            'Hanya pengirim pesan yang dapat menghapus pesan untuk semua orang.'
        );

        $originalCreatedAt = $message->created_at;
        $mediaPath = $message->media_path;

        /*
         * Simpan penanda penghapusan dan hapus pesan asli
         * dalam satu transaksi database.
         */
        $deletionData = DB::transaction(function () use (
            $conversation,
            $message,
            $userId,
            $originalCreatedAt
        ) {
            $deletedAt = now();

            $tombstone = MessageDeletionTombstone::create([
                'original_message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'sender_id' => $message->sender_id,
                'deleted_by' => $userId,
                'original_created_at' => $originalCreatedAt,
                'deleted_at' => $deletedAt,
            ]);

            $messageId = $message->id;

            // Hapus pesan asli.
            $message->delete();

            // Perbarui aktivitas percakapan.
            $conversation->touch();

            return [
                'message_id' => (int) $messageId,
                'original_message_id' => (int) $tombstone->original_message_id,
                'conversation_id' => (int) $conversation->id,
                'sender_id' => (int) $tombstone->sender_id,
                'deleted_by' => (int) $tombstone->deleted_by,
                'created_at' => $originalCreatedAt->toISOString(),
                'original_created_at' => $originalCreatedAt->toISOString(),
                'deleted_at' => $deletedAt->toISOString(),
            ];
        });

        /*
         * Hapus media setelah transaksi database berhasil.
         */
        if ($mediaPath) {
            try {
                Storage::disk('public')->delete($mediaPath);
            } catch (\Throwable $exception) {
                Log::warning('Gagal menghapus media pesan.', [
                    'conversation_id' => $conversation->id,
                    'message_id' => $deletionData['message_id'],
                    'media_path' => $mediaPath,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        /*
         * Beri tahu kedua pihak bahwa pesan telah dihapus.
         */
        $this->publishToAbly(
            $conversation,
            'message-deleted-everyone',
            $deletionData
        );

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dihapus untuk semua orang.',
            'message_id' => $deletionData['message_id'],
            'created_at' => $deletionData['created_at'],
            'deleted_at' => $deletionData['deleted_at'],
        ]);
    }

    /**
     * Memastikan pengguna merupakan anggota percakapan.
     */
    private function authorizeConversation(
        int $userId,
        Conversation $conversation
    ): void {
        abort_unless(
            $userId === (int) $conversation->buyer_id ||
            $userId === (int) $conversation->seller_id,
            403,
            'Kamu tidak memiliki akses ke percakapan ini.'
        );
    }

    /**
     * Memastikan pesan berada dalam percakapan yang benar.
     */
    private function authorizeMessage(
        Conversation $conversation,
        Message $message
    ): void {
        abort_unless(
            (int) $message->conversation_id === (int) $conversation->id,
            404,
            'Pesan tidak ditemukan dalam percakapan ini.'
        );
    }

    /**
     * Menerbitkan event ke channel percakapan.
     */
    private function publishToAbly(
        Conversation $conversation,
        string $event,
        array $data
    ): void {
        try {
            $ably = Server::createHttpClient([
                'key' => config('services.ably.key'),
            ]);

            $channelName = "loaklokal:conversation:{$conversation->id}";

            $ably->channels
                ->get($channelName)
                ->publish($event, $data);

        } catch (\Throwable $exception) {
            Log::warning('Gagal menerbitkan event Ably.', [
                'conversation_id' => $conversation->id,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Menerbitkan event ke channel khusus pengguna.
     */
    private function publishToUser(
        int $userId,
        string $event,
        array $data
    ): void {
        try {
            $ably = Server::createHttpClient([
                'key' => config('services.ably.key'),
            ]);

            $channelName = "loaklokal:user:{$userId}";

            $ably->channels
                ->get($channelName)
                ->publish($event, $data);

        } catch (\Throwable $exception) {
            Log::warning('Gagal menerbitkan event Ably ke channel pengguna.', [
                'user_id' => $userId,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}