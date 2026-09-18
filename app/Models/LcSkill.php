<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HighlightNumbersTrait;
use App\Traits\ExtractNumbersTrait;


class LcSkill extends Model
{
    use HasFactory;
    use HighlightNumbersTrait;
    use ExtractNumbersTrait;

    protected $casts = [
        'positions' => 'array', 
        'differences' => 'array'
    ];

    protected $appends = [
        'descriptions_by_superimposition',
    ];

    /**
     * Get the description attribute with numbers wrapped in <span> tags.
     *
     * This accessor processes the raw 'description' attribute and wraps all
     * numeric values in a <span> element for styling or formatting purposes
     *
     * @param  string  $value  The raw description value from the database
     * @return string          The processed HTML string with numbers wrapped in <span>
     */
    public function getDescriptionAttribute($value)
    {
        return $this->highlightNumbers($value);
    }

    /**
     * Apply the level differences to specific numbers in the description.
     *
     * Each position identifies a number in the description, while its corresponding
     * difference determines how much that number increases per level
     *
     * @param  string  $description  The raw description from the database
     * @param  int     $level        The skill level used for the calculation
     * @return string                The description with updated numeric values
     */
    private function applySuperimpositionDifferences($description, $level)
    {
        $changes = array_combine(
            $this->positions ?? [],
            $this->differences ?? []
        );

        $numberIndex = 0;

        return preg_replace_callback(
            '/-?\d+(?:\.\d+)?/',
            function ($match) use (&$numberIndex, $changes, $level) {
                $numberIndex++;

                if (!array_key_exists($numberIndex, $changes)) {
                    return $match[0];
                }

                $value = (float) $match[0]
                    + ((float) $changes[$numberIndex] * ($level - 1));

                return rtrim(
                    rtrim(number_format($value, 10, '.', ''), '0'),
                    '.'
                );
            },
            $description
        );
    }

    /**
     * Get the description for a specific skill level.
     *
     * This method updates the affected numeric values and then wraps all numbers
     * in span elements for frontend styling
     *
     * @param  int     $level  The skill level used for the description
     * @return string          The processed description for the selected level
     */
    public function descriptionAtSuperimposition($level)
    {
        $description = $this->getRawOriginal('description');

        $description = $this->applySuperimpositionDifferences(
            $description,
            $level
        );

        return $this->highlightNumbers($description);
    }

    /**
     * Get the processed descriptions for every skill level.
     *
     * This method generates a description for each available level so the frontend
     * can switch between them without performing the calculations itself
     *
     * @param  int    $maximumLevel  The maximum skill level to generate
     * @return array                 The descriptions indexed by skill level
     */
    public function descriptionsBySuperimposition($maximumLevel = 5)
    {
        return collect(range(1, $maximumLevel))
            ->mapWithKeys(function ($level) {
                return [
                    $level => $this->descriptionAtSuperimposition($level)
                ];
            })
            ->toArray();
    }

    public function getDescriptionsBySuperimpositionAttribute()
    {
        return $this->descriptionsBySuperimposition();
    }
}