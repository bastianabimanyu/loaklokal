
document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // 1. SLIDER PROMOSI
    // =====================================================

    const slider = document.getElementById('promoSlider');

    if (slider) {
        const track = document.getElementById('promoTrack');
        const slides = slider.querySelectorAll('.promo-slide');
        const prevButton = document.getElementById('promoPrev');
        const nextButton = document.getElementById('promoNext');
        const dots = slider.querySelectorAll('.promo-dot');

        if (track && prevButton && nextButton && slides.length > 0) {
            let currentIndex = 0;

            function showSlide(index) {
                currentIndex = (index + slides.length) % slides.length;

                track.style.transform =
                    `translateX(-${currentIndex * 100}%)`;

                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === currentIndex);
                });
            }

            nextButton.addEventListener('click', function () {
                showSlide(currentIndex + 1);
            });

            prevButton.addEventListener('click', function () {
                showSlide(currentIndex - 1);
            });

            dots.forEach(dot => {
                dot.addEventListener('click', function () {
                    showSlide(Number(dot.dataset.slide));
                });
            });

            showSlide(0);
        }
    }


    // =====================================================
    // 2. ELEMEN PENCARIAN, KATEGORI, DAN KATALOG
    // =====================================================

    const searchInput = document.getElementById('searchInput');
    const katalogContainer = document.getElementById('katalogContainer');
    const tombolKategori = document.querySelectorAll('.btn-kategori');

    let kategoriAktif = 'semua';
    let kotaTerpilih = '';

    function normalisasi(teks) {
        return (teks || '')
            .toString()
            .toLocaleLowerCase('id-ID')
            .trim();
    }

    function ambilSemuaKartu() {
        if (!katalogContainer) {
            return [];
        }

        // Termasuk card yang sedang tersembunyi.
        return Array.from(
            katalogContainer.querySelectorAll('.barang-card')
        );
    }


    // =====================================================
    // 3. FILTER GABUNGAN: NAMA, KATEGORI, LOKASI
    // =====================================================

    function filterBarang() {
        if (!katalogContainer) {
            return;
        }

        const kataKunci = normalisasi(searchInput?.value || '');
        const kartuBarang = ambilSemuaKartu();

        let jumlahDitemukan = 0;

        kartuBarang.forEach(function (kartu) {
            const nama = normalisasi(
                kartu.dataset.nama ||
                kartu.querySelector('h3')?.textContent
            );

            const kategori = normalisasi(
                kartu.dataset.category ||
                kartu.querySelector('.barang-kategori')?.textContent
            );

            const kota = normalisasi(
                kartu.dataset.kota || ''
            );

            const kecamatan = normalisasi(
                kartu.dataset.kecamatan || ''
            );

            const lokasiTampilan = normalisasi(
                kartu.dataset.lokasi ||
                kartu.querySelector('.barang-lokasi')?.textContent
            );

            // Pencarian bebas berdasarkan nama, kategori,
            // kota/kabupaten, kecamatan, atau teks lokasi.
            const cocokPencarian =
                !kataKunci ||
                nama.includes(kataKunci) ||
                kategori.includes(kataKunci) ||
                kota.includes(kataKunci) ||
                kecamatan.includes(kataKunci) ||
                lokasiTampilan.includes(kataKunci);

            // Filter kategori.
            const cocokKategori =
                kategoriAktif === 'semua' ||
                kategori === kategoriAktif;

            // Filter kota/kabupaten dari popup lokasi.
            const cocokLokasi =
                !kotaTerpilih ||
                kota === normalisasi(kotaTerpilih);

            const tampilkan =
                cocokPencarian &&
                cocokKategori &&
                cocokLokasi;

            kartu.hidden = !tampilkan;

            if (tampilkan) {
                jumlahDitemukan++;
            }
        });

        // Pesan ketika tidak ada barang yang cocok.
        let pesanKosong = katalogContainer.querySelector(
            '.hasil-search-kosong'
        );

        if (jumlahDitemukan === 0) {
            if (!pesanKosong) {
                pesanKosong = document.createElement('p');
                pesanKosong.className =
                    'katalog-kosong hasil-search-kosong';

                katalogContainer.appendChild(pesanKosong);
            }

            if (kotaTerpilih) {
                pesanKosong.textContent =
                    `Tidak ada barang dari ${kotaTerpilih} yang cocok dengan pencarianmu.`;
            } else {
                pesanKosong.textContent =
                    'Barang tidak ditemukan. Coba nama, kategori, atau lokasi lain.';
            }

            pesanKosong.hidden = false;
        } else if (pesanKosong) {
            pesanKosong.hidden = true;
        }
    }


    // Input pencarian bebas.
    if (searchInput) {
        searchInput.addEventListener('input', filterBarang);
    }


    // Klik kategori.
    tombolKategori.forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            tombolKategori.forEach(function (item) {
                item.classList.remove('active');
            });

            tombol.classList.add('active');

            kategoriAktif = normalisasi(
                tombol.dataset.category || 'semua'
            );

            filterBarang();
        });
    });


    // =====================================================
    // 4. POPUP PENCARIAN KOTA/KABUPATEN
    // =====================================================

    const tombolLokasi = document.getElementById('radiusLocationBtn');
    const overlay = document.getElementById('lokasiOverlay');
    const tombolTutup = document.getElementById('lokasiClose');
    const inputKota = document.getElementById('kotaSearchInput');
    const tombolCari = document.getElementById('lokasiCariBtn');
    const tombolReset = document.getElementById('lokasiReset');
    const pesanError = document.getElementById('lokasiError');

    function tampilkanError(pesan) {
        if (!pesanError) {
            alert(pesan);
            return;
        }

        pesanError.textContent = pesan;
        pesanError.hidden = false;
    }

    function sembunyikanError() {
        if (pesanError) {
            pesanError.textContent = '';
            pesanError.hidden = true;
        }
    }

    function bukaPopupLokasi() {
        if (!overlay || !inputKota) {
            return;
        }

        inputKota.value = '';
        sembunyikanError();
        overlay.hidden = false;
        inputKota.focus();
    }

    function tutupPopupLokasi() {
        if (overlay) {
            overlay.hidden = true;
        }
    }

    function cariBarangBerdasarkanKota() {
        if (!inputKota) {
            return;
        }

        const kataKunci = inputKota.value.trim();

        sembunyikanError();

        if (!kataKunci) {
            tampilkanError(
                'Masukkan nama kota/kabupaten terlebih dahulu.'
            );
            inputKota.focus();
            return;
        }

        // Ambil semua card, termasuk yang disembunyikan filter.
        const kartu = ambilSemuaKartu();

        const daftarKota = [
            ...new Set(
                kartu
                    .map(card => (card.dataset.kota || '').trim())
                    .filter(Boolean)
            )
        ];

        const daftarKecamatan = [
            ...new Set(
                kartu
                    .map(card => (card.dataset.kecamatan || '').trim())
                    .filter(Boolean)
            )
        ];

        // Cari kecocokan nama kota/kabupaten secara utuh.
        const kotaCocok = daftarKota.find(
            kota => normalisasi(kota) === normalisasi(kataKunci)
        );

        // Jika nama yang diketik adalah kecamatan, tolak.
        const kecamatanCocok = daftarKecamatan.some(
            kecamatan =>
                normalisasi(kecamatan) === normalisasi(kataKunci)
        );

        if (!kotaCocok && kecamatanCocok) {
            alert(
                'Pencarian lokasi hanya bisa berdasarkan kota/kabupaten, bukan kecamatan.'
            );
            return;
        }

        if (!kotaCocok) {
            tampilkanError(
                'Kota/kabupaten tidak ditemukan. Pastikan nama yang kamu masukkan sesuai dengan data barang.'
            );
            return;
        }

        // Simpan kota baru dan hitung ulang SEMUA filter.
        kotaTerpilih = kotaCocok;

        perbaruiLabelLokasi();
        filterBarang();

        tutupPopupLokasi();
    }

    function perbaruiLabelLokasi() {
        const label = tombolLokasi?.querySelector('span');

        if (label) {
            label.textContent = kotaTerpilih || 'Semua Lokasi';
        }
    }

    if (tombolLokasi) {
        tombolLokasi.addEventListener('click', bukaPopupLokasi);
    }

    if (tombolTutup) {
        tombolTutup.addEventListener('click', tutupPopupLokasi);
    }

    if (overlay) {
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                tutupPopupLokasi();
            }
        });
    }

    if (tombolCari) {
        tombolCari.addEventListener(
            'click',
            cariBarangBerdasarkanKota
        );
    }

    if (inputKota) {
        inputKota.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                cariBarangBerdasarkanKota();
            }

            if (event.key === 'Escape') {
                tutupPopupLokasi();
            }
        });
    }


    // =====================================================
    // 5. RESET KE KONDISI AWAL
    // =====================================================

    if (tombolReset) {
        tombolReset.addEventListener('click', function () {
            // Hapus filter lokasi.
            kotaTerpilih = '';

            // Kosongkan pencarian nama/kategori/lokasi.
            if (searchInput) {
                searchInput.value = '';
            }

            // Kembalikan kategori ke Semua.
            kategoriAktif = 'semua';

            tombolKategori.forEach(function (tombol) {
                tombol.classList.toggle(
                    'active',
                    normalisasi(tombol.dataset.category) === 'semua'
                );
            });

            // Reset teks tombol dan tampilkan kembali semua barang.
            perbaruiLabelLokasi();
            filterBarang();
            sembunyikanError();
            tutupPopupLokasi();
        });
    }


    // =====================================================
    // 6. TAMPILAN AWAL
    // =====================================================

    perbaruiLabelLokasi();
    filterBarang();

});
