<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRelicStat extends Model
{
    protected $table = 'user_relic_stats';

    public $timestamps = false;

    protected $fillable = [
        'user_relic_id',
        'stat_id',
        'value',
        'rolls',
        'is_main',
        'line_order',
        'is_hidden'
    ];

    public function userRelic(): BelongsTo
    {
        return $this->belongsTo(UserRelic::class, 'user_relic_id');
    }

    public function stat(): BelongsTo
    {
        return $this->belongsTo(RelicStat::class, 'stat_id');
    }
}