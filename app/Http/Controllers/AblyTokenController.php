<?php

namespace App\Http\Controllers;

use Ably\PubSub\Server;
use App\Models\Conversation;
use Illuminate\Http\Request;

class AblyTokenController extends Controller
{
    /**
     * Token Ably untuk satu ruang percakapan.
     */
    public function generate(Request $request, $conversationId)
    {
        $user = $request->user();

        $conversation = Conversation::findOrFail($conversationId);

        abort_unless(
            (int) $user->id === (int) $conversation->buyer_id ||
            (int) $user->id === (int) $conversation->seller_id,
            403,
            'Kamu tidak memiliki akses ke percakapan ini.'
        );

        $channelName = "loaklokal:conversation:{$conversation->id}";

        $ably = Server::createHttpClient([
            'key' => config('services.ably.key'),
        ]);

        $tokenDetails = $ably->auth->authorize([
            'ttl' => 3600000,
            'capability' => json_encode([
                $channelName => [
                    'publish',
                    'subscribe',
                    'presence',
                ],
            ]),
            'clientId' => 'user-' . $user->id,
        ]);

        return response()->json([
            'token' => $tokenDetails->token,
            'expires' => $tokenDetails->expires,
            'issued' => $tokenDetails->issued,
            'capability' => $tokenDetails->capability,
            'clientId' => $tokenDetails->clientId,
        ]);
    }

    /**
     * Token Ably khusus halaman daftar percakapan.
     *
     * Pengguna dapat subscribe ke:
     * 1. Channel khusus miliknya untuk menerima notifikasi
     *    pesan baru, termasuk dari percakapan yang sebelumnya
     *    dihapus dari daftar chat.
     * 2. Channel percakapan yang melibatkan dirinya untuk
     *    menerima pembaruan pesan secara real-time.
     *
     * Token ini hanya memberikan izin subscribe.
     */
    public function generateForList(Request $request)
    {
        $user = $request->user();
        $userId = (int) $user->id;

        // Channel khusus pengguna.
        $userChannel = "loaklokal:user:{$userId}";

        $capability = [
            $userChannel => ['subscribe'],
        ];

        // Ambil seluruh percakapan milik pengguna,
        // termasuk percakapan yang pernah dihapus dari daftar.
        $conversations = Conversation::query()
            ->where(function ($query) use ($userId) {
                $query->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId);
            })
            ->pluck('id');

        // Berikan izin subscribe ke setiap channel percakapan.
        foreach ($conversations as $conversationId) {
            $channelName = "loaklokal:conversation:{$conversationId}";

            $capability[$channelName] = ['subscribe'];
        }

        $ably = Server::createHttpClient([
            'key' => config('services.ably.key'),
        ]);

        $tokenDetails = $ably->auth->authorize([
            'ttl' => 3600000,
            'capability' => json_encode($capability),
            'clientId' => 'user-' . $userId,
        ]);

        return response()->json([
            'token' => $tokenDetails->token,
            'expires' => $tokenDetails->expires,
            'issued' => $tokenDetails->issued,
            'capability' => $tokenDetails->capability,
            'clientId' => $tokenDetails->clientId,
        ]);
    }
}

