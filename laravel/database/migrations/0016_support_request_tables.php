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
        Schema::create('user_support_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('contact');
            $table->smallInteger('type')->default(0); // phone_number, username, email
            $table->smallInteger('status')->default(0); // pending, approved, rejected
            $table->text('note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->smallInteger('target')->default(0); // forgot-password, become-creator, become-sgm, contact-support
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_support_requests');
    }
};
