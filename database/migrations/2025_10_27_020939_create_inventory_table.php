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
        Schema::create('inventory', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('variant_id');
            $table->uuid('outlet_id');
            $table->integer('on_hand')->default(0); // Current stock level
            $table->integer('safety_stock')->default(0); // Minimum stock alert threshold
            $table->timestamps();

            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('cascade');

            // Indexes
            $table->unique(['tenant_id', 'variant_id', 'outlet_id']);
            $table->index(['tenant_id', 'outlet_id']);
            $table->index('on_hand'); // For fast stock lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
