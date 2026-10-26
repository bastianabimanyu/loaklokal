
document.addEventListener('DOMContentLoaded', function () {
    // ==========================================
    // ELEMEN HALAMAN
    // ==========================================
    const input = document.getElementById('filterSearchInput');
    const tombolHapus = document.getElementById('filterClearButton');
    const katalog = document.getElementById('katalogContainer');
    const pesanKosong = document.getElementById('filterNoResults');
    const tombolResetPencarian = document.getElementById('filterResetSearch');

    const tombolLokasi = document.getElementById('radiusLocationBtn');
    const overlay = document.getElementById('lokasiOverlay');
    const tombolTutup = document.getElementById('lokasiClose');
    const inputKota = document.getElementById('kotaSearchInput');
    const tombolCariLokasi = document.getElementById('lokasiCariBtn');
    const tombolResetLokasi = document.getElementById('lokasiReset');
    const pesanErrorLokasi = document.getElementById('lokasiError');

    const labelLokasiAktif = document.getElementById('activeLocationLabel');
    const labelKategoriAktif = document.getElementById('activeCategoryLabel');

    if (!katalog) {
        console.error('Filter barang: elemen #katalogContainer tidak ditemukan.');
        return;
    }

    // ==========================================
    // STATE
    // ==========================================
    let kotaTerpilih = '';

    // ==========================================
    // HELPER
    // ==========================================
    function normalisasi(teks) {
        return (teks || '')
            .toLocaleLowerCase('id-ID')
            .trim()
            .replace(/\s+/g, ' ');
    }

    function ambilKartu() {
        return [...katalog.querySelectorAll('.barang-card')];
    }

    function tampilkanErrorLokasi(pesan) {
        if (!pesanErrorLokasi) return;

        pesanErrorLokasi.textContent = pesan;
        pesanErrorLokasi.hidden = false;
    }

    function sembunyikanErrorLokasi() {
        if (!pesanErrorLokasi) return;

        pesanErrorLokasi.textContent = '';
        pesanErrorLokasi.hidden = true;
    }

    // ==========================================
    // FILTER PENCARIAN DAN LOKASI
    // ==========================================
    function terapkanFilter() {
        const kataKunci = normalisasi(input?.value);
        const kotaDicari = normalisasi(kotaTerpilih);
        let jumlahTampil = 0;

        ambilKartu().forEach(function (kartu) {
            const nama = normalisasi(kartu.dataset.nama);
            const kategori = normalisasi(kartu.dataset.kategori);
            const kota = normalisasi(kartu.dataset.kota);
            const kecamatan = normalisasi(kartu.dataset.kecamatan);
            const mode = normalisasi(kartu.dataset.mode);

            const cocokKataKunci =
                !kataKunci ||
                nama.includes(kataKunci) ||
                kategori.includes(kataKunci) ||
                kota.includes(kataKunci) ||
                kecamatan.includes(kataKunci) ||
                mode.includes(kataKunci);

            const cocokLokasi =
                !kotaDicari || kota === kotaDicari;

            const tampilkan = cocokKataKunci && cocokLokasi;

            kartu.hidden = !tampilkan;

            if (tampilkan) {
                jumlahTampil++;
            }
        });

        if (pesanKosong) {
            pesanKosong.hidden = jumlahTampil > 0;
        }

        if (tombolHapus) {
            tombolHapus.hidden = !input?.value;
        }

        if (labelLokasiAktif) {
            labelLokasiAktif.textContent =
                kotaTerpilih || 'Semua lokasi';
        }
    }

    // ==========================================
    // PENCARIAN BARANG
    // ==========================================
    if (input) {
        input.addEventListener('input', terapkanFilter);
    }

    if (tombolHapus) {
        tombolHapus.addEventListener('click', function () {
            if (input) {
                input.value = '';
                terapkanFilter();
                input.focus();
            }
        });
    }

    if (tombolResetPencarian) {
        tombolResetPencarian.addEventListener('click', function () {
            if (input) {
                input.value = '';
            }

            kotaTerpilih = '';
            terapkanFilter();
        });
    }

    // ==========================================
    // POPUP LOKASI
    // ==========================================
    function bukaPopupLokasi() {
        if (!overlay || !inputKota) return;

        overlay.hidden = false;
        inputKota.value = '';
        sembunyikanErrorLokasi();
        inputKota.focus();
    }

    function tutupPopupLokasi() {
        if (overlay) {
            overlay.hidden = true;
        }
    }

    if (tombolLokasi) {
        tombolLokasi.addEventListener('click', bukaPopupLokasi);
    }

    const tombolHeroLokasi = document.querySelector('.hero-location-button');

    if (tombolHeroLokasi) {
        tombolHeroLokasi.addEventListener('click', bukaPopupLokasi);
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

    // ==========================================
    // CARI BERDASARKAN KOTA/KABUPATEN
    // ==========================================
    function cariLokasi() {
        if (!inputKota) return;

        sembunyikanErrorLokasi();

        const kataKunci = normalisasi(inputKota.value);

        if (!kataKunci) {
            tampilkanErrorLokasi(
                'Masukkan nama kota/kabupaten terlebih dahulu.'
            );
            inputKota.focus();
            return;
        }

        const kartu = ambilKartu();

        // Periksa apakah yang dimasukkan adalah kecamatan.
        const kecamatanDitemukan = kartu.some(function (item) {
            return normalisasi(item.dataset.kecamatan) === kataKunci;
        });

        if (kecamatanDitemukan) {
            tampilkanErrorLokasi(
                'Masukkan nama kota/kabupaten, bukan kecamatan.'
            );
            return;
        }

        // Cari kota berdasarkan barang yang tersedia.
        const barangCocok = kartu.find(function (item) {
            return normalisasi(item.dataset.kota).includes(kataKunci);
        });

        if (!barangCocok) {
            tampilkanErrorLokasi(
                'Kota/kabupaten tidak ditemukan pada barang yang tersedia.'
            );
            return;
        }

        kotaTerpilih = (barangCocok.dataset.kota || '').trim();

        terapkanFilter();
        tutupPopupLokasi();
    }

    if (tombolCariLokasi) {
        tombolCariLokasi.addEventListener('click', cariLokasi);
    }

    if (inputKota) {
        inputKota.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                cariLokasi();
            }

            if (event.key === 'Escape') {
                tutupPopupLokasi();
            }
        });
    }

    // ==========================================
    // RESET LOKASI
    // ==========================================
    if (tombolResetLokasi) {
        tombolResetLokasi.addEventListener('click', function () {
            kotaTerpilih = '';

            if (inputKota) {
                inputKota.value = '';
            }

            sembunyikanErrorLokasi();
            terapkanFilter();
            tutupPopupLokasi();
        });
    }

    // ==========================================
    // TUTUP POPUP DENGAN ESCAPE
    // ==========================================
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            tutupPopupLokasi();
        }
    });

    // ==========================================
    // INISIALISASI
    // ==========================================
    terapkanFilter();
});
