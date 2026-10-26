<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        return view('pages.edit-akun', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Validasi informasi akun dan foto profil.
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'current_password' => [
                'nullable',
                'required_with:password',
                'current_password',
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',

            'profile_photo.image' => 'File harus berupa gambar.',
            'profile_photo.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'profile_photo.max' => 'Ukuran foto maksimal 2 MB.',

            'current_password.required_with' => 'Password saat ini wajib diisi jika ingin mengganti password.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',

            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Simpan foto lama untuk dihapus setelah update berhasil.
        $oldPhoto = $user->profile_photo_path;
        $newPhoto = null;

        // Hash password baru hanya jika pengguna mengisinya.
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Jangan menyimpan field verifikasi atau file upload mentah.
        unset($data['current_password'], $data['profile_photo']);

        // Simpan foto profil baru jika ada upload.
        if ($request->hasFile('profile_photo')) {
            $newPhoto = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            $data['profile_photo_path'] = $newPhoto;
        }

        try {
            $user->update($data);
        } catch (\Throwable $e) {
            // Hapus file baru jika proses update database gagal.
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $e;
        }

        // Hapus foto lama hanya setelah update berhasil.
        if ($newPhoto && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('profil.edit-akun')
            ->with('success', 'Akun berhasil diperbarui.');
    }
}