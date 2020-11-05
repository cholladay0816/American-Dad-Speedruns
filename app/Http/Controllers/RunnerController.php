<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RunnerController extends Controller
{
    //Gets all runs by one user
    public function show($username)
    {
        $user = User::where('name',$username)->firstOrFail();
        $speedruns = $user->speedruns;

        return view('speedrun.list', ['speedruns'=>$speedruns]);
    }
}
