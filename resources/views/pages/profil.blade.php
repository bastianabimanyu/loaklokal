@extends('layouts.app')

@section('title', 'Profil Saya - LoakLokal')

@push('styles')
    @vite(['resources/css/profil.css'])
@endpush

@section('content')
    <header class="profil-header">
        <div class="profil-header-inner">
            <a href="{{ url('/beranda') }}" class="header-action" aria-label="Kembali">
                <i class="fa-solid fa-arrow-left"></i>
            </a>

            <h1>Profil Saya</h1>

            <a href="#" class="header-action" aria-label="Notifikasi">
                <i class="fa-solid fa-bell"></i>
            </a>
        </div>
    </header>

    <main class="profil-main">
        <section class="profile-card">
            <div class="profil-avatar">
                @if (auth()->user()->profile_photo_path)
                    <img
                        src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                        alt="Foto Profil"
                        class="profil-avatar-image"
                    >
                @else
                    <i class="fa-solid fa-user"></i>
                @endif
            </div>

            <div class="profile-info">
                <h2>{{ auth()->user()?->name ?? 'Pengguna LoakLokal' }}</h2>

                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    {{ auth()->user()?->kota_kabupaten ?? 'Lokasi belum diatur' }}
                </p>
            </div>

            <a href="{{ route('profil.edit-akun') }}" class="profil-edit-akun">
                <i class="fa-solid fa-user-pen"></i>
                Edit Akun
            </a>
        </section>

        <h2 class="profil-section-title">Pengaturan Akun</h2>

        <nav class="menu-group" aria-label="Menu profil">
            <a href="{{ route('profil.edit-akun') }}" class="menu-item">
                <div class="menu-content">
                    <span class="menu-icon">
                        <i class="fa-solid fa-user-pen"></i>
                    </span>
                    <span class="menu-text">
                        <strong>Edit Akun</strong>
                        <small>Ubah informasi profil kamu</small>
                    </span>
                </div>

                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </a>

            <a href="{{ url('/barang-favorit') }}" class="menu-item">
                <div class="menu-content">
                    <span class="menu-icon">
                        <i class="fa-solid fa-box-archive"></i>
                    </span>
                    <span class="menu-text">
                        <strong>Barang Favorit</strong>
                        <small>Lihat barang yang kamu simpan</small>
                    </span>
                </div>

                <i class="fa-solid fa-chevron-right menu-arrow"></i>
            </a>

            <form action="{{ url('/logout') }}" method="POST" class="logout-form">
                @csrf

                <button type="submit" class="menu-item logout-item">
                    <span class="menu-content">
                        <span class="menu-icon">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </span>
                        <span class="menu-text">
                            <strong>Keluar Akun</strong>
                            <small>Keluar dari akun LoakLokal</small>
                        </span>
                    </span>

                    <i class="fa-solid fa-chevron-right menu-arrow"></i>
                </button>
            </form>
        </nav>
    </main>
@endsection
