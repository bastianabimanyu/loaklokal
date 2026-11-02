
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'buyer_deleted_at')) {
                $table->timestamp('buyer_deleted_at')->nullable();
            }

            if (!Schema::hasColumn('conversations', 'seller_deleted_at')) {
                $table->timestamp('seller_deleted_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('conversations', 'buyer_deleted_at')) {
                $columns[] = 'buyer_deleted_at';
            }

            if (Schema::hasColumn('conversations', 'seller_deleted_at')) {
                $columns[] = 'seller_deleted_at';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
