<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangFavoritController extends Controller
{
    public function index(Request $request)
    {
        $barangs = $request->user()
            ->barangFavorit()
            ->with('user')
            ->latest('barang_favorit.created_at')
            ->paginate(12);

        return view('pages.barang-favorit', compact('barangs'));
    }

    public function toggle(Request $request, Barang $barang)
    {
        $user = $request->user();

        // Jangan izinkan pengguna memfavoritkan barang miliknya sendiri.
        if ((int) $barang->user_id === (int) $user->id) {
            return back()->with(
                'error',
                'Kamu tidak dapat memfavoritkan barang milik sendiri.'
            );
        }

        // Barang yang tidak tersedia tidak dapat ditambahkan ke favorit.
        if ($barang->status !== 'tersedia') {
            return back()->with(
                'error',
                'Barang ini sudah tidak tersedia.'
            );
        }

        $sudahFavorit = $user->barangFavorit()
            ->where('barangs.id', $barang->id)
            ->exists();

        if ($sudahFavorit) {
            $user->barangFavorit()->detach($barang->id);

            return back()->with('success', 'Barang dihapus dari favorit.');
        }

        $user->barangFavorit()->syncWithoutDetaching([$barang->id]);

        return back()->with('success', 'Barang ditambahkan ke favorit.');
    }
}
