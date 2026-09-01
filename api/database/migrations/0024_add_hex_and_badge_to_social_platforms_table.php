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
        Schema::table('social_platforms', function (Blueprint $table) {
            if (! Schema::hasColumn('social_platforms', 'hex')) {
                $table->string('hex')->nullable()->after('label');
            }
            if (! Schema::hasColumn('social_platforms', 'badge')) {
                $table->string('badge')->nullable()->after('hex');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_platforms', function (Blueprint $table) {
            if (Schema::hasColumn('social_platforms', 'hex')) {
                $table->dropColumn('hex');
            }
            if (Schema::hasColumn('social_platforms', 'badge')) {
                $table->dropColumn('badge');
            }
        });
    }
};
