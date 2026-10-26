<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    //Menampilkan halaman login
    public function showLogin() {
        return view('auth.login');
    }

    //Memproses login
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if(Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('beranda'));
        }

        return back()
        ->withErrors([
            'email' => 'Email atau password salah',
        ])
        ->onlyinput('email');
    }


    // Menampilkan halaman register 
    public function showRegister() {
        return View('auth.register');
    }

    // Memproses register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => [
                'required',
                'string',
                'max:25',
                'regex:/^[0-9+\-\s()]+$/',
                'unique:users,phone',
            ],
            'fullname' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
            ],
            'ktp_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',

            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',

            'fullname.required' => 'Nama lengkap wajib diisi.',

            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',

            'ktp_image.required' => 'Silakan upload foto KTP.',
            'ktp_image.image' => 'File KTP harus berupa gambar.',
            'ktp_image.mimes' => 'KTP harus berformat JPG, JPEG, PNG, atau WEBP.',
            'ktp_image.max' => 'Ukuran foto KTP maksimal 2 MB.',
        ]);

        // Simpan KTP di penyimpanan privat, bukan folder public.
        $ktpPath = $request->file('ktp_image')->store('ktp');

        try {
            $user = User::create([
                'name' => $validated['fullname'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'ktp_path' => $ktpPath,
            ]);
        } catch (\Throwable $exception) {
            Storage::delete($ktpPath);

            throw $exception;
        }

        // Auth::login($user);
        // $request->session()->regenerate();

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Silahkan login kembali menggunakan akun yg sudah dibuat');
    }


    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Kamu berhasil logout.');
    }
}
