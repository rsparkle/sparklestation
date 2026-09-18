<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_characters', function (Blueprint $table) {
            $table->integer('level')->default(80)->after('copies_available');
        });

        Schema::table('user_lightcones', function (Blueprint $table) {
            $table->integer('level')->default(80)->after('copies_available');
        });
    }

    public function down(): void
    {
        Schema::table('user_characters', function (Blueprint $table) {
            $table->dropColumn('level');
        });

        Schema::table('user_lightcones', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};