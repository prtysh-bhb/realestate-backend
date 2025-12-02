<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider');
            }
            // Skip avatar since it already exists
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id']);
        });
    }
};