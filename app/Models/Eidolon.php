<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HighlightNumbersTrait;

class Eidolon extends Model
{
    use HasFactory;
    use HighlightNumbersTrait;

    public $timestamps = false;

    protected $appends = ['img', 'icon_img'];

    protected $fillable = [
        'character_id',
        'eidolon_number',
        'name',
        'description',
    ];

    public function character(): BelongsTo
    {   
        return $this->belongsTo(Character::class);
    }

    public function getImgAttribute()
    {
        return asset('images/eidolons/' .
                    $this->character->slug . '-' .
                    str_pad($this->eidolon_number, 2, '0', STR_PAD_LEFT) .
                    '-art.webp');
    }

    public function getIconImgAttribute()
    {
        return asset('images/eidolons/' .
                    $this->character->slug . '-' .
                    str_pad($this->eidolon_number, 2, '0', STR_PAD_LEFT) .
                    '-icon.webp');
    }

    /**
     * Get the description attribute with numbers wrapped in <span> tags.
     *
     * This accessor processes the raw 'description' attribute and wraps all
     * numeric values in a <span> element for styling or formatting purposes.
     *
     * @param  string  $value  The raw description value from the database
     * @return string          The processed HTML string with numbers wrapped in <span>
     */
    public function getDescriptionAttribute($value)
    {
        return $this->highlightNumbers($value);
    }
}
