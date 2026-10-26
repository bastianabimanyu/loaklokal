
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();

            // Pemilik barang
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Informasi barang
            $table->string('nama', 150);
            $table->string('kategori', 50);
            $table->string('mode', 30);
            $table->unsignedBigInteger('harga')->nullable();
            $table->text('deskripsi');

            // Lokasi barang
            $table->string('kota_kabupaten', 100);
            $table->string('kecamatan', 100);

            // Path foto di storage
            $table->string('foto');

            // Status barang
            $table->string('status')->default('tersedia');

            $table->timestamps();

            $table->index(['kategori', 'status']);
            $table->index(['kota_kabupaten', 'kecamatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};