<?php

namespace App\Services;

use App\Models\UserCharacter;

class CharacterStatsCalculator
{
    private const STAT_ALIASES = [
        'spd' => 'speed',
        'crit_d' => 'crit_dmg',
        'crit_r' => 'crit_rate',
    ];

    public function calculate(UserCharacter $userCharacter): array
    {
        $character = $userCharacter->character;

        $stats = $character->getStatsAtLevel(
            $userCharacter->level
        );

        $stats['crit_dmg'] = (float) ($character->stats->crit_d ?? 0);
        $stats['crit_rate'] = (float) ($character->stats->crit_r ?? 0);

        $equippedLightcone = $userCharacter->lightcone;

        $lightconeStats = $equippedLightcone
            ? $equippedLightcone->lightcone->getStatsAtLevel(
                $equippedLightcone->level
            )
            : [
                'hp' => 0,
                'atk' => 0,
                'def' => 0,
            ];

        $equippedRelics = [
            $userCharacter->head,
            $userCharacter->hands,
            $userCharacter->body,
            $userCharacter->feet,
            $userCharacter->planarSphere,
            $userCharacter->linkRope,
        ];

        $relicStats = [];

        foreach ($equippedRelics as $relic) {
            if (!$relic) continue;

            foreach ($relic->stats as $relicStat) {
                if ($relicStat->is_hidden) continue;

                $slug = $relicStat->stat?->slug;

                if (!$slug) continue;

                $slug = self::STAT_ALIASES[$slug] ?? $slug;

                $relicStats[$slug] =
                    ($relicStats[$slug] ?? 0)
                    + (float) $relicStat->value;
            }
        }

        foreach (['hp', 'atk', 'def'] as $stat) {
            $base =
                (float) ($stats[$stat] ?? 0)
                + (float) ($lightconeStats[$stat] ?? 0);

            $percentage =
                (float) ($relicStats["{$stat}_pct"] ?? 0) / 100;

            $flat = (float) ($relicStats[$stat] ?? 0);

            $stats[$stat] =
                $base * (1 + $percentage) + $flat;
        }

        $speedPercentage =
            (float) ($relicStats['speed_pct'] ?? 0) / 100;

        $flatSpeed = (float) ($relicStats['speed'] ?? 0);

        $stats['speed'] =
            (float) ($stats['speed'] ?? 0)
            * (1 + $speedPercentage)
            + $flatSpeed;

        foreach ([
            'crit_rate',
            'crit_dmg',
            'break_effect',
            'effect_hit_rate',
            'effect_res',
            'outgoing_healing',
            'physical_dmg',
            'fire_dmg',
            'ice_dmg',
            'lightning_dmg',
            'wind_dmg',
            'quantum_dmg',
            'imaginary_dmg',
        ] as $stat) {
            $stats[$stat] =
                (float) ($stats[$stat] ?? 0)
                + (float) ($relicStats[$stat] ?? 0);
        }

        $stats['energy_regen_rate'] =
            100 + (float) ($relicStats['energy_regen_rate'] ?? 0);

        return $stats;
    }
}