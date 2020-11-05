<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speedrun extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsToMany(Category::class)->firstOrFail();
    }

    public function platform()
    {
        return $this->belongsToMany(Platform::class)->firstOrFail();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
