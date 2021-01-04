<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RunnerController extends Controller
{
    //Gets all runs by one user
    public function show(User $user)
    {
        return view('speedrun.list', ['speedruns'=>$user->speedruns->sortBy('time')->sortBy(function($run) {return $run->disqualified();})]);
    }
}
