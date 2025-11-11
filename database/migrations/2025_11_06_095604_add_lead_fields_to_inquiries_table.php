<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->enum('stage', ['new', 'contacted', 'qualified', 'negotiation', 'closed_won', 'closed_lost'])->default('new')->after('status');
            $table->text('notes')->nullable()->after('stage');
            $table->json('history')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['stage', 'notes', 'history']);
        });
    }
};