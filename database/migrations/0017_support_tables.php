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
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('contact');
            $table->string('type')->nullable()->default('phone_number'); // phone_number, username, email
            $table->string('status')->nullable()->default('pending'); // pending, approved, rejected
            $table->text('note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('target')->nullable()->default('forgot-password'); // forgot-password, become-creator, become-sgm, contact-support
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
