<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // In PostgreSQL, Laravel creates a CHECK constraint for enums.
        // We need to drop the old constraint and add a new one including 'contact-support'.
        DB::statement('ALTER TABLE user_support_requests DROP CONSTRAINT user_support_requests_target_check');
        DB::statement("ALTER TABLE user_support_requests ADD CONSTRAINT user_support_requests_target_check CHECK (target IN ('forgot-password', 'become-creator', 'become-sgm', 'contact-support'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE user_support_requests DROP CONSTRAINT user_support_requests_target_check');
        DB::statement("ALTER TABLE user_support_requests ADD CONSTRAINT user_support_requests_target_check CHECK (target IN ('forgot-password', 'become-creator', 'become-sgm'))");
    }
};
