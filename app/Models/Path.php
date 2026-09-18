<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Path extends Model
{
    use HasFactory;

    protected $appends = ['img'];

    public function getImgAttribute()
    {
        return asset(("images/paths/$this->name.webp"));
    }
}
