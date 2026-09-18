<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eidolons', function (Blueprint $table) {
            $table->unsignedTinyInteger('eidolon_number')
                ->nullable()
                ->after('character_id');

            $table->unique([
                'character_id',
                'eidolon_number',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('eidolons', function (Blueprint $table) {
            $table->dropUnique([
                'character_id',
                'eidolon_number',
            ]);

            $table->dropColumn('eidolon_number');
        });
    }
};