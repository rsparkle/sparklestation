<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserItemLevelSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_characters')->update([
            'level' => 80
        ]);

        DB::table('user_lightcones')->update([
            'level' => 80
        ]);
    }
}