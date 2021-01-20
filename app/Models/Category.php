<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $touches = ['speedruns'];

    protected $guarded = [];

    public function speedruns()
    {
        return $this->belongsToMany(Speedrun::class);
    }
}
