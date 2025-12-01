<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('video')->nullable()->after('primary_image');
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('video');
        });
    }
};