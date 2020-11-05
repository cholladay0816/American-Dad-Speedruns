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

    public function placement()
    {
        $runs = Speedrun::where('time', '<' ,$this->time);
        $count = 0;
        foreach ($runs as $run)
        {
            if($run->category()->id == $this->category()->id && $run->platform()->id == $this->platform()->id)
                $count++;
        }
        return $count + 1;
    }
}
