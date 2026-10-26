@extends('layouts.app')

@section('title', 'Beranda - LoakLokal')

@push('styles')
    @vite(['resources/css/beranda.css'])
@endpush

@push('scripts')
    @vite(['resources/js/beranda.js'])
@endpush

@section('content')
    <header class="header-beranda">
        <div class="container-header">

            <a href="{{ route('beranda') }}" class="header-left">
                <img src="{{ asset('assets/images/LOGO.png') }}" alt="Logo LoakLokal" class="logo">
                <h1>LOAKLOKAL</h1>
            </a>

            <div class="header-right">
                <button type="button" id="radiusLocationBtn" class="radius-location">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Semua Lokasi</span>
                </button>

                <a href="#" class="notification-link" title="Notifikasi" aria-label="Notifikasi">
                    <i class="fa-solid fa-bell bellicon" id="bellIcon"></i>
                    <span class="notification-badge"></span>
                </a>
            </div>

        </div>
    </header>

    <!-- Popup pencarian lokasi -->
    <div class="lokasi-overlay" id="lokasiOverlay" hidden>
        <section class="lokasi-popup" role="dialog" aria-modal="true"
            aria-labelledby="lokasiPopupTitle">

            <button type="button" class="lokasi-close" id="lokasiClose"
                aria-label="Tutup popup">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="lokasi-popup-icon">
                <i class="fa-solid fa-location-dot"></i>
            </div>

            <h2 id="lokasiPopupTitle">Pilih Kota/Kabupaten</h2>
            <p class="lokasi-popup-description">
                Cari lokasi untuk menemukan barang di kota atau kabupaten pilihanmu.
            </p>

            <label for="kotaSearchInput" class="lokasi-label">
                Nama kota/kabupaten
            </label>

            <div class="lokasi-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="search"
                    id="kotaSearchInput"
                    placeholder="Contoh: Kediri, Surabaya..."
                    autocomplete="off"
                >
            </div>

            <p class="lokasi-error" id="lokasiError" hidden></p>

            
            <div class="lokasi-hasil" id="lokasiHasil" hidden></div>

            <p class="lokasi-error" id="lokasiError" hidden></p>

            <button type="button" class="lokasi-cari-btn" id="lokasiCariBtn">
                <i class="fa-solid fa-magnifying-glass"></i>
                Cari Barang
            </button>

            <button type="button" class="lokasi-reset" id="lokasiReset">
                Tampilkan Semua Lokasi
            </button>
        </section>
    </div>

    <main class="main-content">
        <!-- Pencarian -->
        <div class="header-center">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="search" id="searchInput" placeholder="Cari nama barang bekas, kategori, atau lokasi..."
                aria-label="Cari barang">
        </div>

        
        <!-- Kategori LoakLokal -->
        <section class="kategori-section">
            <div class="kategori-header">
                <h2>Kategori Barang</h2>
                <span>Temukan barang pilihanmu</span>
            </div>

            <div class="kategori-list">
                <button class="btn-kategori active" data-category="semua">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-border-all"></i>
                    </span>
                    <span>Semua</span>
                </button>

                <button class="btn-kategori" data-category="elektronik">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </span>
                    <span>Elektronik</span>
                </button>

                <button class="btn-kategori" data-category="pakaian">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-shirt"></i>
                    </span>
                    <span>Pakaian</span>
                </button>

                <button class="btn-kategori" data-category="perabotan">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-couch"></i>
                    </span>
                    <span>Perabotan</span>
                </button>

                <button class="btn-kategori" data-category="buku">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-book-open"></i>
                    </span>
                    <span>Buku</span>
                </button>

                <button class="btn-kategori" data-category="olahraga">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-bicycle"></i>
                    </span>
                    <span>Olahraga</span>
                </button>

                <button class="btn-kategori" data-category="bayi-anak">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-child"></i>
                    </span>
                    <span>Bayi & Anak</span>
                </button>

                <button class="btn-kategori" data-category="rumah-tangga">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-kitchen-set"></i>
                    </span>
                    <span>Rumah Tangga</span>
                </button>

                <button class="btn-kategori" data-category="otomotif">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-motorcycle"></i>
                    </span>
                    <span>Otomotif</span>
                </button>

                <button class="btn-kategori" data-category="hibah">
                    <span class="kategori-icon">
                        <i class="fa-solid fa-gift"></i>
                    </span>
                    <span>Hibah</span>
                </button>
            </div>
        </section>


        <!-- Banner Promosi Slider -->
        <section class="promo-section">
            <h2 class="promo-title">Iklan & Promosi Lokal</h2>

            <div class="promo-slider" id="promoSlider">

                <!-- Daftar gambar -->
                <div class="promo-track" id="promoTrack">

                    <!-- Gambar pertama -->
                    <div class="promo-slide">
                        <img src="{{ asset('assets/images/banner-promosi1.png') }}" alt="Promosi LoakLokal pertama">
                    </div>

                    <!-- Gambar kedua: ganti nama file sesuai gambar Anda -->
                    <div class="promo-slide">
                        <img src="{{ asset('assets/images/banner-promosi2.png') }}" alt="Promosi LoakLokal kedua">
                    </div>

                </div>

                <!-- Tombol kembali -->
                <button type="button" class="promo-arrow promo-prev" id="promoPrev" aria-label="Banner sebelumnya">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <!-- Tombol lanjut -->
                <button type="button" class="promo-arrow promo-next" id="promoNext" aria-label="Banner berikutnya">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <!-- Indikator slide -->
                <div class="promo-dots">
                    <button class="promo-dot active" data-slide="0" aria-label="Banner pertama"></button>
                    <button class="promo-dot" data-slide="1" aria-label="Banner kedua"></button>
                </div>

            </div>
        </section>


        <!-- Judul katalog -->
        <div class="catalog-header">
            <h2 class="section-title">Barang Dijual</h2>
            <a href="#" class="see-all">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <!-- Katalog -->  
        <div class="katalog-grid" id="katalogContainer">
            @forelse ($barangs as $barang)
                <article
                    class="barang-card"
                    data-category="{{ $barang->kategori }}"
                    data-nama="{{ strtolower($barang->nama) }}"
                    data-lokasi="{{ strtolower($barang->kota_kabupaten . ' ' . $barang->kecamatan) }}"
                    data-kota="{{ $barang->kota_kabupaten }}"
                    data-kecamatan="{{ $barang->kecamatan }}"
                >
                    <div class="barang-card-image">
                        <img
                            src="{{ asset('storage/' . $barang->foto) }}"
                            alt="{{ $barang->nama }}"
                            loading="lazy"
                        >
                    </div>

                    <div class="barang-card-content">
                        <span class="barang-kategori">
                            {{ ucfirst($barang->kategori) }}
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
                            {{ $barang->kecamatan }},
                            {{ $barang->kota_kabupaten }}
                        </p>

                        <p class="barang-deskripsi">
                            {{ $barang->deskripsi }}
                        </p>

                        <p class="barang-penjual">
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
                        @endauth

                        @auth
                            @if ((int) auth()->id() === (int) $barang->user_id)
                                <div class="barang-actions">
                                    <a href="{{ route('barang.edit', $barang) }}">
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

                                        <button type="submit">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>
                </article>
            @empty
                <p class="katalog-kosong">
                    Belum ada barang yang tersedia. Jadilah orang pertama yang mengunggah barang!
                </p>
            @endforelse
        </div>
    </main>

    <!-- Tombol upload melayang -->
    <a href="{{ url('/barang/upload') }}" class="button-upload">
        <i class="fa-solid fa-plus"></i>
        <span>Upload Barang</span>
    </a>

@endsection
