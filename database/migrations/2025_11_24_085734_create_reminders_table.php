<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('inquiry_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['inquiry_followup', 'appointment_followup', 'general', 'document_pending', 'payment_followup'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->dateTime('remind_at');
            $table->enum('status', ['pending', 'completed', 'snoozed', 'cancelled'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->boolean('notification_sent')->default(false);
            $table->enum('email_status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('email_error')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'status', 'remind_at']);
            $table->index(['status', 'remind_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reminders');
    }
};