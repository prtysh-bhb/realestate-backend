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
        Schema::create('rating_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');

            $table->float('avg_construction')->default(0);
            $table->float('avg_amenities')->default(0);
            $table->float('avg_management')->default(0);
            $table->float('avg_connectivity')->default(0);
            $table->float('avg_green_area')->default(0);
            $table->float('avg_locality')->default(0);

            $table->float('overall_rating')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_stats');
    }
};
