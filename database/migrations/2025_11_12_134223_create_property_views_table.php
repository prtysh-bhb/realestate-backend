<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('property_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('viewed_at');
            
            // Indexes for performance
            $table->index(['property_id', 'viewed_at'], 'property_views_property_viewed_idx');
            $table->index('viewed_at', 'property_views_viewed_at_idx');
            $table->index('user_id', 'property_views_user_id_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_views');
    }
};