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
        Schema::create('label_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image_url')->nullable();
            $table->string('code');
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });

        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('label_categories');
    }
};
