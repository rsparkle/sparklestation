<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Character;
use App\Models\UserLightcone;
use App\Models\UserRelic;

class UserCharacter extends Model
{
    protected $fillable = [
        'user_id',
        'character_id',
        "equipped_lightcone_id",
        "head_id",
        "hands_id",
        "body_id",
        "feet_id",
        "planar_sphere_id",
        "link_rope_id",
        'eidolon',
        'copies_available',
        'level',
    ];

    protected $casts = [
        'eidolon' => 'integer',
        'copies_available' => 'integer',
    ];

    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class);
    }

    public function character(): BelongsTo 
    {
        return $this->belongsTo(Character::class);
    }

    public function lightcone(): BelongsTo
    {
        return $this->belongsTo(UserLightcone::class, 'equipped_lightcone_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(UserRelic::class, 'head_id');
    }

    public function hands(): BelongsTo
    {
        return $this->belongsTo(UserRelic::class, 'hands_id');
    }

    public function body(): BelongsTo
    {
        return $this->belongsTo(UserRelic::class, 'body_id');
    }

    public function feet(): BelongsTo
    {
        return $this->belongsTo(UserRelic::class, 'feet_id');
    }

    public function planarSphere(): BelongsTo
    {
        return $this->belongsTo(UserRelic::class, 'planar_sphere_id');
    }

    public function linkRope(): BelongsTo
    {
        return $this->belongsTo(UserRelic::class, 'link_rope_id');
    }
}