<?php

namespace App\Http\Controllers;

use App\Models\Speedrun;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $speedruns = Speedrun::where('verified','0')->get();
        return view('admin.index', ['speedruns'=>$speedruns]);
    }
}
