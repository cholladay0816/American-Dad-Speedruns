<?php

namespace App\Http\Controllers;

use App\Models\Speedrun;
use Illuminate\Http\Request;

class SpeedrunController extends Controller
{
    public function index()
    {
        $speedruns = Speedrun::all();
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
