
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\BarangFavoritController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('beranda')
        : redirect()->route('login');
});

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

Route::middleware('auth')->group(function () {
    Route::get('/beranda', function () {
        $barangs = Barang::with('user')
        ->where('status', 'tersedia')
        ->latest()
        ->get();

        return view('pages.beranda', compact('barangs'));
    })->name('beranda');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/barang-favorit', [BarangFavoritController::class, 'index'])
    ->name('barang-favorit.index');

    Route::post('/barang/{barang}/favorit', [BarangFavoritController::class, 'toggle'])
    ->name('barang.favorit.toggle');

    Route::get('/profil/edit-akun', [ProfileController::class, 'edit'])
    ->name('profil.edit-akun');

    Route::put('/profil/edit-akun', [ProfileController::class, 'update'])
        ->name('profil.update-akun');
});




/*
|--------------------------------------------------------------------------
| Katalog Barang LoakLokal
|--------------------------------------------------------------------------
*/

// Katalog dapat dilihat pengunjung.
Route::get('/barang', [BarangController::class, 'index'])
    ->name('barang.index');

// Operasi pengelolaan barang wajib login.
Route::middleware('auth')->group(function () {

    // Form upload barang.
    Route::get('/barang/upload', [BarangController::class, 'create'])
        ->name('barang.create');

    // Menyimpan barang baru.
    Route::post('/barang', [BarangController::class, 'store'])
        ->name('barang.store');

    // Form edit barang.
    Route::get('/barang/{barang}/edit', [BarangController::class, 'edit'])
        ->name('barang.edit');

    // Menyimpan perubahan barang.
    Route::put('/barang/{barang}', [BarangController::class, 'update'])
        ->name('barang.update');

    // Menandai barang sudah terjual.
    Route::patch('/barang/{barang}/terjual', [BarangController::class, 'tandaiTerjual'])
        ->name('barang.terjual');

    // Menghapus barang.
    Route::delete('/barang/{barang}', [BarangController::class, 'destroy'])
        ->name('barang.destroy');

    // Detail Barang
    Route::get('/barang/{barang}', [BarangController::class, 'show'])
    ->name('barang.show');
});



// Route Halaman Filter
Route::get('/filter', function (Request $request) {
    $barangs = Barang::with('user')
        ->where('status', 'tersedia')
        ->latest()
        ->get();

    // Ambil ID barang yang sudah difavoritkan oleh pengguna login.
    $favoritIds = $request->user()
        ? $request->user()->barangFavorit()->pluck('barangs.id')->toArray()
        : [];

    return view('pages.filter', compact('barangs', 'favoritIds'));
})->name('filter');


// Route Halaman Profil
Route::get('/profil', function () {
    return view('pages.profil');
})->name('profil');


// Route Halaman Chat
Route::view('/chat', 'pages.chat')
    ->middleware('auth')
    ->name('chat.index');