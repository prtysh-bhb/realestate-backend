<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->nullable()->constrained('properties')->onDelete('set null');
            $table->json('property_details');
            $table->decimal('estimated_price', 15, 2);
            $table->decimal('price_range_min', 15, 2);
            $table->decimal('price_range_max', 15, 2);
            $table->text('ai_reasoning')->nullable();
            $table->json('comparables')->nullable();
            $table->json('breakdown')->nullable();
            $table->decimal('suggested_listing_price', 15, 2)->nullable();
            $table->timestamps();
            
            $table->index('agent_id');
            $table->index('property_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_estimates');
    }
};