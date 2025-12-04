<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
            
            $table->index('blog_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};