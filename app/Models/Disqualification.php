<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disqualification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $touches = ['speedrun'];

    public function speedrun()
    {
        return $this->belongsTo(Speedrun::class);
    }
}
