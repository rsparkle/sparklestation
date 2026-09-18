<?php

namespace App\Services;

use App\Models\User;
use App\Models\Character;
use App\Models\Lightcone;
use App\Models\UserPull;
use App\Models\UserCharacter;
use App\Models\UserLightcone;
use Illuminate\Support\Facades\DB;

class GachaService
{
    private const FOUR_STAR_BASE_RATE = 0.051;
    private const FOUR_STAR_PITY_RATE = 0.514;
    private const FOUR_STAR_PITY_PULL = 10;

    private const FIVE_STAR_BASE_RATE = 0.006;
    private const FIVE_STAR_SOFT_PITY_PULL = 74;
    private const FIVE_STAR_PITY_PULL = 90;
    private const FIVE_STAR_RATE_INCREASE = 0.06;

    public function processPull(User $user, int $pullCount, string $type, int $itemId): array
    {
        $pities = $user->pities()
            ->where('type', $type)
            ->get()
            ->keyBy('rarity');

        $fourPity = $pities[4];
        $fivePity = $pities[5];

        return DB::transaction(function () use ($user, $pullCount, $fourPity, $fivePity, $type, $itemId) {
            return $this->getResults($user, $pullCount, $fourPity, $fivePity, $type, $itemId);
        });
    }

    public function getResults(User $user, int $pullCount, $fourPity, $fivePity, string $type, int $itemId): array
    {
        $results = [];
        $pullsLog = [];

        $maxFivePity = $type === 'character' 
            ? self::FIVE_STAR_PITY_PULL 
            : self::FIVE_STAR_PITY_PULL - 10;

        $fiveStarSoftPityPull = $type === 'character' 
            ? self::FIVE_STAR_SOFT_PITY_PULL 
            : self::FIVE_STAR_SOFT_PITY_PULL - 10;

        $model = match ($type) {
            'character' => Character::class,
            'lightcone' => Lightcone::class,
        };

        $threeStarPool = Lightcone::where('rarity', 3)->get();
        $fourStarPool  = Character::where('rarity', 4)->get()
            ->concat(Lightcone::where('rarity', 4)->get());
        $standardItems = $model::where('is_standard', true)->where('rarity', 5)->get();
        $featuredItem  = $model::findOrFail($itemId);

        $fourPityCount      = $fourPity->pity;
        $fivePityCount      = $fivePity->pity;
        $fiveStarGuaranteed = (bool) $fivePity->guaranteed;

        for ($i = 1; $i <= $pullCount; $i++) {
            if ($fivePityCount >= $maxFivePity) {
                $fiveStarOdds = 1.0;
            } elseif ($fivePityCount >= $fiveStarSoftPityPull) {
                $fiveStarOdds = self::FIVE_STAR_BASE_RATE + (
                    ($fivePityCount - $fiveStarSoftPityPull + 1) * self::FIVE_STAR_RATE_INCREASE
                );
            } else {
                $fiveStarOdds = self::FIVE_STAR_BASE_RATE;
            }

            $fourStarOdds = match($fourPityCount) {
                10      => 1.0,
                9       => self::FOUR_STAR_PITY_RATE,
                default => self::FOUR_STAR_BASE_RATE
            };

            if (random_int(1, 10000) <= $fiveStarOdds * 10000) {
                $isStandard = !$fiveStarGuaranteed && (random_int(1, 10000) <= 5000);
                
                $drawnItem          = $isStandard ? $standardItems->random() : $featuredItem;
                $fiveStarGuaranteed = $isStandard; // Next 5-star is guaranteed if standard was pulled
                $fivePityCount      = 0;
                $fourPityCount      = $fourPityCount >= 10 ? 0 : $fourPityCount + 1;
            } 
            elseif (random_int(1, 10000) <= $fourStarOdds * 10000) {
                $drawnItem     = $fourStarPool->random();
                $fourPityCount = 0;
                $fivePityCount++;
            } 
            else {
                $drawnItem = $threeStarPool->random();
                $fourPityCount++;
                $fivePityCount++;
            }

            $itemType  = $drawnItem instanceof Character ? 'character' : 'lightcone';
            $results[] = $drawnItem;

            $pullsLog[] = [
                'user_id'    => $user->id,
                'item_type'  => $itemType,
                'item_id'    => $drawnItem->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->saveUserItem($user->id, $itemType, $drawnItem->id);
        }

        UserPull::insert($pullsLog);

        $fourPity->update(['pity' => $fourPityCount]);
        $fivePity->update([
            'pity'       => $fivePityCount,
            'guaranteed' => $fiveStarGuaranteed,
        ]);

        return [
            'pulls'        => $results,
            'fiveStarPity' => $fivePityCount,
            'fourStarPity' => $fourPityCount,
        ];
    }

    private function saveUserItem(int $userId, string $itemType, int $itemId): void
    {
        $isCharacter = $itemType === 'character';
        $modelClass  = $isCharacter ? UserCharacter::class : UserLightcone::class;
        $foreignKey  = $isCharacter ? 'character_id' : 'lightcone_id';

        $record = $modelClass::firstOrNew([
            'user_id'   => $userId,
            $foreignKey => $itemId,
        ]);

        if ($record->exists) {
            $record->increment('copies_available');
        } else {
            $record->copies_available = 0;
            $record->save();
        }
    }
}