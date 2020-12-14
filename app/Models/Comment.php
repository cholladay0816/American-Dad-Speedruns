<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function speedrun()
    {
        return $this->belongsTo(Speedrun::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function uploaded()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
    public function canDelete()
    {
        //Guest cannot delete
        if(auth()->guest())
            return false;
        //Suspended users cannot
        if(auth()->user()->isSuspended())
            return false;
        //Admins can
        if(Gate::allows('manage_speedruns'))
            return true;
        //Commentor can
        if($this->user_id == auth()->user()->id)
            return true;
        //Uploader can
        if($this->speedrun->user_id = auth()->user()->id)
            return true;

        return false;
    }
}
