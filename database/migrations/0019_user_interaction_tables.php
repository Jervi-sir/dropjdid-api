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
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Interaction type: 'like', 'save', 'share'
            $table->string('type'); 
            
            // Target entity: target_type ('advertisement', 'ad', 'drop', etc.) & target_id
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');

            // Optional metadata (e.g. shared_to_user_id, platform, etc.)
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['target_type', 'target_id', 'type']);
            $table->index(['user_id', 'type', 'target_type', 'target_id']);
        });

        // Add counter columns to advertisements if not present
        Schema::table('advertisements', function (Blueprint $table) {
            if (! Schema::hasColumn('advertisements', 'nb_liked')) {
                $table->unsignedInteger('nb_liked')->default(0);
            }
            if (! Schema::hasColumn('advertisements', 'nb_saved')) {
                $table->unsignedInteger('nb_saved')->default(0);
            }
            if (! Schema::hasColumn('advertisements', 'nb_shared')) {
                $table->unsignedInteger('nb_shared')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_interactions');

        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn(['nb_liked', 'nb_saved', 'nb_shared']);
        });
    }
};
