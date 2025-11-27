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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');

            // message types: text, image, file, property, video
            $table->enum('type', ['text', 'image', 'file', 'property', 'video'])
                ->default('text');

            // text message
            $table->text('message')->nullable();
            
            // file url (pdf, docx, mp4, etc.)
            $table->string('file_url')->nullable();

            // file name
            $table->string('file_name')->nullable();

            // property reference
            $table->unsignedBigInteger('property_id')->nullable();
            
            // For extra data (caption, file size, mime type, etc.)
            $table->json('meta')->nullable();

            // read status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // foreign keys
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
