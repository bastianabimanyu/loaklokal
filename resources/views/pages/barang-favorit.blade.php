
@extends('layouts.app')

@section('title', 'Barang Favorit - LoakLokal')

@push('styles')
    @vite(['resources/css/barang-favorit.css'])
@endpush

@section('content')
<main class="favorit-page">

    {{-- INTRO / HERO --}}
    <section class="favorit-hero">
        <div class="favorit-hero-copy">
            <span class="favorit-eyebrow">
                <i class="fa-solid fa-bookmark"></i>
                KOLEKSI PRIBADIMU
            </span>

            <h1>Barang yang<br><span>kamu sukai.</span></h1>

            <p>
                Semua barang menarik yang kamu simpan ada di sini.
                Jelajahi kembali, bandingkan, dan temukan barang yang
                paling cocok untukmu.
            </p>

            <a href="{{ route('filter') }}" class="favorit-explore-btn">
                <i class="fa-solid fa-compass"></i>
                Jelajahi barang
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="favorit-hero-art" aria-hidden="true">
            <div class="favorit-art-circle favorit-art-circle-one"></div>
            <div class="favorit-art-circle favorit-art-circle-two"></div>

            <div class="favorit-art-card favorit-art-card-back">
                <i class="fa-regular fa-image"></i>
            </div>

            <div class="favorit-art-card favorit-art-card-front">
                <i class="fa-solid fa-heart"></i>
                <span>Disimpan untukmu</span>
            </div>

            <span class="favorit-art-spark favorit-spark-one">✦</span>
            <span class="favorit-art-spark favorit-spark-two">✧</span>
        </div>
    </section>

    {{-- RINGKASAN KOLEKSI --}}
    <section class="favorit-overview">
        <div class="favorit-overview-text">
            <span class="favorit-overline">RUANG PILIHANMU</span>
            <h2>Koleksi favorit</h2>
            <p>Kumpulan barang yang ingin kamu lihat kembali.</p>
        </div>

        <div class="favorit-count-card">
            <span class="favorit-count-icon">
                <i class="fa-solid fa-heart"></i>
            </span>
            <div>
                <strong>{{ $barangs->total() }}</strong>
                <span>Barang tersimpan</span>
            </div>
        </div>
    </section>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="favorit-notice favorit-notice-success" role="status">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="favorit-notice favorit-notice-error" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- DAFTAR FAVORIT --}}
    @if ($barangs->count() > 0)
        <section class="favorit-collection">
            <div class="favorit-collection-heading">
                <div>
                    <h2>Yang menarik perhatianmu</h2>
                    <p>Pilih barang untuk melihat informasi lebih lengkap.</p>
                </div>

                <span class="favorit-collection-total">
                    {{ $barangs->firstItem() }}–{{ $barangs->lastItem() }}
                    dari {{ $barangs->total() }}
                </span>
            </div>

            <div class="favorit-grid">
                @foreach ($barangs as $barang)
                    <article class="favorit-item">

                        {{-- FOTO BARANG --}}
                        <div class="favorit-item-visual">
                            <a
                                href="{{ route('barang.show', $barang) }}"
                                class="favorit-item-image-link"
                                aria-label="Lihat {{ $barang->nama }}"
                            >
                                @if ($barang->foto)
                                    <img
                                        src="{{ asset('storage/' . $barang->foto) }}"
                                        alt="{{ $barang->nama }}"
                                        class="favorit-item-image"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="favorit-item-placeholder">
                                        <i class="fa-regular fa-image"></i>
                                        <span>Foto belum tersedia</span>
                                    </div>
                                @endif
                            </a>

                            <span class="favorit-saved-label">
                                <i class="fa-solid fa-bookmark"></i>
                                Tersimpan
                            </span>

                            {{-- HAPUS DARI FAVORIT --}}
                            <form
                                action="{{ route('barang.favorit.toggle', $barang) }}"
                                method="POST"
                                class="favorit-remove-form"
                                onsubmit="return confirm('Hapus barang ini dari daftar favorit?')"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="favorit-remove-btn"
                                    aria-label="Hapus {{ $barang->nama }} dari favorit"
                                    title="Hapus dari favorit"
                                >
                                    <i class="fa-solid fa-heart"></i>
                                </button>
                            </form>
                        </div>

                        {{-- DETAIL BARANG --}}
                        <div class="favorit-item-body">
                            <div class="favorit-item-meta">
                                <span class="favorit-category">
                                    {{ ucfirst($barang->kategori ?? 'Lainnya') }}
                                </span>

                                @if ($barang->mode === 'Hibah Gratis')
                                    <span class="favorit-mode favorit-mode-free">
                                        Gratis
                                    </span>
                                @elseif ($barang->mode === 'Barter/Tukar')
                                    <span class="favorit-mode favorit-mode-barter">
                                        Barter
                                    </span>
                                @endif
                            </div>

                            <a
                                href="{{ route('barang.show', $barang) }}"
                                class="favorit-item-title-link"
                            >
                                <h3>{{ $barang->nama }}</h3>
                            </a>

                            <p class="favorit-item-price">
                                @if ($barang->mode === 'Hibah Gratis')
                                    Gratis
                                @elseif ($barang->mode === 'Barter/Tukar')
                                    Barter / Tukar
                                @else
                                    Rp{{ number_format($barang->harga ?? 0, 0, ',', '.') }}
                                @endif
                            </p>

                            <div class="favorit-item-location">
                                <i class="fa-solid fa-location-dot"></i>
                                <span>
                                    {{ $barang->kecamatan ?? 'Kecamatan tidak tersedia' }},
                                    {{ $barang->kota_kabupaten ?? 'Lokasi tidak tersedia' }}
                                </span>
                            </div>

                            <div class="favorit-item-footer">
                                <span class="favorit-seller">
                                    <i class="fa-regular fa-user"></i>
                                    {{ $barang->user->name ?? 'Pengguna LoakLokal' }}
                                </span>

                                <a
                                    href="{{ route('barang.show', $barang) }}"
                                    class="favorit-detail-btn"
                                    aria-label="Lihat detail {{ $barang->nama }}"
                                >
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            @if ($barangs->hasPages())
                <div class="favorit-pagination">
                    {{ $barangs->links() }}
                </div>
            @endif
        </section>
    @else
        {{-- TAMPILAN JIKA BELUM ADA FAVORIT --}}
        <section class="favorit-empty-state">
            <div class="favorit-empty-illustration" aria-hidden="true">
                <span class="favorit-empty-orbit"></span>
                <div class="favorit-empty-heart">
                    <i class="fa-regular fa-heart"></i>
                </div>
                <span class="favorit-empty-star favorit-empty-star-one">✦</span>
                <span class="favorit-empty-star favorit-empty-star-two">✧</span>
            </div>

            <span class="favorit-empty-kicker">KOLEKSIMU MENUNGGU</span>
            <h2>Belum ada barang favorit</h2>
            <p>
                Temukan barang yang kamu suka, lalu tekan ikon hati.
                Barang tersebut akan muncul di halaman ini.
            </p>

            <a href="{{ route('filter') }}" class="favorit-empty-btn">
                <i class="fa-solid fa-magnifying-glass"></i>
                Temukan barang sekarang
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </section>
    @endif

    <footer class="favorit-page-footer">
        <i class="fa-regular fa-heart"></i>
        <span>Simpan barang yang kamu suka, temukan kembali kapan saja.</span>
    </footer>

</main>
@endsection
