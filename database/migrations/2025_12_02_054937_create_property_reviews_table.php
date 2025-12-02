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
        Schema::create('property_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->unsignedTinyInteger('construction');
            $table->unsignedTinyInteger('amenities');
            $table->unsignedTinyInteger('management');
            $table->unsignedTinyInteger('connectivity');
            $table->unsignedTinyInteger('green_area');
            $table->unsignedTinyInteger('locality');

            $table->text('positive_comment')->nullable();
            $table->text('negative_comment')->nullable();

            $table->boolean('is_visible')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_reviews');
    }
};
