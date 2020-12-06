<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $guarded = [];

    use HasFactory;


    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function detach()
    {
        $this->users()->detach(auth()->user());
    }
}
