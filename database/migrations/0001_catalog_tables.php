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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });
        Schema::create('wilayas', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('number')->nullable();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilaya_id')->constrained('wilayas')->cascadeOnDelete();
            $table->string('code')->index();
            $table->string('post_code')->nullable();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });
        Schema::create('genders', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });
        Schema::create('social_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('label')->nullable();
            $table->string('hex')->nullable();
            $table->string('badge')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->string('code'); // S, M, L, XL, 40, 41, One Size
            $table->string('type')->nullable();  // clothing, shoes, numeric, universal

            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();

            $table->timestamps();
        });
        Schema::create('qualities', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            // original, copy, premium_copy

            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();

            $table->timestamps();
        });

        // 1. Delivery companies / providers (e.g., Yalidine, Swift Express, ZR Express, Kazi Tour, EcoTrack, etc.)
        Schema::create('delivery_companies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayas');
    }
};
