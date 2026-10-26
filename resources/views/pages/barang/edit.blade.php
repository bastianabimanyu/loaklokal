
@extends('layouts.app')

@section('title', 'Edit Barang - LoakLokal')

@push('styles')
    @vite(['resources/css/barang.css'])
@endpush

@section('content')
    <main class="edit-barang-page">
        <div class="edit-barang-container">

            <div class="edit-barang-heading">
                <a href="{{ route('beranda') }}" class="edit-back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Beranda
                </a>

                <h1>Edit Barang</h1>
                <p>Perbarui informasi barang yang kamu unggah di LoakLokal.</p>
            </div>

            <div class="edit-barang-card">

                <div class="edit-barang-card-heading">
                    <div class="edit-heading-icon">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>

                    <div>
                        <h2>Informasi Barang</h2>
                        <p>Pastikan informasi barang sudah benar.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="edit-alert edit-alert-error">
                        <strong>Data belum valid.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('barang.update', $barang) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="edit-barang-form"
                >
                    @csrf
                    @method('PUT')

                    <div class="edit-form-group">
                        <label for="nama">Nama Barang <span>*</span></label>

                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            value="{{ old('nama', $barang->nama) }}"
                            maxlength="150"
                            placeholder="Contoh: Meja belajar bekas"
                            required
                        >

                        @error('nama')
                            <small class="edit-field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label for="kategori">Kategori <span>*</span></label>

                            <select id="kategori" name="kategori" required>
                                @foreach ([
                                    'elektronik' => 'Elektronik',
                                    'pakaian' => 'Pakaian',
                                    'buku' => 'Buku',
                                    'perabotan' => 'Perabotan',
                                    'lainnya' => 'Lainnya',
                                ] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('kategori', $barang->kategori) === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('kategori')
                                <small class="edit-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="edit-form-group">
                            <label for="mode">Jenis Transaksi <span>*</span></label>

                            <select id="mode" name="mode" required>
                                @foreach ([
                                    'Jual Murah' => 'Jual Murah',
                                    'Barter/Tukar' => 'Barter / Tukar',
                                    'Hibah Gratis' => 'Hibah Gratis',
                                ] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('mode', $barang->mode) === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('mode')
                                <small class="edit-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="edit-form-group" id="editHargaGroup">
                        <label for="harga">Harga (Rupiah)</label>

                        <div class="edit-price-input">
                            <span>Rp</span>
                            <input
                                type="number"
                                id="harga"
                                name="harga"
                                value="{{ old('harga', $barang->harga) }}"
                                min="0"
                                step="1"
                                placeholder="Contoh: 50000"
                            >
                        </div>

                        <small class="edit-help-text">
                            Wajib diisi untuk transaksi Jual Murah. Tidak diperlukan untuk Hibah Gratis.
                        </small>

                        @error('harga')
                            <small class="edit-field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="edit-form-group">
                        <label for="deskripsi">Deskripsi Barang <span>*</span></label>

                        <textarea
                            id="deskripsi"
                            name="deskripsi"
                            rows="5"
                            maxlength="5000"
                            placeholder="Jelaskan kondisi, ukuran, dan informasi barang..."
                            required
                        >{{ old('deskripsi', $barang->deskripsi) }}</textarea>

                        @error('deskripsi')
                            <small class="edit-field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="edit-form-row">
                        <div class="edit-form-group">
                            <label for="kota_kabupaten">Kota/Kabupaten <span>*</span></label>

                            <input
                                type="text"
                                id="kota_kabupaten"
                                name="kota_kabupaten"
                                value="{{ old('kota_kabupaten', $barang->kota_kabupaten) }}"
                                maxlength="100"
                                placeholder="Contoh: Kediri"
                                required
                            >

                            @error('kota_kabupaten')
                                <small class="edit-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="edit-form-group">
                            <label for="kecamatan">Kecamatan <span>*</span></label>

                            <input
                                type="text"
                                id="kecamatan"
                                name="kecamatan"
                                value="{{ old('kecamatan', $barang->kecamatan) }}"
                                maxlength="100"
                                placeholder="Nama kecamatan"
                                required
                            >

                            @error('kecamatan')
                                <small class="edit-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="edit-form-group">
                        <label>Foto Saat Ini</label>

                        <div class="edit-current-photo">
                            <img
                                src="{{ asset('storage/' . $barang->foto) }}"
                                alt="Foto {{ $barang->nama }}"
                            >
                            <div>
                                <strong>{{ $barang->nama }}</strong>
                                <p>Foto saat ini akan dipertahankan jika kamu tidak mengunggah foto baru.</p>
                            </div>
                        </div>
                    </div>

                    <div class="edit-form-group">
                        <label for="foto">Ganti Foto Barang</label>

                        <label for="foto" class="edit-upload-area">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <strong>Pilih foto baru</strong>
                            <span>JPG, JPEG, PNG, atau WEBP · Maksimal 5 MB</span>
                            <span id="editFileName">Belum ada foto baru dipilih</span>
                        </label>

                        <input
                            type="file"
                            id="foto"
                            name="foto"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            hidden
                        >

                        <div id="editPhotoPreview" class="edit-photo-preview" hidden>
                            <img id="editPreviewImage" src="" alt="Pratinjau foto baru">
                        </div>

                        @error('foto')
                            <small class="edit-field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="edit-form-actions">
                        <a href="{{ route('beranda') }}" class="edit-btn-cancel">
                            Batal
                        </a>

                        <button type="submit" class="edit-btn-submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modeInput = document.getElementById('mode');
            const hargaGroup = document.getElementById('editHargaGroup');
            const hargaInput = document.getElementById('harga');

            const fotoInput = document.getElementById('foto');
            const fileName = document.getElementById('editFileName');
            const previewContainer = document.getElementById('editPhotoPreview');
            const previewImage = document.getElementById('editPreviewImage');

            function updateHargaField() {
                const isHibah = modeInput.value === 'Hibah Gratis';

                hargaGroup.hidden = isHibah;
                hargaInput.required = modeInput.value === 'Jual Murah';

                if (isHibah) {
                    hargaInput.value = '';
                }
            }

            modeInput.addEventListener('change', updateHargaField);
            updateHargaField();

            let previewUrl = null;

            fotoInput.addEventListener('change', function () {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }

                const file = fotoInput.files[0];

                if (!file) {
                    fileName.textContent = 'Belum ada foto baru dipilih';
                    previewContainer.hidden = true;
                    previewImage.removeAttribute('src');
                    return;
                }

                fileName.textContent = file.name;

                if (!file.type.startsWith('image/')) {
                    previewContainer.hidden = true;
                    previewImage.removeAttribute('src');
                    return;
                }

                previewUrl = URL.createObjectURL(file);
                previewImage.src = previewUrl;
                previewContainer.hidden = false;
            });
        });
    </script>
@endsection
