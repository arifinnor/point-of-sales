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
        Schema::create('registers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('outlet_id');
            $table->string('name');
            $table->uuid('printer_profile_id')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade');

            // Indexes
            $table->index('outlet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registers');
    }
};
