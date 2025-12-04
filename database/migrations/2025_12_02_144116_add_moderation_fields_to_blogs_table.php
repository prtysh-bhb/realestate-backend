<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('blogs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('blogs', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('user_id')->constrained('blog_categories')->onDelete('set null');
            }
            if (!Schema::hasColumn('blogs', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('blogs', 'content')) {
                $table->longText('content')->nullable()->after('excerpt');
            }
            if (!Schema::hasColumn('blogs', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('image');
            }
            
            // Modify status column if it exists
            if (Schema::hasColumn('blogs', 'status')) {
                DB::statement("ALTER TABLE blogs MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'rejected') DEFAULT 'draft'");
            }
            
            // Add moderation fields
            if (!Schema::hasColumn('blogs', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('blogs', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('rejection_reason')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('blogs', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (!Schema::hasColumn('blogs', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('reviewed_at');
            }
            if (!Schema::hasColumn('blogs', 'views_count')) {
                $table->integer('views_count')->default(0)->after('published_at');
            }
            if (!Schema::hasColumn('blogs', 'meta_tags')) {
                $table->json('meta_tags')->nullable()->after('views_count');
            }
            
            // Add soft deletes if not exists
            if (!Schema::hasColumn('blogs', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'category_id',
                'excerpt',
                'content',
                'featured_image',
                'rejection_reason',
                'reviewed_by',
                'reviewed_at',
                'published_at',
                'views_count',
                'meta_tags',
                'deleted_at',
            ]);
        });
    }
};