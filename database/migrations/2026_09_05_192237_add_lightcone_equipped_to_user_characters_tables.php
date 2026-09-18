<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_characters', function (Blueprint $table) {
            $table->foreignId('equipped_lightcone_id')
                ->nullable()
                ->after('character_id')
                ->constrained('user_lightcones')
                ->nullOnDelete();

            $table->foreignId('head_id')
                ->nullable()
                ->after('equipped_lightcone_id')
                ->constrained('user_relics')
                ->nullOnDelete();

            $table->foreignId('hands_id')
                ->nullable()
                ->after('head_id')
                ->constrained('user_relics')
                ->nullOnDelete();

            $table->foreignId('body_id')
                ->nullable()
                ->after('hands_id')
                ->constrained('user_relics')
                ->nullOnDelete();

            $table->foreignId('feet_id')
                ->nullable()
                ->after('body_id')
                ->constrained('user_relics')
                ->nullOnDelete();

            $table->foreignId('planar_sphere_id')
                ->nullable()
                ->after('feet_id')
                ->constrained('user_relics')
                ->nullOnDelete();

            $table->foreignId('link_rope_id')
                ->nullable()
                ->after('planar_sphere_id')
                ->constrained('user_relics')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_characters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipped_lightcone_id');
            $table->dropConstrainedForeignId('head_id');
            $table->dropConstrainedForeignId('hands_id');
            $table->dropConstrainedForeignId('body_id');
            $table->dropConstrainedForeignId('feet_id');
            $table->dropConstrainedForeignId('planar_sphere_id');
            $table->dropConstrainedForeignId('link_rope_id');
        });
    }
};