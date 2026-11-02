
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangFavoritController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AblyTokenController;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('beranda')
        : redirect()->route('login');
});

// AUTENTIKASI GUEST
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.process');
});

// ROUTE PENGGUNA LOGIN
Route::middleware('auth')->group(function () {

    // BERANDA
    Route::get('/beranda', function () {
        $barangs = Barang::with('user')
            ->where('status', 'tersedia')
            ->latest()
            ->get();

        return view('pages.beranda', compact('barangs'));
    })->name('beranda');

    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // BARANG FAVORIT
    Route::get('/barang-favorit', [BarangFavoritController::class, 'index'])
        ->name('barang-favorit.index');

    Route::post('/barang/{barang}/favorit', [BarangFavoritController::class, 'toggle'])
        ->name('barang.favorit.toggle');

    // PROFIL
    Route::get('/profil/edit-akun', [ProfileController::class, 'edit'])
        ->name('profil.edit-akun');

    Route::put('/profil/edit-akun', [ProfileController::class, 'update'])
        ->name('profil.update-akun');

    // SISTEM CHAT

    Route::post('/barang/{barang}/chat', [ConversationController::class, 'mulai'])
        ->name('chat.start');

    Route::get('/chat', [ConversationController::class, 'index'])
        ->name('chat.index');

    // TOKEN ABLY UNTUK DAFTAR CHAT
    Route::get('/chat/ably-token/list', [AblyTokenController::class, 'generateForList'])
        ->name('chat.list-ably-token');

    // HAPUS OBROLAN HANYA DARI DAFTAR PENGGUNA YANG MEMILIH
    Route::delete(
        '/chat/{conversation}/delete-for-me',
        [ConversationController::class, 'deleteForMe']
    )->name('chat.delete-for-me');

    // DETAIL OBROLAN
    Route::get('/chat/{conversation}', [ConversationController::class, 'show'])
        ->name('chat.show');

    // PESAN
    Route::post('/chat/{conversation}/messages', [MessageController::class, 'kirim'])
        ->name('chat.messages.store');

    // STATUS PESAN TERKIRIM
    Route::post(
        '/chat/{conversation}/messages/{message}/delivered',
        [MessageController::class, 'delivered']
    )->name('chat.messages.delivered');

    // STATUS PESAN DIBACA
    Route::post(
        '/chat/{conversation}/messages/read',
        [MessageController::class, 'read']
    )->name('chat.messages.read');

    // HAPUS PESAN DARI AKUN SENDIRI
    Route::delete(
        '/chat/{conversation}/messages/{message}/delete-for-me',
        [MessageController::class, 'deleteForMe']
    )->name('chat.messages.delete-for-me');

    // HAPUS PESAN UNTUK SEMUA ORANG
    Route::delete(
        '/chat/{conversation}/messages/{message}/delete-for-everyone',
        [MessageController::class, 'deleteForEveryone']
    )->name('chat.messages.delete-for-everyone');

    // TOKEN ABLY SATU PERCAKAPAN
    Route::get(
        '/chat/{conversation}/ably-token',
        [AblyTokenController::class, 'generate']
    )->name('chat.ably-token');
});

// KATALOG BARANG
Route::get('/barang', [BarangController::class, 'index'])
    ->name('barang.index');

// OPERASI BARANG YANG MEMERLUKAN LOGIN
Route::middleware('auth')->group(function () {

    Route::get('/barang/upload', [BarangController::class, 'create'])
        ->name('barang.create');

    Route::post('/barang', [BarangController::class, 'store'])
        ->name('barang.store');

    Route::get('/barang/{barang}/edit', [BarangController::class, 'edit'])
        ->name('barang.edit');

    Route::put('/barang/{barang}', [BarangController::class, 'update'])
        ->name('barang.update');

    Route::patch('/barang/{barang}/terjual', [BarangController::class, 'tandaiTerjual'])
        ->name('barang.terjual');

    Route::delete('/barang/{barang}', [BarangController::class, 'destroy'])
        ->name('barang.destroy');

    Route::get('/barang/{barang}', [BarangController::class, 'show'])
        ->name('barang.show');
});

// HALAMAN FILTER
Route::get('/filter', function (Request $request) {
    $barangs = Barang::with('user')
        ->where('status', 'tersedia')
        ->latest()
        ->get();

    $favoritIds = $request->user()
        ? $request->user()->barangFavorit()->pluck('barangs.id')->toArray()
        : [];

    return view('pages.filter', compact('barangs', 'favoritIds'));
})->name('filter');

// HALAMAN PROFIL
Route::get('/profil', function () {
    return view('pages.profil');
})->middleware('auth')->name('profil');
