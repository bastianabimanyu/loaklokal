
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Percakapan tempat pesan dikirim
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();

            // Pengguna yang mengirim pesan
            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Jenis pesan: text, image, video, location
            $table->enum('type', ['text', 'image', 'video', 'location'])->default('text');

            // Isi pesan teks
            $table->text('body')->nullable();

            // Informasi file media
            $table->string('media_path')->nullable();
            $table->string('media_mime', 100)->nullable();

            // Informasi lokasi
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->string('location_name')->nullable();

            $table->timestamps();

            // Mempercepat pengambilan riwayat pesan
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
