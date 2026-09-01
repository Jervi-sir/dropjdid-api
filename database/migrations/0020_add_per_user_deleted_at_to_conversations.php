<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('conversations', 'first_user_deleted_at')) {
                $table->timestamp('first_user_deleted_at')->nullable()->after('second_user_last_read_at');
            }
            if (! Schema::hasColumn('conversations', 'second_user_deleted_at')) {
                $table->timestamp('second_user_deleted_at')->nullable()->after('first_user_deleted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['first_user_deleted_at', 'second_user_deleted_at']);
        });
    }
};
