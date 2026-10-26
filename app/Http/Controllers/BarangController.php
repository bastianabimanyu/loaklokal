<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BarangController extends Controller
{
    /**
     * Memastikan hanya pemilik barang yang dapat mengelolanya.
     */
    private function pastikanPemilik(Barang $barang): void
    {
        abort_unless(
            request()->user() !== null &&
            (int) $barang->user_id === (int) request()->user()->id,
            403,
            'Kamu tidak memiliki izin untuk mengelola barang ini.'
        );
    }

    /**
     * Aturan validasi untuk upload dan edit.
     */
    private function aturanValidasi(bool $fotoWajib = true): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'max:150',
            ],

            'kategori' => [
                'required',
                Rule::in([
                    'elektronik',
                    'pakaian',
                    'buku',
                    'perabotan',
                    'lainnya',
                ]),
            ],

            'mode' => [
                'required',
                Rule::in([
                    'Jual Murah',
                    'Barter/Tukar',
                    'Hibah Gratis',
                ]),
            ],

            'harga' => [
                'nullable',
                'integer',
                'min:0',
                'required_if:mode,Jual Murah',
                'prohibited_if:mode,Hibah Gratis',
            ],

            'deskripsi' => [
                'required',
                'string',
                'max:5000',
            ],

            'kota_kabupaten' => [
                'required',
                'string',
                'max:100',
            ],

            'kecamatan' => [
                'required',
                'string',
                'max:100',
            ],

            'foto' => [
                $fotoWajib ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    /**
     * Menampilkan katalog barang.
     */
    public function index()
    {
        $barangs = Barang::with('user')
            ->latest()
            ->paginate(12);

        return view('pages.barang.index', compact('barangs'));
    }

    /**
     * Menampilkan form upload.
     */
    public function create()
    {
        return view('pages.upload-barang');
    }

    /**
     * Menyimpan barang baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->aturanValidasi()
        );

        // Simpan foto ke storage/app/public/barangs
        $fotoPath = $request->file('foto')
            ->store('barangs', 'public');

        // Hibah gratis tidak membutuhkan harga.
        if ($validated['mode'] === 'Hibah Gratis') {
            $validated['harga'] = null;
        }

        unset($validated['foto']);

        $barang = new Barang($validated);

        // Pemilik selalu diambil dari akun yang login.
        $barang->user_id = $request->user()->id;
        $barang->foto = $fotoPath;
        $barang->status = 'tersedia';

        $barang->save();

        return redirect()
            ->route('beranda')
            ->with('success', 'Barang berhasil diunggah!');
    }

    /**
     * Menampilkan form edit barang.
     */
    public function edit(Barang $barang)
    {
        $this->pastikanPemilik($barang);

        return view('pages.barang.edit', compact('barang'));
    }

    /**
     * Memperbarui barang milik pengguna.
     */
    public function update(Request $request, Barang $barang)
    {
        $this->pastikanPemilik($barang);

        $validated = $request->validate(
            $this->aturanValidasi(false)
        );

        if ($validated['mode'] === 'Hibah Gratis') {
            $validated['harga'] = null;
        }

        // Jika pengguna memilih foto baru.
        if ($request->hasFile('foto')) {
            $fotoLama = $barang->foto;

            $fotoBaru = $request->file('foto')
                ->store('barangs', 'public');

            $validated['foto'] = $fotoBaru;

            if ($fotoLama) {
                Storage::disk('public')->delete($fotoLama);
            }
        }

        // Tidak menerima user_id dari form.
        // Status juga tidak diubah lewat form edit.
        $barang->update($validated);

        return redirect()
            ->route('beranda')
            ->with('success', 'Barang berhasil diperbarui!');
    }

    /**
     * Mengubah status barang menjadi sudah terjual.
     */
    public function tandaiTerjual(Barang $barang)
    {
        $this->pastikanPemilik($barang);

        $barang->update([
            'status' => 'terjual',
        ]);

        return back()->with(
            'success',
            'Barang berhasil ditandai sebagai sudah terjual.'
        );
    }

    /**
     * Menghapus barang milik pengguna.
     */
    public function destroy(Barang $barang)
    {
        $this->pastikanPemilik($barang);

        // Hapus foto dari storage.
        if ($barang->foto) {
            Storage::disk('public')->delete($barang->foto);
        }

        // Hapus data barang dari database.
        $barang->delete();

        return redirect()
            ->route('beranda')
            ->with('success', 'Barang berhasil dihapus.');
    }

    public function show(Barang $barang)
    {
        $barang->load('user');

        return view('pages.barang.detail-barang', compact('barang'));
    }
}

