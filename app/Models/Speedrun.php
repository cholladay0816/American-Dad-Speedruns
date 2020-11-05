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

    public function embed_url()
    {
        $id = substr( $this->url, 17);
        return 'https://www.youtube.com/embed/'.$id;
    }

    public function placement()
    {
        if($this->disqualified())
            return Speedrun::where('verified','1')->get()->count();
        $runs = Speedrun::where('time', '<' ,$this->time)->where('verified','1')->get();
        $count = 0;
        foreach ($runs as $run)
        {
            if($run->disqualified())
                continue;

            if($run->category()->id == $this->category()->id && $run->platform()->id == $this->platform()->id)
                $count++;
        }
        return $count + 1;
    }

    public function disqualification()
    {
        return $this->hasOne(Disqualification::class);
    }

    public function disqualified()
    {
        return $this->disqualification != null;
    }

    public function status()
    {
        if($this->disqualified())
            return 'Disqualified';
        return 'Active';
    }
}
