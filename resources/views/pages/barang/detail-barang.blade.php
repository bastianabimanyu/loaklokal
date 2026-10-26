
@extends('layouts.app')

@section('title', 'Detail Barang - LoakLokal')

@push('styles')
    @vite(['resources/css/detail-barang.css'])
@endpush

@section('content')
    <header class="header-detail">
        <div class="mainwrap-header">
            <div class="wrap-header">
                <a href="{{ route('beranda') }}" class="detail-back-button"
                    aria-label="Kembali ke Beranda">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>

                <a href="{{ route('beranda') }}" class="detail-brand">
                    <img
                        src="{{ asset('assets/images/LOGO.png') }}"
                        alt="Logo LoakLokal"
                        class="logoweb-detailbarang"
                    >
                    <h2>Loak Lokal</h2>
                </a>
            </div>
        </div>
    </header>

    <main class="detail-main">
        <div class="detail-page-container">
            @if (session('success'))
                <div class="detail-alert detail-alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif

            <article class="detail-card" id="detailContainer">

                {{-- Foto barang --}}
                <div class="detail-image">
                    <img
                        src="{{ asset('storage/' . $barang->foto) }}"
                        alt="{{ $barang->nama }}"
                    >

                    <span class="detail-category-badge">
                        {{ ucfirst($barang->kategori) }}
                    </span>
                </div>

                {{-- Informasi barang --}}
                <div class="detail-info">

                    <div class="detail-topline">
                        <span class="detail-condition">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ $barang->status === 'tersedia' ? 'Tersedia' : 'Sudah terjual' }}
                        </span>

                        <span class="detail-date">
                            <i class="fa-regular fa-clock"></i>
                            {{ $barang->created_at->format('d M Y') }}
                        </span>
                    </div>

                    <h1 class="detail-title">
                        {{ $barang->nama }}
                    </h1>

                    <div class="detail-price-loc">
                        <div class="detail-price">
                            @if ($barang->mode === 'Hibah Gratis')
                                <span class="price-tag">Gratis</span>
                            @elseif ($barang->mode === 'Barter/Tukar')
                                <span class="price-tag">Barter / Tukar</span>
                            @else
                                <span class="price-tag">
                                    Rp{{ number_format($barang->harga ?? 0, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>

                        <div class="detail-location">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>
                                {{ $barang->kecamatan }},
                                {{ $barang->kota_kabupaten }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    {{-- Jenis transaksi --}}
                    <div class="detail-transaction">
                        <i class="fa-solid fa-handshake"></i>
                        <div>
                            <span>Jenis transaksi</span>
                            <strong>{{ $barang->mode }}</strong>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <section class="detail-description-section">
                        <h2>
                            <i class="fa-solid fa-align-left"></i>
                            Deskripsi Barang
                        </h2>

                        <div class="detail-desc">{{ $barang->deskripsi }}</div>
                    </section>

                    <hr>

                    {{-- Profil penjual --}}
                    <section class="seller-section">
                        <h2>Informasi Penjual</h2>

                        <div class="seller-profile">
                            <div class="seller-avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div class="seller-profile-info">
                                <span>Diunggah oleh</span>
                                <h3 class="seller-name">
                                    {{ $barang->user->name ?? 'Pengguna LoakLokal' }}
                                </h3>
                                <p>
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{ $barang->kecamatan }},
                                    {{ $barang->kota_kabupaten }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Tombol aksi --}}
                    <div class="action-buttons">
                        @auth
                            @if ((int) auth()->id() === (int) $barang->user_id)
                                <a
                                    href="{{ route('barang.edit', $barang) }}"
                                    class="btn-action btn-edit"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Edit Barang
                                </a>
                            @elseif ($barang->status === 'tersedia')
                                <button type="button" class="btn-action btn-chat">
                                    Chat Penjual
                                </button>

                                <a href="{{ route('beranda') }}" class="btn-action btn-action-primary">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    Kembali ke Katalog
                                </a>
                            @else
                                <button type="button" class="btn-action btn-action-disabled" disabled>
                                    Barang Sudah Terjual
                                </button>
                            @endif
                        @else
                            @if ($barang->status === 'tersedia')
                                <a href="{{ route('login') }}" class="btn-action btn-action-primary">
                                    <i class="fa-solid fa-right-to-bracket"></i>
                                    Login untuk Melanjutkan
                                </a>
                            @else
                                <button type="button" class="btn-action btn-action-disabled" disabled>
                                    Barang Sudah Terjual
                                </button>
                            @endif
                        @endauth

                    </div>

                    @auth
                        @if ((int) auth()->id() !== (int) $barang->user_id && $barang->status === 'tersedia')
                            <p class="detail-cod">
                                <i class="fa-solid fa-circle-info"></i>
                                Tertarik dengan barang ini? Hubungi penjual untuk membahas kondisi
                                barang dan kesepakatan pengambilan.
                            </p>
                        @endif
                    @endauth

                </div>
            </article>

        </div>
    </main>
@endsection
