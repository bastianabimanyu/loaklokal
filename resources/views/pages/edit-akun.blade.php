
@extends('layouts.app')

@section('title', 'Edit Akun - LoakLokal')

@push('styles')
    @vite(['resources/css/edit-akun.css'])
@endpush

@section('content')
<main class="edit-akun-page">
    <div class="edit-akun-container">

        <a href="{{ url('/profil') }}" class="edit-akun-back">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke Profil
        </a>

        <header class="edit-akun-heading">
            <span class="edit-akun-eyebrow">PENGATURAN AKUN</span>
            <h1>Edit Akun</h1>
            <p>Perbarui informasi pribadi akun LoakLokal kamu.</p>
        </header>

        @if (session('success'))
            <div class="edit-akun-alert success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="edit-akun-alert error">
                <strong>Periksa kembali data berikut:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('profil.update-akun') }}"
            method="POST"
            enctype="multipart/form-data"
            class="edit-akun-form"
        >
            @csrf
            @method('PUT')

            {{-- FOTO PROFIL --}}
            <section class="edit-akun-card">
                <div class="edit-akun-card-heading">
                    <i class="fa-solid fa-camera"></i>
                    <div>
                        <h2>Foto Profil</h2>
                        <p>Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    </div>
                </div>

                <div class="edit-akun-photo-area">
                    <div class="edit-akun-avatar">
                        <div
                            class="edit-akun-placeholder"
                            id="profilePhotoPlaceholder"
                            @if ($user->profile_photo_path) hidden @endif
                        >
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <img
                            src="{{ $user->profile_photo_path
                                ? asset('storage/' . $user->profile_photo_path)
                                : '' }}"
                            alt="Foto profil"
                            id="profilePhotoPreview"
                            @unless ($user->profile_photo_path) hidden @endunless
                        >
                    </div>

                    <div>
                        <label for="profile_photo" class="edit-akun-upload">
                            <i class="fa-solid fa-upload"></i>
                            Pilih Foto
                        </label>

                        <input
                            type="file"
                            id="profile_photo"
                            name="profile_photo"
                            accept=".jpg,.jpeg,.png,.webp"
                            hidden
                        >

                        <p class="edit-akun-hint">
                            Pilih foto yang jelas dan mudah dikenali.
                        </p>
                    </div>
                </div>
            </section>

            {{-- INFORMASI PRIBADI --}}
            <section class="edit-akun-card">
                <div class="edit-akun-card-heading">
                    <i class="fa-solid fa-user-pen"></i>
                    <div>
                        <h2>Informasi Pribadi</h2>
                        <p>Ubah informasi akun kamu.</p>
                    </div>
                </div>

                <div class="edit-akun-field">
                    <label for="name">Nama Lengkap</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="edit-akun-field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="edit-akun-field">
                    <label for="phone">Nomor Telepon</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        maxlength="20"
                        placeholder="Masukkan nomor telepon"
                    >
                </div>
            </section>

            {{-- UBAH PASSWORD --}}
            <section class="edit-akun-card">
                <div class="edit-akun-card-heading">
                    <i class="fa-solid fa-lock"></i>
                    <div>
                        <h2>Ubah Password</h2>
                        <p>
                            Kosongkan semua kolom password jika tidak ingin
                            mengubahnya.
                        </p>
                    </div>
                </div>

                <div class="edit-akun-field">
                    <label for="current_password">
                        Password Saat Ini
                    </label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        placeholder="Isi jika ingin mengganti password"
                    >
                </div>

                <div class="edit-akun-field">
                    <label for="password">Password Baru</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                    >
                </div>

                <div class="edit-akun-field">
                    <label for="password_confirmation">
                        Konfirmasi Password Baru
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                    >
                </div>
            </section>

            {{-- TOMBOL --}}
            <div class="edit-akun-actions">
                <a href="{{ url('/profil') }}" class="edit-akun-cancel">
                    Batal
                </a>

                <button type="submit" class="edit-akun-submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('profile_photo');
    const preview = document.getElementById('profilePhotoPreview');
    const placeholder = document.getElementById('profilePhotoPlaceholder');

    if (!input || !preview || !placeholder) {
        return;
    }

    input.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) return;

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {
            alert('Pilih foto berformat JPG, PNG, atau WEBP.');
            this.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2 MB.');
            this.value = '';
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
        placeholder.hidden = true;
    });
});
</script>
@endpush
