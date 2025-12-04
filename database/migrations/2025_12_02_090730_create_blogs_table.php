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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            
            // User & Category relationships
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->onDelete('set null');
            
            // Basic fields (existing)
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable(); // Made nullable
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            
            // Images (support both old and new)
            $table->string('image')->nullable(); // Keep for backward compatibility
            $table->string('featured_image')->nullable();
            
            // Status (updated enum)
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            
            // Moderation fields
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            
            // Additional fields
            $table->integer('views_count')->default(0);
            $table->json('meta_tags')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'published_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};