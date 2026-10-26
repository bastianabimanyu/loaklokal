
@php
    $isEdit = isset($barang);
@endphp

<form
    action="{{ $isEdit ? route('barang.update', $barang) : route('barang.store') }}"
    method="POST"
    enctype="multipart/form-data"
    class="upload-form"
>
    @csrf

    @if ($isEdit)
        @method('PUT')
    @endif

    {{-- Foto barang --}}
    <div class="form-group">
        <label for="foto">Foto Barang</label>

        @if ($isEdit && $barang->foto)
            <img
                src="{{ asset('storage/' . $barang->foto) }}"
                alt="Foto {{ $barang->nama }}"
                style="width: 100%; max-height: 220px; object-fit: contain; margin-bottom: 12px;"
            >
        @endif

        <input
            type="file"
            id="foto"
            name="foto"
            accept="image/jpeg,image/png,image/webp"
            {{ $isEdit ? '' : 'required' }}
        >

        <small>
            Format JPG, PNG, atau WEBP. Maksimal 5 MB.
            @if ($isEdit)
                Kosongkan jika tidak ingin mengganti foto.
            @endif
        </small>

        @error('foto')
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    {{-- Nama barang --}}
    <div class="form-group">
        <label for="nama">Nama Barang / Judul Postingan</label>

        <input
            type="text"
            id="nama"
            name="nama"
            value="{{ old('nama', $barang->nama ?? '') }}"
            placeholder="Contoh: Kipas Angin Portable Bekas"
            required
            maxlength="150"
        >

        @error('nama')
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kategori --}}
    <div class="form-group">
        <label for="kategori">Kategori Barang</label>

        <select id="kategori" name="kategori" required>
            <option value="">-- Pilih Kategori --</option>

            @foreach ([
                'elektronik' => 'Elektronik',
                'pakaian' => 'Pakaian & Fashion',
                'buku' => 'Buku & Alat Tulis',
                'perabotan' => 'Perabotan Rumah',
                'lainnya' => 'Lainnya',
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('kategori', $barang->kategori ?? '') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('kategori')
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    {{-- Mode transaksi --}}
    <div class="form-group">
        <label>Mode Transaksi</label>

        <div class="radio-group">
            @foreach (['Jual Murah', 'Barter/Tukar', 'Hibah Gratis'] as $mode)
                <label class="radio-item">
                    <input
                        type="radio"
                        name="mode"
                        value="{{ $mode }}"
                        @checked(old('mode', $barang->mode ?? 'Jual Murah') === $mode)
                        required
                    >
                    {{ $mode }}
                </label>
            @endforeach
        </div>

        @error('mode')
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    {{-- Harga --}}
    <div class="form-group">
        <label for="harga">Harga Barang (Rp)</label>

        <input
            type="number"
            id="harga"
            name="harga"
            min="0"
            step="1"
            value="{{ old('harga', $barang->harga ?? '') }}"
            placeholder="Contoh: 35000"
        >

        <small>
            Wajib diisi untuk Jual Murah. Untuk Barter boleh dikosongkan.
            Hibah Gratis tidak menggunakan harga.
        </small>

        @error('harga')
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    {{-- Deskripsi --}}
    <div class="form-group">
        <label for="deskripsi">Deskripsi Singkat & Kondisi</label>

        <textarea
            id="deskripsi"
            name="deskripsi"
            rows="4"
            maxlength="5000"
            placeholder="Tuliskan kondisi barang, kekurangan, dan informasi lainnya..."
            required
        >{{ old('deskripsi', $barang->deskripsi ?? '') }}</textarea>

        @error('deskripsi')
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kota atau kabupaten --}}
    <div class="form-group">
        <label for="kota_kabupaten">Kota / Kabupaten</label>

        <input
            type="text"
            id="kota_kabupaten"
            name="kota_kabupaten"
            value="{{ old('kota_kabupaten', $barang->kota_kabupaten ?? '') }}"
            placeholder="Contoh: Kota Kediri"
            maxlength="100"
            required
        >

        @error('kota_kabupaten')
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kecamatan --}}
    <div class="form-group">
        <label for="kecamatan">Kecamatan</label>

        <input
            type="text"
            id="kecamatan"
            name="kecamatan"
            value="{{ old('kecamatan', $barang->kecamatan ?? '') }}"
            placeholder="Contoh: Mojoroto"
            maxlength="100"
            required
        >

        @error('kecamatan')
            <p class="error-message">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="btn-submit" id="uploadSubmitButton">
        <span id="uploadButtonText">
            {{ $isEdit ? 'Simpan Perubahan' : 'Upload Barang' }}
        </span>

        <span id="uploadButtonLoading" class="upload-loading" hidden>
            <span class="loading-spinner"></span>
            {{ $isEdit ? 'Menyimpan perubahan...' : 'Mengupload barang...' }}
        </span>
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.upload-form');
    const button = document.getElementById('uploadSubmitButton');
    const buttonText = document.getElementById('uploadButtonText');
    const buttonLoading = document.getElementById('uploadButtonLoading');

    if (!form || !button || !buttonText || !buttonLoading) {
        return;
    }

    form.addEventListener('submit', function (event) {
        // Jangan tampilkan loading jika validasi browser gagal.
        if (!form.checkValidity()) {
            return;
        }

        // Mencegah submit berulang.
        if (button.disabled) {
            event.preventDefault();
            return;
        }

        buttonText.hidden = true;
        buttonLoading.hidden = false;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    });
});
</script>
