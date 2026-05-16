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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wilaya_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('store_name');
            $table->string('phone_number')->nullable();
            $table->string('password')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();

            $table->decimal('balance', 12, 2)->default(0);

            $table->smallInteger('status')->default(0); // pending, active, suspended,

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
