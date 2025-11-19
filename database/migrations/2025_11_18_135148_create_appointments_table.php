<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('inquiry_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['visit', 'call'])->default('visit');
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(30); // Duration in minutes
            $table->enum('status', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('customer_notes')->nullable(); // Customer can add notes
            $table->text('agent_notes')->nullable(); // Agent can add notes
            $table->string('location')->nullable(); // For visits
            $table->string('phone_number')->nullable(); // For calls
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by')->nullable(); // 'agent' or 'customer'
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'scheduled_at']);
            $table->index(['customer_id', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};