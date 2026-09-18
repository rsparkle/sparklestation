<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRelic;
use App\Models\UserRelicStat;
use App\Models\Relic;
use App\Models\PlanarOrnament;
use App\Models\RelicPiece;
use App\Models\PlanarOrnamentPiece;
use App\Models\RelicStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RelicInventoryController extends Controller
{
    public function index() 
    {
        $user = auth()->user();

        if (!$user) return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);

        $relicList = UserRelic::with([
                'relicPiece.type',
                'relicPiece.relicSet',
                'planarPiece.type',
                'planarPiece.planarSet',
                'stats.stat'
            ])
            ->where('user_id', $user->id)
            ->get();

        $setList = [
            'relics' => Relic::all()->sortByDesc('id')->map(function($relic) {
                return [
                    'id' => $relic->id,
                    'name' => $relic->name,
                    'slug' => $relic->slug,
                    'img' => $relic->img,
                    'first_effect' => $relic->first_effect,
                    'second_effect' => $relic->second_effect,
                    'set_bonus' => $relic->getSetBonus(),
                ];
            })->values()->toArray(),
            'planarOrnaments' => PlanarOrnament::all()->sortByDesc('id')->map(function($planar) {
                return [
                    'id' => $planar->id,
                    'name' => $planar->name,
                    'slug' => $planar->slug,
                    'img' => $planar->img,
                    'first_effect' => $planar->effect,
                    'set_bonus' => $planar->getSetBonus(),
                ];
            })->values()->toArray(),
        ];

        return response()->json([
            'relicList' => $relicList,
            'setList' => $setList
        ]);
    }

    private function retrieveStatRoll($stat): float
    {
        $roll = mt_rand(1, 100);

        if ($roll <= 10) {
            return $stat->sub_high;
        } elseif ($roll <= 30) {
            return $stat->sub_med;
        } else {
            return $stat->sub_low;
        }
    }

    public function generateRelics(Request $request)
    {
        $user = auth()->user();

        if (!$user) return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);

        $data = $request->validate([
            'relic' => 'required|exists:relics,id',
            'planarOrnament' => 'required|exists:planar_ornaments,id'
        ]);

        $items = [
            'relic' => RelicPiece::class,
            'planarOrnament' => PlanarOrnamentPiece::class
        ];

        $columns = [
            'relic' => 'relic_id',
            'planarOrnament' => 'planar_ornament_id',
        ];

        $generatedItems = [];

        foreach ($items as $key => $modelClass) {
            // Generate random relic pieces
            $roll = mt_rand(1, 100);
            $numberOfRelics = 4;
            if ($roll <= 80) $numberOfRelics = 3;
            
            for ($i = 0; $i < $numberOfRelics; $i++) {
                $relicPiece = $modelClass::where($columns[$key], $data[$key])
                            ->inRandomOrder()
                            ->first();

                // Generate mainstats
                $mainStatName = $relicPiece->randomMainStat();
                // Generate substats
                $subStatNames = $relicPiece->randomSubStats($mainStatName);

                // Create entities
                $userRelic = UserRelic::create([
                    'user_id' => $user->id,
                    'piece_id' => $relicPiece->id,
                    'item_type' => $key
                ]);
                $generatedItems[] = $userRelic;

                $allStatNames = array_merge([$mainStatName], $subStatNames);
                $lineOrder = 0;

                foreach($allStatNames as $statName) {
                    $stat = RelicStat::where('name', $statName)->first();
                    
                    if (!$stat) continue;

                    if ($lineOrder == 0) {
                        $value = $stat->main_stat_value;
                    } else {
                        $value = $this->retrieveStatRoll($stat);
                    }   
                    $isHidden = ($lineOrder == 4 && mt_rand(1, 100) <= 80) ? 1 : 0;

                    UserRelicStat::create([
                        'user_relic_id' => $userRelic->id,
                        'stat_id' => $stat->id,
                        'value' => $value,
                        'is_main' => $lineOrder === 0 ? 1 : 0,
                        'line_order' => $lineOrder,
                        'is_hidden' => $isHidden
                    ]);
                    $lineOrder++;
                }
            }
        }

        // Load relationships for the modal
        foreach ($generatedItems as $item) {
            $item->load([
                'relicPiece.type', 
                'relicPiece.relicSet',
                'planarPiece.type', 
                'planarPiece.planarSet',
                'stats.stat'
            ]);
        }

        return response()->json([
            'success' => true,
            'relics' => $generatedItems,
        ]);
    }

    public function update(Request $request, UserRelic $userRelic)
    {
        abort_unless($userRelic->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'level' => ['sometimes', 'integer', 'between:0,15'],
            'status' => [
                'sometimes',
                Rule::in(['none', 'locked', 'discarded']),
            ],
            'stats' => ['sometimes', 'array'],
            'stats.*.id' => ['required', 'integer'],
            'stats.*.value' => ['sometimes', 'numeric'],
            'stats.*.rolls' => ['sometimes', 'integer', 'min:0'],
            'stats.*.is_hidden' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(function () use ($userRelic, $data) {
            $userRelic->update(
                collect($data)->only(['level', 'status'])->all()
            );

            foreach ($data['stats'] ?? [] as $statData) {
                $stat = $userRelic->stats()
                    ->whereKey($statData['id'])
                    ->firstOrFail();

                $stat->update(
                    collect($statData)
                        ->only(['value', 'rolls', 'is_hidden'])
                        ->all()
                );
            }
        });

        return response()->json(['success' => true]);
    }
}