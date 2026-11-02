<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageDeletionTombstone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    /**
     * Menampilkan daftar percakapan pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        $userId = (int) $request->user()->id;

        $conversations = Conversation::query()
            ->where(function ($query) use ($userId) {
                $query->where(function ($buyerQuery) use ($userId) {
                    $buyerQuery->where('buyer_id', $userId)
                        ->whereNull('buyer_deleted_at');
                })->orWhere(function ($sellerQuery) use ($userId) {
                    $sellerQuery->where('seller_id', $userId)
                        ->whereNull('seller_deleted_at');
                });
            })

            // Percakapan tetap ditampilkan jika masih memiliki
            // pesan yang terlihat atau penanda pesan yang terlihat.
            ->where(function ($query) use ($userId) {
                $query->whereHas('messages', function ($messageQuery) use ($userId) {
                    $messageQuery->whereDoesntHave(
                        'userDeletions',
                        function ($deletionQuery) use ($userId) {
                            $deletionQuery->where('user_id', $userId);
                        }
                    );
                })->orWhereHas(
                    'messageDeletionTombstones',
                    function ($tombstoneQuery) use ($userId) {
                        $tombstoneQuery->whereNotExists(
                            function ($subQuery) use ($userId) {
                                $subQuery->select(DB::raw(1))
                                    ->from('message_user_deletions')
                                    ->whereColumn(
                                        'message_user_deletions.message_id',
                                        'message_deletion_tombstones.original_message_id'
                                    )
                                    ->where(
                                        'message_user_deletions.user_id',
                                        $userId
                                    );
                            }
                        );
                    }
                );
            })

            ->with([
                'barang',
                'buyer',
                'seller',
                'messages' => function ($query) use ($userId) {
                    $query->whereDoesntHave(
                        'userDeletions',
                        function ($deletionQuery) use ($userId) {
                            $deletionQuery->where('user_id', $userId);
                        }
                    )
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->limit(1);
                },
            ])

            // Hitung pesan lawan bicara yang belum dibaca
            // dan belum dihapus dari tampilan pengguna.
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->where('sender_id', '!=', $userId)
                        ->whereDoesntHave(
                            'userDeletions',
                            function ($deletionQuery) use ($userId) {
                                $deletionQuery->where('user_id', $userId);
                            }
                        )
                        ->whereRaw(
                            'messages.created_at > COALESCE(
                                (
                                    SELECT CASE
                                        WHEN conversations.buyer_id = ?
                                        THEN conversations.buyer_last_read_at
                                        ELSE conversations.seller_last_read_at
                                    END
                                    FROM conversations
                                    WHERE conversations.id = messages.conversation_id
                                ),
                                "1970-01-01 00:00:00"
                            )',
                            [$userId]
                        );
                },
            ])

            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        return view('pages.chat', compact('conversations'));
    }

    /**
     * Memulai percakapan antara pembeli dan penjual.
     */
    public function mulai(Request $request, Barang $barang)
    {
        $buyer = $request->user();

        abort_if(
            (int) $barang->user_id === (int) $buyer->id,
            403,
            'Kamu tidak dapat menghubungi diri sendiri.'
        );

        abort_unless(
            $barang->status === 'tersedia',
            403,
            'Barang ini sudah tidak tersedia.'
        );

        $sellerId = (int) $barang->user_id;

        $conversation = Conversation::firstOrCreate(
            [
                'barang_id' => $barang->id,
                'buyer_id' => $buyer->id,
            ],
            [
                'seller_id' => $sellerId,
            ]
        );

        abort_unless(
            (int) $conversation->seller_id === $sellerId,
            403,
            'Data percakapan tidak sesuai dengan pemilik barang.'
        );

        // Jika pembeli pernah menghapus obrolan,
        // tampilkan kembali ketika pembeli memulai chat lagi.
        if ($conversation->buyer_deleted_at !== null) {
            $conversation->buyer_deleted_at = null;
            $conversation->save();
        }

        return redirect()->route('chat.show', $conversation);
    }

    /**
     * Menghapus obrolan hanya dari daftar pengguna yang sedang login.
     * Pesan dan data percakapan tidak dihapus dari database.
     */
    public function deleteForMe(Request $request, Conversation $conversation)
    {
        $userId = (int) $request->user()->id;

        abort_unless(
            $userId === (int) $conversation->buyer_id ||
            $userId === (int) $conversation->seller_id,
            403,
            'Kamu tidak memiliki akses ke percakapan ini.'
        );

        if ($userId === (int) $conversation->buyer_id) {
            $conversation->buyer_deleted_at = now();
        } else {
            $conversation->seller_deleted_at = now();
        }

        $conversation->save();

        return response()->json([
            'success' => true,
            'message' => 'Obrolan berhasil dihapus dari daftar percakapan kamu.',
            'redirect' => route('chat.index'),
        ]);
    }

    /**
     * Menampilkan ruang percakapan.
     */
    public function show(Request $request, Conversation $conversation)
    {
        $userId = (int) $request->user()->id;

        abort_unless(
            $userId === (int) $conversation->buyer_id ||
            $userId === (int) $conversation->seller_id,
            403,
            'Kamu tidak memiliki akses ke percakapan ini.'
        );

        // Pastikan obrolan belum dihapus dari daftar pengguna.
        if ($userId === (int) $conversation->buyer_id) {
            abort_if(
                $conversation->buyer_deleted_at !== null,
                404,
                'Percakapan tidak ditemukan.'
            );

            $conversation->buyer_last_read_at = now();
        } else {
            abort_if(
                $conversation->seller_deleted_at !== null,
                404,
                'Percakapan tidak ditemukan.'
            );

            $conversation->seller_last_read_at = now();
        }

        $conversation->save();

        $conversation->load([
            'barang',
            'buyer',
            'seller',
        ]);

        /*
         * Ambil pesan yang belum dihapus untuk pengguna ini.
         */
        $visibleMessages = $conversation->messages()
            ->with('sender')
            ->whereDoesntHave(
                'userDeletions',
                function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            )
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        /*
         * Ambil penanda pesan yang dihapus untuk semua orang.
         *
         * Jika pengguna sebelumnya sudah menghapus pesan tersebut
         * untuk dirinya sendiri, penanda itu juga tidak ditampilkan.
         */
        $visibleTombstones = MessageDeletionTombstone::query()
            ->where('conversation_id', $conversation->id)
            ->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('message_user_deletions')
                    ->whereColumn(
                        'message_user_deletions.message_id',
                        'message_deletion_tombstones.original_message_id'
                    )
                    ->where(
                        'message_user_deletions.user_id',
                        $userId
                    );
            })
            ->with(['sender', 'deleter'])
            ->orderBy('original_created_at')
            ->orderBy('id')
            ->get();

        /*
         * Gabungkan pesan dan penanda ke dalam satu timeline.
         *
         * Format:
         * [
         *   ['kind' => 'message', 'item' => Message],
         *   ['kind' => 'deleted', 'item' => MessageDeletionTombstone],
         * ]
         */
        $timeline = collect();

        foreach ($visibleMessages as $message) {
            $timeline->push([
                'kind' => 'message',
                'item' => $message,
                'sort_at' => $message->created_at,
                'sort_id' => (int) $message->id,
            ]);
        }

        foreach ($visibleTombstones as $tombstone) {
            $timeline->push([
                'kind' => 'deleted',
                'item' => $tombstone,
                'sort_at' => $tombstone->original_created_at,
                'sort_id' => (int) $tombstone->id,
            ]);
        }

        $timeline = $timeline
            ->sort(function ($a, $b) {
                $timeComparison = $a['sort_at']->timestamp
                    <=> $b['sort_at']->timestamp;

                if ($timeComparison !== 0) {
                    return $timeComparison;
                }

                return $a['sort_id'] <=> $b['sort_id'];
            })
            ->values();

        return view('pages.chat-room', compact(
            'conversation',
            'timeline'
        ));
    }
}