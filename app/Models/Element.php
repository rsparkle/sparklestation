<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    use HasFactory;

    protected $appends = ['img'];

    public function getImgAttribute()
    {
        return asset(("images/elements/$this->name.webp"));
    }

    public function getColorAttribute()
    {
        switch($this->name) {
            case "Lightning":
                return "#E385F1";
            case "Fire":
                return "#F45D53";
            case "Ice":
                return "#55A3D5";
            case "Imaginary":
                return "#F1E596";
            case "Physical":
                return "#E6E6E6";
            case "Quantum":
                return "#8576CD";
            case "Wind":
                return "#66C38C";
        }
    }
}
