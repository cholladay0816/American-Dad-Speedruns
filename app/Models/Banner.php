<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $guarded = [];

    use HasFactory;

    public function uploader()
    {
        return $this->hasOne(User::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
