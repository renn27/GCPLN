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
        Schema::create('hasil_gcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rbm_id')->unique()->constrained('rbms')->cascadeOnDelete();
            $table->integer('open')->default(0);
            $table->integer('submitted')->default(0);
            $table->integer('rejected')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_gcs');
    }
};
