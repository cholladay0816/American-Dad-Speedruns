<?php

namespace App\Http\Controllers;

use App\Models\Speedrun;
use Illuminate\Http\Request;

class SpeedrunController extends Controller
{
    public function welcome()
    {
        $featured = Speedrun::inRandomOrder()->first();
        $speedruns = Speedrun::where('verified',1)->orderBy('time', 'asc')->get()->filter(function($run) {return !$run->disqualified();});
        return view('welcome', ['featured'=>$featured, 'speedruns'=>$speedruns]);
    }
    public function index()
    {
        $speedruns = Speedrun::where('verified',1)->orderBy('time', 'asc')->get()->filter(function($run) {return !$run->disqualified();});
        return view('speedrun.list',['speedruns'=>$speedruns]);
    }
    public function show(Speedrun $speedrun)
    {
        return view('speedrun.show',['speedrun'=>$speedrun]);
    }
    public function find()
    {
        $speedrun = Speedrun::findOrFail(\request('run'));
        return view('speedrun.show',['speedrun'=>$speedrun]);
    }
}
