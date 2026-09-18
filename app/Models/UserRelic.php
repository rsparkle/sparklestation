<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRelic extends Model
{
    protected $table = 'user_relics';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'piece_id',
        'item_type',
        'level',
        'status',
        'obtained_at'
    ];

    protected $appends = ['piece', 'mainStat', 'subStats', 'img', 'level'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function relicPiece(): BelongsTo
    {
        return $this->belongsTo(RelicPiece::class, 'piece_id');
    }

    public function planarPiece(): BelongsTo
    {
        return $this->belongsTo(PlanarOrnamentPiece::class, 'piece_id');
    }

    public function stats() : HasMany
    {
        return $this->hasMany(UserRelicStat::class, 'user_relic_id');
    }

    public function getMainStatAttribute()
    {
        $mainStat = $this->stats->where('is_main', 1)->first();
        if (!$mainStat) return null;
        
        return [
            'id' => $mainStat->id,
            'stat_type' => $mainStat->stat->name,
            'value' => $mainStat->value
        ];
    }

    public function getSubStatsAttribute()
    {
        return $this->stats->where('is_main', 0)->map(function($stat) {
            return [
                'id' => $stat->id,
                'stat_type' => $stat->stat->name,
                'value' => $stat->value,
                'rolls' => $stat->rolls,
                'isHidden' => $stat->is_hidden
            ];
        })->values();
    }

    public function getPieceAttribute()
    {
        if ($this->item_type == 'relic') {
            return $this->relicPiece;
        } else {
            return $this->planarPiece;
        }
    }

    public function getLevelAttribute($value)
    {
        return $this->attributes['level'] ?? 0;
    }

    public function getImgAttribute()
    {
        return $this->piece?->img;
    }
}