<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Speedrun;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('category.list', ['categories'=>$categories]);
    }

    public function show(Category $category)
    {
        $speedruns = $category->speedruns->sortBy(function ($speedrun, $key) {
            return $speedrun->disqualified()?INF:$speedrun->time;
        });
        return view('speedrun.list', ['speedruns'=>$speedruns]);
    }

}
