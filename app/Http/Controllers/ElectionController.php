<?php

namespace App\Http\Controllers;

use App\Models\Election;
use Illuminate\Http\Request;

class ElectionController extends Controller
{
    public function index()
    {
        $elections = Election::orderBy('created_at', 'DESC')->get();
        return view('elections.index', ['elections'=>$elections]);
    }
    public function show(Election $election)
    {
        return view('elections.show', ['election'=>$election]);
    }
}
