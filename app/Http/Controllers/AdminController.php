<?php

namespace App\Http\Controllers;

use App\Models\Speedrun;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $count = Speedrun::where('verified','0')->count();
        return view('admin.index', ['runcount'=>$count]);
    }
    public function verify()
    {
        $speedruns = Speedrun::where('verified','0')->get();
        return view('admin.verify', ['speedruns'=>$speedruns]);
    }
}
