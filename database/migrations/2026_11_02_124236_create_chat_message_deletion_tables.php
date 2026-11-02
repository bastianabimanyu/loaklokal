
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_user_deletions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('message_id')
                ->constrained('messages')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['message_id', 'user_id'],
                'mud_message_user_unique'
            );
        });

        Schema::create('message_deletion_tombstones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('original_message_id')->unique(
                'mdt_original_message_unique'
            );

            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('deleted_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('original_created_at');
            $table->timestamp('deleted_at');

            $table->timestamps();

            $table->index(
                ['conversation_id', 'original_created_at'],
                'mdt_conv_created_idx'
            );
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->timestamp('buyer_deleted_at')->nullable();
            $table->timestamp('seller_deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_deleted_at',
                'seller_deleted_at',
            ]);
        });

        Schema::dropIfExists('message_deletion_tombstones');
        Schema::dropIfExists('message_user_deletions');
    }
};
