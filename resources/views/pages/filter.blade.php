
@extends('layouts.app')

@section('title', 'Jelajahi Barang - LoakLokal')

@push('styles')
    @vite([
        'resources/css/beranda.css',
        'resources/css/filter.css'
    ])
@endpush

@push('scripts')
    @vite(['resources/js/filter.js'])
@endpush

@section('content')

<header class="header-beranda">
    <div class="container-header">
        <a href="{{ route('beranda') }}" class="header-left">
            <img
                src="{{ asset('assets/images/LOGO.png') }}"
                alt="Logo LoakLokal"
                class="logo"
            >
            <h1>LOAKLOKAL</h1>
        </a>

        <div class="header-right">
            <button
                type="button"
                id="radiusLocationBtn"
                class="radius-location"
            >
                <i class="fa-solid fa-location-dot"></i>
                <span>Lokasi</span>
            </button>

            <a
                href="#"
                class="notification-link"
                aria-label="Notifikasi"
                title="Notifikasi"
            >
                <i class="fa-solid fa-bell bellicon" id="bellIcon"></i>
                <span class="notification-badge"></span>
            </a>
        </div>
    </div>
</header>

<main class="jelajah-main">

    {{-- BANNER UTAMA --}}
    <section class="jelajah-hero">
        <div class="jelajah-hero-content">
            <span class="jelajah-eyebrow">
                <i class="fa-solid fa-compass"></i>
                JELAJAHI LOAKLOKAL
            </span>

            <h2>Temukan Barang Favoritmu!</h2>

            <p>
                Cari barang bekas berkualitas, tukar barang, atau temukan
                barang gratis dari orang-orang di sekitarmu.
            </p>

            <button
                type="button"
                class="hero-location-button"
                onclick="document.getElementById('radiusLocationBtn')?.click()"
            >
                <i class="fa-solid fa-location-dot"></i>
                Cari berdasarkan lokasi
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>

        <div class="jelajah-hero-decoration" aria-hidden="true">
            <div class="hero-circle hero-circle-one"></div>
            <div class="hero-circle hero-circle-two"></div>

            <div class="hero-shopping-icon">
                <i class="fa-solid fa-bag-shopping"></i>
                <span><i class="fa-solid fa-heart"></i></span>
            </div>

            <div class="hero-floating-tag">
                <i class="fa-solid fa-recycle"></i>
                Barang punya cerita
            </div>
        </div>
    </section>

    {{-- PENCARIAN --}}
    <section class="jelajah-search-section">
        <div class="jelajah-search-heading">
            <div>
                <h2>Mau cari apa hari ini?</h2>
                <p>Temukan barang yang kamu butuhkan dengan mudah.</p>
            </div>

            <span class="search-heading-icon" aria-hidden="true">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
        </div>

        <div class="jelajah-search-box">
            <i class="fa-solid fa-magnifying-glass search-main-icon"></i>

            <input
                type="search"
                id="filterSearchInput"
                placeholder="Cari nama barang, kategori, atau lokasi..."
                aria-label="Cari barang"
            >

            <button
                type="button"
                id="filterClearButton"
                class="search-clear-button"
                aria-label="Hapus pencarian"
                hidden
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="jelajah-search-hint">
            <i class="fa-regular fa-lightbulb"></i>
            <span>Contoh: buku, pakaian, elektronik, atau Kediri</span>
        </div>
    </section>

    {{-- KATALOG BARANG --}}
    <section class="jelajah-catalog-section">
        <div class="jelajah-catalog-heading">
            <div>
                <span class="section-kicker">TEMUKAN HARTA KARUNMU</span>
                <h2>Barang untuk Kamu</h2>
                <p>Temukan barang menarik dari komunitas lokal.</p>
            </div>

            <span class="catalog-decoration" aria-hidden="true">
                <i class="fa-solid fa-box-open"></i>
            </span>
        </div>

        <div class="jelajah-active-filters">
            <span class="active-filter-label">
                <i class="fa-solid fa-sliders"></i>
                Filter:
            </span>

            <span class="active-filter-chip" id="activeCategoryLabel">
                Semua kategori
            </span>

            <span class="active-filter-chip" id="activeLocationLabel">
                Semua lokasi
            </span>
        </div>

        <div class="katalog-grid" id="katalogContainer">
            @forelse ($barangs as $barang)

                @php
                    $favoritIds = $favoritIds ?? [];

                    $isFavorit = in_array(
                        $barang->id,
                        $favoritIds
                    );
                @endphp

                <article
                    class="barang-card"
                    data-nama="{{ $barang->nama }}"
                    data-kategori="{{ $barang->kategori }}"
                    data-kecamatan="{{ $barang->kecamatan }}"
                    data-kota="{{ $barang->kota_kabupaten }}"
                >
                    {{-- FOTO DAN TOMBOL FAVORIT --}}
                    <div class="barang-card-image-wrapper">
                        @if ($barang->foto)
                            <img
                                src="{{ asset('storage/' . $barang->foto) }}"
                                alt="{{ $barang->nama }}"
                                class="barang-card-image"
                                loading="lazy"
                            >
                        @else
                            <div class="barang-image-placeholder">
                                <i class="fa-solid fa-image"></i>
                                <span>Foto belum tersedia</span>
                            </div>
                        @endif

                        @auth
                            @if ((int) auth()->id() !== (int) $barang->user_id)
                                <form
                                    action="{{ route('barang.favorit.toggle', $barang) }}"
                                    method="POST"
                                    class="favorit-toggle-form"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="favorit-heart {{ $isFavorit ? 'is-favorited' : '' }}"
                                        aria-label="{{ $isFavorit ? 'Hapus dari favorit' : 'Tambahkan ke favorit' }}"
                                        title="{{ $isFavorit ? 'Hapus dari favorit' : 'Tambahkan ke favorit' }}"
                                    >
                                        <i class="{{ $isFavorit ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>

                    {{-- INFORMASI BARANG --}}
                    <div class="barang-card-content">
                        <span class="barang-kategori">
                            {{ ucfirst($barang->kategori ?? 'Lainnya') }}
                        </span>

                        <h3>{{ $barang->nama }}</h3>

                        <p class="barang-harga">
                            @if ($barang->mode === 'Hibah Gratis')
                                Gratis
                            @elseif ($barang->mode === 'Barter/Tukar')
                                Barter / Tukar
                            @else
                                Rp{{ number_format($barang->harga ?? 0, 0, ',', '.') }}
                            @endif
                        </p>

                        <p class="barang-lokasi">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>
                                {{ $barang->kecamatan ?? 'Kecamatan tidak tersedia' }},
                                {{ $barang->kota_kabupaten ?? 'Lokasi tidak tersedia' }}
                            </span>
                        </p>

                        <p class="barang-deskripsi">
                            {{ $barang->deskripsi ?? 'Tidak ada deskripsi.' }}
                        </p>

                        <p class="barang-penjual">
                            <i class="fa-regular fa-user"></i>
                            Penjual:
                            {{ $barang->user->name ?? 'Pengguna LoakLokal' }}
                        </p>

                        @auth
                            @if ((int) auth()->id() !== (int) $barang->user_id)
                                <a
                                    href="{{ route('barang.show', $barang) }}"
                                    class="btn-selengkapnya"
                                >
                                    Selengkapnya
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endif

                            @if ((int) auth()->id() === (int) $barang->user_id)
                                <div class="barang-actions">
                                    <a href="{{ route('barang.edit', $barang) }}">
                                        <i class="fa-solid fa-pen"></i>
                                        Edit
                                    </a>

                                    @if ($barang->status === 'tersedia')
                                        <form
                                            action="{{ route('barang.terjual', $barang) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit">
                                                <i class="fa-solid fa-check"></i>
                                                Tandai Terjual
                                            </button>
                                        </form>
                                    @endif

                                    <form
                                        action="{{ route('barang.destroy', $barang) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus barang ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-hapus">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="btn-selengkapnya"
                            >
                                Login untuk melihat
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        @endauth
                    </div>
                </article>

            @empty
                <div class="katalog-kosong">
                    <i class="fa-solid fa-box-open"></i>
                    <h3>Belum ada barang tersedia</h3>
                    <p>Silakan cek kembali nanti.</p>
                </div>
            @endforelse
        </div>

        {{-- PESAN JIKA PENCARIAN TIDAK MENEMUKAN HASIL --}}
        <div class="jelajah-no-results" id="filterNoResults" hidden>
            <div class="empty-state-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <h3>Barang tidak ditemukan</h3>
            <p>Coba gunakan kata kunci yang berbeda.</p>
            <button type="button" id="filterResetSearch">
                Tampilkan semua barang
            </button>
        </div>

        @if (method_exists($barangs, 'links'))
            <div class="jelajah-pagination">
                {{ $barangs->links() }}
            </div>
        @endif
    </section>
</main>

{{-- POPUP LOKASI --}}
<div id="lokasiOverlay" class="lokasi-overlay" hidden>
    <div
        class="lokasi-popup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lokasiTitle"
    >
        <button
            type="button"
            id="lokasiClose"
            class="lokasi-close"
            aria-label="Tutup"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="lokasi-icon">
            <i class="fa-solid fa-location-dot"></i>
        </div>

        <h2 id="lokasiTitle">Pilih Kota/Kabupaten</h2>

        <p class="lokasi-description">
            Temukan barang dari kota atau kabupaten pilihanmu.
        </p>

        <label for="kotaSearchInput">Nama kota/kabupaten</label>

        <div class="lokasi-search">
            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="search"
                id="kotaSearchInput"
                placeholder="Contoh: Kediri, Surabaya..."
            >
        </div>

        <p id="lokasiError" class="lokasi-error" hidden></p>

        <button type="button" id="lokasiCariBtn" class="lokasi-cari">
            Cari Barang
        </button>

        <button type="button" id="lokasiReset" class="lokasi-reset">
            Tampilkan Semua Lokasi
        </button>
    </div>
</div>

@endsection
