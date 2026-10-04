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
        Schema::create('product_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('classification_code')->nullable()->after('quality_id');
            $table->foreign('classification_code')->references('code')->on('product_classifications')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['classification_code']);
            $table->dropColumn('classification_code');
        });

        Schema::dropIfExists('product_classifications');
    }
};
