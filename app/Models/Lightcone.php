<?php

namespace App\Models;

use App\Models\Path;
use App\Models\LightconeStat;
use App\Traits\StatsCalculationTrait;
use App\Traits\HasFuzzyRouteBinding;
use App\Traits\WordMatchTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lightcone extends Model
{
    use HasFactory;
    use StatsCalculationTrait;
    use HasFuzzyRouteBinding;
    use WordMatchTrait;

    public $timestamps = false;

    protected $appends = ['img', 'artwork_img', 'path_img', 'icon_img'];

    protected $fillable = ['name', 'slug', 'path'];

    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }
    
    public function stats(): HasOne
    {
        return $this->hasOne(LcStat::class);
    }

    public function patch(): BelongsTo
    {
        return $this->belongsTo(Patch::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(LcSkill::class);
    }

    public function getSkillValues()
    {
        if (!$this->skill) {
            return [];
        };

        return array_map(
            fn($diff, $pos) => ["difference" => $diff, "position" => $pos], 
            $this->skill->differences,
            $this->skill->positions
        );
    }

    public function enemyMaterials(): HasMany
    {
        return $this->hasMany(Material::class, 'material_group', 'enemy_material_group')
                    ->orderBy('rarity', 'asc');
    }

    public function getPathMaterials() 
    {
        $patch = ($this->patch->number ?? 0) < 2 ? 1 : 2;

        $material_group = Material::where('type_id', 6)
            ->where('rarity', 2) 
            ->where('path_id', $this->path->id)
            ->where('patch', $patch)
            ->value('material_group');

        return Material::where('material_group', $material_group)
                        ->orderBy('rarity', 'asc')
                        ->get();
    }

    /**
     * Calculate the stats multiplier for a specific level
     * 
     * @param int $level Lightcone level (1-80)
     * @return float The combined multiplier for stats at this level
     */
    private function calculateLevelMultiplier($level) 
    {
        // Base level scaling
        $levelMultiplier = 0.15 * $level + 0.85;
        
        // Add ascension bonuses for levels 20, 30, 40, 50, 60, 70, 80
        $ascensionBonus = 0;
        if ($level >= 20) {
            $ascensionBonus = 1.2;
            $ascensionBonus += (floor($level/10) - 2) * 1.6;
        }
        
        return $levelMultiplier + $ascensionBonus;
    }

    public function getLevelUpMaterials()
    {
        $materials = [
            "credits" => Material::where('name', 'Credits')->first(),
            "path" => $this->getPathMaterials()->all(),
            "enemy" => $this->enemyMaterials()->get()->all()
        ];

        // Material requirements by rarity [credits, path_material, enemy_material]
        $materialTables = [
            3 => [
                1 => [3000, 0, 4],      // credits, no path material, enemy[0]
                2 => [6000, 2, 8],      // credits, path[0], enemy[0]
                3 => [12000, 2, 4],     // credits, path[1], enemy[1]
                4 => [30000, 4, 6],     // credits, path[1], enemy[1]
                5 => [60000, 3, 3],     // credits, path[2], enemy[2]
                6 => [120000, 6, 5],    // credits, path[2], enemy[2]
            ],
            4 => [
                1 => [4000, 0, 5],
                2 => [8000, 3, 10],
                3 => [16000, 3, 6],
                4 => [40000, 6, 9],
                5 => [80000, 4, 5],
                6 => [160000, 8, 7],
            ],
            5 => [
                1 => [5000, 0, 8],
                2 => [10000, 4, 12],
                3 => [20000, 4, 8],
                4 => [50000, 8, 12],
                5 => [100000, 5, 6],
                6 => [200000, 10, 8],
            ],
        ];

        $rarity = $this->rarity;
        $levelMaterials;
        foreach ($materialTables[$rarity] as $breakthroughIndex=>$rowMats) {
            $matRarity = $breakthroughIndex <= 2 ? 0 : ($breakthroughIndex <= 4 ? 1 : 2);

            $levelMaterials[$breakthroughIndex] = [
                ['quantity' => $rowMats[0], 'material' => $materials['credits']]
            ];

            if ($rowMats[1] > 0) {
                $levelMaterials[$breakthroughIndex][] = [
                    'quantity' => $rowMats[1],
                    'material' => $materials['path'][$matRarity]
                ];
            }

            $levelMaterials[$breakthroughIndex][] = [
                'quantity' => $rowMats[2],
                'material' => $materials['enemy'][$matRarity]
            ];
        }

        return $levelMaterials;
    }

    public function getIconImgAttribute() 
    {
        return asset("images/lightcones/{$this->slug}-icon.webp");
    }
    
    public function getArtworkImgAttribute() 
    {
        return asset("images/lightcones/{$this->slug}-artwork.webp");
    }
    
    public function getImgAttribute() 
    {
        return asset("images/lightcones/{$this->slug}.webp");
    }
    
    public function getVideoAttribute() 
    {
        return asset("images/lightcones/{$this->slug}.mp4");
    }

    public function getRarityBackgroundImgAttribute()
    {
        return asset('images/filter/' . $this->rarity . 'star.webp');
    }

    public function getPathImgAttribute() 
    {
        return asset('images/paths/' . ucfirst($this->path->name) . '.webp');
    }

    protected function baseQuery()
    {
        return self::with([
            'path', 'stats'
        ]);
    }
}
