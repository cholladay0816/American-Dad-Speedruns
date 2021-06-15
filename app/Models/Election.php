<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class Election extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function votes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vote::class);
    }
    public function voters() : Collection
    {
        return $this->votes()->pluck('user_id')->flatten()->unique();
    }
    public function canVote() : bool
    {
        if($this->expired())
            return false;
        if(auth()->guest())
            return false;
        if(auth()->user()->isSuspended())
            return false;
        if(Gate::denies('vote'))
            return false;

        return $this->voters()->contains(auth()->user()->id);
    }

    public function positive()
    {
        return $this->votes->where('positive', '1')->count();
    }
    public function negative()
    {
        return $this->votes->where('positive', '0')->count();
    }
    public function total()
    {
        return $this->votes->count();
    }
    public function speedrun()
    {
        return $this->belongsTo(Speedrun::class);
    }

    public function percent($positive = '1')
    {
        if($this->total() == 0)
            return 0;
        return number_format((($positive == '1' ? $this->positive() : $this->negative()) / $this->total()) * 100, 0);
    }
    public function timeleft()
    {
        return Carbon::parse($this->expiration)->diffForHumans(['parts' => 2]);
    }
    public function timepercent()
    {
        return  ( now()->timestamp - Carbon::parse($this->created_at)->timestamp ) / $this->timeBetween();
    }
    public function timeBetween()
    {
        return Carbon::parse($this->expiration)->timestamp - Carbon::parse($this->created_at)->timestamp;
    }
    public function expired()
    {
        return now() > Carbon::parse($this->expiration);
    }
}
