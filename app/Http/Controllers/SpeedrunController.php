<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Platform;
use App\Models\Speedrun;
use Illuminate\Http\Request;

class SpeedrunController extends Controller
{
    public function welcome()
    {
        $featured = Speedrun::inRandomOrder()->first();
        $speedruns = Speedrun::where('verified',1)->latest()->limit(12)->get()->filter(function($run) {return !$run->disqualified();});
        return view('welcome', ['featured'=>$featured, 'speedruns'=>$speedruns]);
    }
    public function index($category = 'Any%', $platform = '')
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

    public function create()
    {
        $categories = Category::all();
        $platforms = Platform::all();
        return view('speedrun.new', ['categories'=>$categories, 'platforms'=>$platforms]);
    }
    public function store(Request $request)
    {
        $request->validate(
            [
                'url'=>'required|url|unique:speedruns,url|max:28|starts_with:https://youtu.be/',
                'time'=>'numeric|required|min:0.0001',
                'platform_id'=>'integer|required|exists:platforms,id',
                'category_id'=>'integer|required|exists:categories,id'
            ]);
        $speedrun = new Speedrun(['url'=>$request['url'],'time'=>$request['time']]);
        $speedrun->user_id = auth()->user()->id;
        $speedrun->platform()->sync($request['platform_id']);
        $speedrun->category()->sync($request['category_id']);
        $speedrun->save();
        return redirect(url('/speedruns/'.auth()->user()->name))->with(['success'=>'Speedrun Submitted']);
    }

    public function update(Speedrun $speedrun)
    {

    }

    public function delete(Speedrun $speedrun)
    {

    }
}
