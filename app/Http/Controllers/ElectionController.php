<?php

namespace App\Http\Controllers;

use App\Models\Election;
use Illuminate\Http\Request;

class ElectionController extends Controller
{
    public function show(Election $election)
    {
        return $election;
    }
}
