<?php

namespace App\Models;

use App\Cacheable;
use App\Mail\DisqualifiedRun;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class Speedrun extends Model
{
    use Cacheable;
    use HasFactory;
    protected $guarded = [];

    public function title()
    {
        return '['.str_ordinal($this->placement()).'] '.$this->category()->title." by ".$this->user->name." in ".$this->time."s";
    }

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
    public function getCategoryAttribute()
    {
        return $this->category();
    }
    public function platform()
    {
        if($this->platforms->count() == 0)
            $this->platforms()->attach(1);
        return $this->platforms()->first();
    }
    public function getPlatformAttribute()
    {
        return $this->platform();
    }

    public function canDelete()
    {
        if(auth()->guest())
            return false;
        return $this->user_id == auth()->user()->id || Gate::allows('manage_speedruns');
    }
    public function canVerify()
    {
        if(auth()->guest())
            return false;
        return Gate::allows('manage_speedruns');
    }

    public function videoExists()
    {
        $file = @file_get_contents('https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=' . $this->video_id());
        return $file != 'Not Found';
    }
    public function disqualify($reason = 'No reason provided.', $evidence = null)
    {
        $dq = new Disqualification(['speedrun_id'=>$this->id, 'reason'=>$reason, 'evidence'=>$evidence]);
        $dq->save();
        $category = $this->category();
        $category->updated_at = now()->toDateTimeString();
        $category->save();
        Mail::to($this->user->email)
            ->queue(new DisqualifiedRun($this));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function video_id()
    {
        return substr( $this->url, 17);;
    }
    public function embed_url()
    {
        $id = $this->video_id();
        return 'https://www.youtube.com/embed/'.$id;
    }

    public function placement()
    {
        if($this->verified == 0)
            return Speedrun::where('verified','1')->count();
        if($this->disqualified())
            return Speedrun::where('verified','1')->count();

        return Category::where('name', $this->category()->name)->firstOrFail()
            ->speedruns->where('time', '<' ,$this->time)->where('verified','1')
            ->filter(function($speedrun) { return !$speedrun->disqualified(); })->count() + 1;
    }
    public function getPlacementAttribute() {
        return $this->placement();
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

    protected static function booted()
    {
        static::updating(function ($speedrun) {
            Cache::forget($speedrun->getCacheKey());
        });
    }
}
