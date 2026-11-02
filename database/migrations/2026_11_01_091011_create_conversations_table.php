
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Barang yang menjadi konteks percakapan
            $table->foreignId('barang_id')
                ->constrained('barangs')
                ->cascadeOnDelete();

            // Pembeli dan penjual
            $table->foreignId('buyer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Satu pembeli hanya memiliki satu percakapan
            // untuk barang yang sama
            $table->unique(['barang_id', 'buyer_id']);

            // Mempermudah pencarian percakapan pengguna
            $table->index(['buyer_id', 'updated_at']);
            $table->index(['seller_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
