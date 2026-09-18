<?php

namespace App\Models;

use App\Models\Patch;
use App\Models\Element;
use App\Models\Path;
use App\Models\Faction;
use App\Models\Story;
use App\Models\CharStat;
use App\Models\CharAbility;
use App\Models\Material;
use App\Traits\StatsCalculationTrait;
use App\Traits\HasFuzzyRouteBinding;
use App\Traits\WordMatchTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Character extends Model
{
    use HasFactory;
    use StatsCalculationTrait;
    use HasFuzzyRouteBinding;
    use WordMatchTrait;

    public $timestamps = false;

    protected $appends = ['splash_img', 'path_img', 'element_img', 'icon_img'];

    protected $fillable = ['name', 'slug', 'element', 'path', 'faction', 'gender'];

    protected $casts = [
        'unique_buff_targets' => 'array'
    ];

    // Auto-generate slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($character) {
            $character->slug = Str::slug($character->name);
        });
        
        static::updating(function ($character) {
            if ($character->isDirty('name')) {
                $character->slug = Str::slug($character->name);
            }
        });
    }

    public function getUniqueBuffTargetsListAttribute()
    {
        $targets = Character::whereIn('id', $this->unique_buff_targets ?? [])
            ->select('id', 'name', 'slug')
            ->get();

        $abilities = CharAbility::where('character_id', $this->id)
            ->whereIn('target_character_id', $targets->pluck('id'))
            ->get()
            ->keyBy('target_character_id');

        return $targets->map(function ($target) use ($abilities) {
            $ability = $abilities->get($target->id);

            return [
                'id' => $target->id,
                'slug' => $target->slug,
                'name' => $target->name === 'Dan Heng • Permansor Terrae'
                    ? 'Dan Heng'
                    : $target->name,
                'buff_title' => $ability?->name,
                'buff_description' => $ability?->description,
            ];
        })->all();
    }

    public function patch(): BelongsTo
    {
        return $this->belongsTo(Patch::class);
    }
    
    public function element(): BelongsTo
    {
        return $this->belongsTo(Element::class);
    }
    
    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }
    
    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }    

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function stats(): hasOne
    {
        return $this->hasOne(CharStat::class);
    }

    public function traces(): hasMany
    {
        return $this->hasMany(Trace::class)->orderBy('position');
    }

    public function eidolons(): hasMany
    {
        return $this->hasMany(Eidolon::class)->orderBy('eidolon_number');
    }

    public function abilities(): hasMany
    {
        return $this->hasMany(CharAbility::class);
    }

    public function basicAtk()
    {
        return $this->abilities()->ofType('Basic ATK');
    }

    public function skill()
    {
        return $this->abilities()->ofType('Skill');
    }

    public function ultimate()
    {
        return $this->abilities()->ofType('Ultimate');
    }

    public function talent()
    {
        return $this->abilities()->ofType('Talent');
    }

    public function technique()
    {
        return $this->abilities()->ofType('Technique');
    }

    /**
     * Calculate the stats multiplier for a specific level
     * 
     * @param int $level Character level (1-80)
     * @return float The combined multiplier for stats at this level
     */
    private function calculateLevelMultiplier($level) 
    {
        // Base level scaling
        $levelMultiplier = 0.05 * $level + 0.95;
        
        // Add ascension bonuses for levels 20, 30, 40, 50, 60, 70, 80
        $ascensionBonus = 0;
        if ($level >= 20) {
            $ascensionBonus = (floor($level/10) - 1) * 0.4;
        }
        
        return $levelMultiplier + $ascensionBonus;
    }

    public function enemyMaterials(): HasMany
    {
        return $this->hasMany(Material::class, 'material_group', 'enemy_material_group')
                    ->orderBy('rarity', 'asc');
    }

    public function ascensionMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function advancedTrace(): BelongsTo
    {
        return $this->belongsTo(Material::class);
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

    public function getlevelUpMaterials()
    {
        // Prepare the credits
        $credits = Material::where('name', 'Credits')->first();
        
        // Prepare the enemy materials
        $enemyMaterials = $this->enemyMaterials()->get();

        // Merge the arrays
        $materials = array_merge(
            [$credits, $this->ascensionMaterial],
            $enemyMaterials->all()
        );

        return [
            1 => [
                ['quantity' => 4000, 'material' => $materials[0]]
            ],
            2 => [
                ['quantity' => 12000, 'material' => $materials[0]],
                ['quantity' => 1, 'material' => $materials[2]]
            ],
            3 => [
                ['quantity' => 28000, 'material' => $materials[0]],
                ['quantity' => 3, 'material' => $materials[1]],
                ['quantity' => 15, 'material' => $materials[2]],
                ['quantity' => 6, 'material' => $materials[3]],
            ],
            4 => [
                ['quantity' => 68000, 'material' => $materials[0]],
                ['quantity' => 10, 'material' => $materials[1]],
                ['quantity' => 15, 'material' => $materials[2]],
                ['quantity' => 15, 'material' => $materials[3]],
            ],
            5 => [
                ['quantity' => 148000, 'material' => $materials[0]],
                ['quantity' => 30, 'material' => $materials[1]],
                ['quantity' => 15, 'material' => $materials[2]],
                ['quantity' => 15, 'material' => $materials[3]],
                ['quantity' => 6, 'material' => $materials[4]],
            ],
            6 => [
                ['quantity' => 308000, 'material' => $materials[0]],
                ['quantity' => 65, 'material' => $materials[1]],
                ['quantity' => 15, 'material' => $materials[2]],
                ['quantity' => 15, 'material' => $materials[3]],
                ['quantity' => 15, 'material' => $materials[4]],
            ],
        ];
    }

    public function getTraceMaterials()
    {
        $materials = [
            "credits" => Material::where('name', 'Credits')->first(),
            "tracks_of_destiny" => Material::where('name', 'Tracks of Destiny')->first(),
            "advanced_trace" => $this->advancedTrace,
            "path" => $this->getPathMaterials()->all(),
            "enemy" => $this->enemyMaterials()->get()->all()
        ];

        $charRarityIndex = $this->rarity === 5 ? 1 : 0;

        $config = config('trace_costs');
        $baseCosts = $config['base_costs'];
        $abilityCosts = $config['ability_upgrade_costs'];
        $creditMultiplier = $config['creditMultiplier'][$charRarityIndex];

        $finalCosts = [];

        foreach ($baseCosts as $traceKey => $costs) {
            $finalCosts[$traceKey] = processCostEntry($costs, $materials, $creditMultiplier, $charRarityIndex);
        }

        foreach ($abilityCosts as $skillLevel => $skillMats) {
            $finalCosts[$skillLevel] = processCostEntry($skillMats, $materials, $creditMultiplier, $charRarityIndex);
        }

        return $finalCosts;
    }

    public function getTotalMinorTraces() 
    {
        $total = [];

        foreach ($this->traces as $trace) {
            // Skip main traces
            if (in_array($trace->position, [1,5,9,13])) {
                continue;
            }

            // Extract stat name (text before 'increases')
            $pos = strpos($trace->description, 'increases');
            $stat = $pos !== false ? substr($trace->description, 0, $pos-1) : $trace->description;

            // Extract numeric value and check if it's a percentage
            if (preg_match('/^(.*?)(\d+(\.\d+)?)(%?)$/', $trace->description, $matches)) {
                $number = (float)$matches[2];
                $isPercent = $matches[4] === '%';
            } else {
                $number = 0;
                $isPercent = false;
            }

            // Add new stat or sum with existing
            if (!isset($total[$stat])) {
                $total[$stat] = [
                    'value' => $number,
                    'is_percent' => $isPercent
                ];
            } else {
                $total[$stat]['value'] += $number;
                if ($isPercent) $total[$stat]['is_percent'] = true;
            }
        }

        // Format final output (append % if needed)
        foreach ($total as $stat => $data) {
            $total[$stat] = $data['value'] . ($data['is_percent'] ? '%' : '');
        }

        return $total;
    }

    public function getMinorTraceImg($stat) 
    {
        // Map substrings to image filenames
        $map = [
            "HP" => "global-hp.webp",
            "ATK" => "global-atk.webp",
            "DEF" => "global-def.webp",
            "RES" => "global-res.webp",
            "Effect" => "global-effect.webp",
            "Rate" => "global-cr.webp",
            "CRIT DMG" => "global-cd.webp",
            "Break" => "global-break.webp",
            "SPD" => "global-spd.webp",
            "Fire" => "global-fire.webp",
            "Quantum" => "global-quantum.webp",
            "Imaginary" => "global-imaginary.webp",
            "Ice" => "global-ice.webp",
            "Lightning" => "global-lightning.webp",
            "Physical" => "global-physical.webp",
            "Wind" => "global-wind.webp",
        ];

        // Loop through the map and return the first match
        foreach ($map as $key => $image) {
            if (str_contains($stat, $key)) {
                return $image;
            }
        }

        return 'default.webp';
    }

    public function getIconImgAttribute() 
    {
        $basePath = "images/characters/{$this->slug}";
 
        return asset("$basePath-icon.webp");
    }

    public function getSplashImgAttribute() 
    {
        $basePath = "images/characters/{$this->slug}";
 
        return asset("$basePath-splash-image.webp");
    }

    public function getSplashBgImgAttribute() 
    {
        $basePath = "images/characters/{$this->slug}";
 
        return asset("$basePath-splash-bg.webp");
    }

    public function getModelImgAttribute() 
    {
        $basePath = "images/characters/{$this->slug}";
 
        return asset("$basePath-model.webp");
    }

    public function getRarityBackgroundImgAttribute()
    {
        return asset($this->rarity == 4 ? 'images/filter/4star.webp' : 'images/filter/5star.webp');
    }

    public function getElementImgAttribute() 
    {
        return asset('images/elements/' . ucfirst($this->element->name) . '.webp');
    }
    
    public function getPathImgAttribute() 
    {
        return asset('images/paths/' . ucfirst($this->path->name) . '.webp');
    } 
    
    public function getUniqueBuffsImgAttribute()
    {
        return asset('images/abilities/' . $this->slug . '-unique-buffs.webp');
    }

    public function getUniqueBuffsModalImgAttribute()
    {
        return asset('images/modal/' . $this->slug . '/modal.png');
    }

    public function getUniqueBuffsSelectorImgAttribute()
    {
        return asset('images/modal/' . $this->slug . '/selector.png');
    }

    public function getUniqueBuffsSelectorSelectedImgAttribute()
    {
        return asset('images/modal/' . $this->slug . '/selector-selected.png');
    }

    public function uniqueBuffTargetCard(string $slug): string
    {
        return asset("images/modal/{$this->slug}/{$slug}-card.png");
    }

    public function uniqueBuffTargetIcon(string $slug): string
    {
        return asset("images/modal/{$this->slug}/{$slug}-icon.png");
    }

    public function getFormattedStoryParts()
    {
        return [
            ['story' => 'Character Details', 'description' => $this->story->char_details],
            ['story' => 'Part I',            'description' => $this->story->part_one],
            ['story' => 'Part II',           'description' => $this->story->part_two],
            ['story' => 'Part III',          'description' => $this->story->part_three],
            ['story' => 'Part IV',           'description' => $this->story->part_four],
        ];
    }

    protected function baseQuery()
    {
        return self::with([
            'element', 'path', 'faction', 'stats', 'story',
            'traces', 'abilities.ability', 'abilities.type', 'enemyMaterials'
        ]);
    }
};
