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
        Schema::create('media_backups', function (Blueprint $table) {
            $table->id();

            $table->string('disk')->default('public');
            $table->string('directory')->nullable();

            $table->string('name')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->string('path')->nullable();
            $table->string('url');

            $table->string('collection')->default('default');

            $table->nullableMorphs('mediable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_backups');
    }
};
