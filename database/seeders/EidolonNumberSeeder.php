<?php

namespace Database\Seeders;

use App\Models\Character;
use Illuminate\Database\Seeder;

class EidolonNumberSeeder extends Seeder
{
    /**
     * Assign E1–E6 to each character's existing eidolons.
     */
    public function run(): void
    {
        Character::with([
            'eidolons' => fn ($query) => $query->orderBy('id'),
        ])->each(function (Character $character) {
            foreach ($character->eidolons as $index => $eidolon) {
                $eidolon->update([
                    'eidolon_number' => $index + 1,
                ]);
            }
        });
    }
}