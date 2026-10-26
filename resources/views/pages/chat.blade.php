
@extends('layouts.app')

@section('title', 'Chat - LoakLokal')

@push('styles')
    @vite(['resources/css/chat.css'])
@endpush

@section('content')

{{-- HEADER KHUSUS HALAMAN CHAT --}}
<header class="chat-top-header">
    <a href="{{ route('beranda') }}" class="chat-top-brand">
        <img
            src="{{ asset('assets/images/LOGO.png') }}"
            alt="Logo LoakLokal"
            class="chat-top-logo"
        >

        <span>LOAKLOKAL</span>
    </a>

    <a
        href="#"
        class="chat-top-notification"
        aria-label="Notifikasi"
        title="Notifikasi"
    >
        <i class="fa-solid fa-bell"></i>
    </a>
</header>

{{-- KONTEN HALAMAN CHAT --}}
<main class="chat-page">
    <div class="chat-container">

        {{-- JUDUL --}}
        <header class="chat-heading">
            <span class="chat-heading-label">PESAN PRIBADI</span>

            <h1>Daftar Percakapan</h1>

            <p>
                Terhubung dan berkomunikasi dengan komunitas LoakLokal.
            </p>
        </header>

        {{-- PENCARIAN CHAT --}}
        <div class="chat-search">
            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="search"
                id="chatSearchInput"
                placeholder="Cari pesan atau nama..."
                aria-label="Cari pesan atau nama"
                autocomplete="off"
            >

            <button
                type="button"
                id="chatSearchClear"
                aria-label="Hapus pencarian"
                hidden
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- DAFTAR PERCAKAPAN --}}
        <section class="chat-list" id="chatList">

            {{-- PERCAKAPAN ALIF --}}
            <a
                href="#"
                class="chat-item"
                data-nama="Alif"
                data-peran="Pemilik Barang"
                data-pesan="Halo kak, barang masih ada? Bisakah saya melihatnya?"
            >
                <div class="chat-avatar chat-avatar-blue">A</div>

                <div class="chat-info">
                    <h2>
                        Alif <span>(Pemilik Barang)</span>
                    </h2>

                    <p class="chat-last-message">
                        Halo kak, barang masih ada? Bisakah saya melihatnya?
                    </p>
                </div>

                <div class="chat-meta">
                    <time>16:35</time>
                    <span class="chat-unread">1</span>
                </div>
            </a>

            {{-- PERCAKAPAN RINA --}}
            <a
                href="#"
                class="chat-item"
                data-nama="Rina"
                data-peran="Pembeli"
                data-pesan="Oke, nanti saya siapkan tiketnya."
            >
                <div class="chat-avatar chat-avatar-orange">R</div>

                <div class="chat-info">
                    <h2>
                        Rina <span>(Pembeli)</span>
                    </h2>

                    <p class="chat-last-message">
                        <strong>Anda:</strong>
                        Oke, nanti saya siapkan tiketnya.
                    </p>
                </div>

                <div class="chat-meta">
                    <time>Kemarin</time>
                </div>
            </a>

            {{-- PERCAKAPAN DIMAS --}}
            <a
                href="#"
                class="chat-item"
                data-nama="Dimas"
                data-peran="Pemilik Barang"
                data-pesan="Terima kasih banyak kak barangnya sudah diterima."
            >
                <div class="chat-avatar chat-avatar-purple">D</div>

                <div class="chat-info">
                    <h2>
                        Dimas <span>(Pemilik Barang)</span>
                    </h2>

                    <p class="chat-last-message">
                        Terima kasih banyak kak barangnya sudah diterima.
                    </p>
                </div>

                <div class="chat-meta">
                    <time>22 Okt</time>
                </div>
            </a>

            {{-- JIKA CHAT TIDAK DITEMUKAN --}}
            <div class="chat-empty" id="chatEmpty" hidden>
                <div class="chat-empty-icon">
                    <i class="fa-regular fa-comments"></i>
                </div>

                <h3>Percakapan tidak ditemukan</h3>

                <p>
                    Coba gunakan nama atau kata kunci lainnya.
                </p>
            </div>

        </section>

    </div>
</main>

@endsection

@push('scripts')
    @vite(['resources/js/chat.js'])
@endpush
