<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRelic;
use App\Models\UserCharacter;
use App\Services\CharacterStatsCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserItemsController extends Controller
{

    public function __construct(
        private readonly CharacterStatsCalculator $statsCalculator
    ) {}

    private const STAT_RELATIONS = [
        'character.stats',
        'lightcone.lightcone.stats',
        'head.stats.stat',
        'hands.stats.stat',
        'body.stats.stat',
        'feet.stats.stat',
        'planarSphere.stats.stat',
        'linkRope.stats.stat',
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        $userLightcones = $user->lightcones()
            ->with(['lightcone.stats', 'lightcone.skill'])
            ->get()
            ->map(function ($userLightcone) {
                $lightcone = $userLightcone->lightcone;

                return [
                    ...$lightcone->toArray(),
                    'stats' => $lightcone->getStatsAtLevel(
                        $userLightcone->level
                    ),
                    'user_lightcone_id' => $userLightcone->id,
                    'level' => $userLightcone->level,
                    'superimposition' => $userLightcone->superimposition,
                    'copies_available' => $userLightcone->copies_available,
                ];
            })
            ->sortByDesc('rarity')
            ->values();

        $relicRelations = [
            'stats.stat',
            'relicPiece.type',
            'relicPiece.relicSet',
            'planarPiece.type',
            'planarPiece.planarSet',
        ];

        $userRelics = $user->relics()
            ->with($relicRelations)
            ->get()
            ->map(fn ($userRelic) => $this->formatRelic($userRelic))
            ->sortByDesc('level')
            ->values();

        $userCharacters = $user->characters()
            ->with([
                'character.eidolons',
                'character.stats',
                'lightcone.lightcone.stats',
                'head.stats.stat',
                'head.relicPiece.type',
                'head.relicPiece.relicSet',
                'hands.stats.stat',
                'hands.relicPiece.type',
                'hands.relicPiece.relicSet',
                'body.stats.stat',
                'body.relicPiece.type',
                'body.relicPiece.relicSet',
                'feet.stats.stat',
                'feet.relicPiece.type',
                'feet.relicPiece.relicSet',
                'planarSphere.stats.stat',
                'planarSphere.planarPiece.type',
                'planarSphere.planarPiece.planarSet',
                'linkRope.stats.stat',
                'linkRope.planarPiece.type',
                'linkRope.planarPiece.planarSet',
            ])
            ->get()
            ->map(function ($userCharacter) use ($userLightcones) {
                $character = $userCharacter->character;

                return [
                    ...$character->toArray(),
                    'stats' => $this->statsCalculator->calculate($userCharacter),
                    'user_character_id' => $userCharacter->id,
                    'level' => $userCharacter->level,
                    'eidolon' => $userCharacter->eidolon,
                    'copies_available' => $userCharacter->copies_available,
                    'equipped_lightcone' => $userLightcones->firstWhere(
                        'user_lightcone_id',
                        $userCharacter->equipped_lightcone_id
                    ),
                    'head' => $this->formatRelic($userCharacter->head),
                    'hands' => $this->formatRelic($userCharacter->hands),
                    'body' => $this->formatRelic($userCharacter->body),
                    'feet' => $this->formatRelic($userCharacter->feet),
                    'planarSphere' => $this->formatRelic(
                        $userCharacter->planarSphere
                    ),
                    'linkRope' => $this->formatRelic(
                        $userCharacter->linkRope
                    ),
                ];
            })
            ->sortBy([
                ['rarity', 'desc'],
                ['id', 'desc'],
            ])
            ->values();

        return response()->json([
            'characters' => $userCharacters,
            'lightcones' => $userLightcones,
            'relics' => $userRelics,
        ]);
    }

    public function equipLightcone(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'user_character_id' => ['required', 'integer'],
            'user_lightcone_id' => ['nullable', 'integer'],
        ]);

        $userCharacter = $user->characters()
            ->findOrFail($data['user_character_id']);

        if ($data['user_lightcone_id'] !== null) {
            $user->lightcones()
                ->findOrFail($data['user_lightcone_id']);
        }

        $userCharacter->equipped_lightcone_id = $data['user_lightcone_id'];
        $userCharacter->save();
        $userCharacter->load(self::STAT_RELATIONS);

        return response()->json(['success' => true, 'stats' => $this->statsCalculator->calculate($userCharacter)]);
    }

    public function superimposeLightcone(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'user_lightcone_id' => ['required', 'integer'],
            'copies_used' => ['required', 'integer', 'min:1'],
        ]);

        $userLightcone = $user->lightcones()
            ->findOrFail($data['user_lightcone_id']);

        $amountEquipped = $user->characters()
            ->where('equipped_lightcone_id', $userLightcone->id)
            ->count();

        $duplicatesCurrentlyEquipped = max(0, $amountEquipped - 1);
        $availableCopies = max(
            0,
            $userLightcone->copies_available - $duplicatesCurrentlyEquipped
        );
        $ranksRemaining = max(0, 5 - $userLightcone->superimposition);
        $maximumUsable = min($availableCopies, $ranksRemaining);

        if ($data['copies_used'] > $maximumUsable) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid number of copies.',
            ], 422);
        }

        $userLightcone->copies_available -= $data['copies_used'];
        $userLightcone->superimposition += $data['copies_used'];
        $userLightcone->save();

        return response()->json([
            'success' => true,
            'superimposition' => $userLightcone->superimposition,
            'copies_available' => $userLightcone->copies_available,
        ]);
    }

    public function saveRelics(Request $request)
    {
        $user = $request->user();

        $ownedRelic = fn () => Rule::exists('user_relics', 'id')
            ->where('user_id', $user->id);

        $data = $request->validate([
            'user_character_id' => ['required', 'integer'],
            'head_id' => ['nullable', 'integer', $ownedRelic()],
            'hands_id' => ['nullable', 'integer', $ownedRelic()],
            'body_id' => ['nullable', 'integer', $ownedRelic()],
            'feet_id' => ['nullable', 'integer', $ownedRelic()],
            'planar_sphere_id' => ['nullable', 'integer', $ownedRelic()],
            'link_rope_id' => ['nullable', 'integer', $ownedRelic()],
        ]);

        $userCharacter = $user->characters()
            ->findOrFail($data['user_character_id']);

        $slotTypes = [
            'head_id' => 'Head',
            'hands_id' => 'Hands',
            'body_id' => 'Body',
            'feet_id' => 'Feet',
            'planar_sphere_id' => 'Planar Sphere',
            'link_rope_id' => 'Link Rope',
        ];

        $selectedRelicIds = collect($slotTypes)
            ->keys()
            ->map(fn ($field) => $data[$field])
            ->filter(fn ($relicId) => $relicId !== null)
            ->unique()
            ->values();

        $selectedRelics = $user->relics()
            ->with([
                'relicPiece.type',
                'planarPiece.type',
            ])
            ->whereIn('id', $selectedRelicIds)
            ->get()
            ->keyBy('id');

        foreach ($slotTypes as $field => $expectedType) {
            $relicId = $data[$field];

            if ($relicId === null) continue;

            $actualType = $selectedRelics
                ->get($relicId)
                ?->piece
                ?->type
                ?->name;

            if ($actualType !== $expectedType) {
                throw ValidationException::withMessages([
                    $field => "The selected relic must be a {$expectedType}.",
                ]);
            }
        }

        $relicSlots = collect($slotTypes)
            ->keys()
            ->mapWithKeys(fn ($field) => [$field => $data[$field]])
            ->all();

        $affectedCharacterIds = DB::transaction(function () use (
            $user,
            $userCharacter,
            $relicSlots
        ) {
            $affectedIds = collect([$userCharacter->id]);

            foreach ($relicSlots as $column => $relicId) {
                if ($relicId === null) continue;

                $previousOwnerIds = $user->characters()
                    ->where('id', '!=', $userCharacter->id)
                    ->where($column, $relicId)
                    ->pluck('id');

                $affectedIds = $affectedIds->merge($previousOwnerIds);

                $user->characters()
                    ->whereIn('id', $previousOwnerIds)
                    ->update([$column => null]);
            }

            $userCharacter->update($relicSlots);

            return $affectedIds->unique()->values();
        });

        $affectedCharacters = $user->characters()
                                    ->whereIn('id', $affectedCharacterIds)
                                    ->with(self::STAT_RELATIONS)
                                    ->get();

        $updatedCharacters = $affectedCharacters->mapWithKeys(
            fn ($character) => [
                $character->id => [
                    'stats' => $this->statsCalculator->calculate($character),
                    'relic_ids' => [
                        'head_id' => $character->head_id,
                        'hands_id' => $character->hands_id,
                        'body_id' => $character->body_id,
                        'feet_id' => $character->feet_id,
                        'planar_sphere_id' => $character->planar_sphere_id,
                        'link_rope_id' => $character->link_rope_id,
                    ],
                ],
            ]
        );

        $userCharacter->load(self::STAT_RELATIONS);

        return response()->json([
            'success' => true,
            'message' => 'Relics saved successfully.',
            'updated_characters' => $updatedCharacters,
        ]);
    }

    public function activateEidolon(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'user_character_id' => ['required', 'integer'],
            'eidolon_number' => ['required', 'integer', 'between:1,6'],
        ]);

        $userCharacter = $user->characters()
            ->findOrFail($data['user_character_id']);

        if (
            $data['eidolon_number'] !== $userCharacter->eidolon + 1 ||
            $userCharacter->copies_available <= 0
        ) {
            throw ValidationException::withMessages([
                'eidolon_number' => 'Unable to activate this Eidolon.',
            ]);
        }

        $userCharacter->eidolon++;
        $userCharacter->copies_available--;
        $userCharacter->save();

        return response()->json([
            'success' => true,
            'message' => 'Eidolon activated successfully.',
            'eidolon' => $userCharacter->eidolon,
            'copies_available' => $userCharacter->copies_available,
        ]);
    }

    private function formatRelic(?UserRelic $userRelic)
    {
        if (!$userRelic) return null;

        $piece = $userRelic->piece;
        $set = match ($userRelic->item_type) {
            'relic' => $piece?->relicSet,
            'planarOrnament' => $piece?->planarSet,
            default => throw new \UnexpectedValueException(
                        "Invalid relic item type: {$userRelic->item_type}"
                    ),
        };

        return [
            'id' => $userRelic->id,
            'piece_id' => $userRelic->piece_id,
            'item_type' => $userRelic->item_type,
            'level' => $userRelic->level,
            'status' => $userRelic->status,
            'obtained_at' => $userRelic->obtained_at,
            'piece' => $piece
                ? [
                    ...$piece->attributesToArray(),
                    'img' => $piece->img,
                    'type' => $piece->type?->name,
                    'set' => $set
                        ? [
                            ...$set->attributesToArray(),
                            'key' => "{$userRelic->item_type}-{$set->id}",
                            'highlightedFirstEffect' =>
                                $set->getHighlightedFirstEffect(),
                            'highlightedSecondEffect' =>
                                $userRelic->item_type === 'relic'
                                    ? $set->getHighlightedSecondEffect()
                                    : null,
                        ]
                        : null,
                ]
                : null,
            'mainStat' => $userRelic->mainStat,
            'subStats' => $userRelic->subStats,
        ];
    }
}
