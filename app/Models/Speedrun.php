<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Speedrun extends Model
{
    use HasFactory;
    protected $guarded = ['verified'];
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class);
    }

    public function category()
    {
        if($this->categories->count() == 0)
            $this->categories()->attach(1);
        return $this->categories()->first();
    }
    public function platform()
    {
        if($this->platforms->count() == 0)
            $this->platforms()->attach(1);
        return $this->platforms()->first();
    }

    public function canDelete()
    {
        if(auth()->guest())
            return false;
        return $this->user_id == auth()->user()->id || Gate::allows('manage_speedruns');
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
        if($this->verified == 0)
            return Speedrun::where('verified','1')->get()->count();
        $runs = Speedrun::where('time', '<' ,$this->time)->where('verified','1')->get();
        $count = 0;
        foreach ($runs as $run)
        {
            if($run->disqualified())
                continue;

            //if($run->category()->id == $this->category()->id && $run->platform()->id == $this->platform()->id)
            //    $count++;

            if($run->category()->id == $this->category()->id)
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
        return $this->verified==1?'Verified':'Submitted';
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
