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

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code');
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
