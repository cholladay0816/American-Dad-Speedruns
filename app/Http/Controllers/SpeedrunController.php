<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Platform;
use App\Models\Speedrun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SpeedrunController extends Controller
{
    public function welcome()
    {
        $featured = Speedrun::where('verified',1)->inRandomOrder()->get()->filter(function($run) {return !$run->disqualified();})->first();
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
        $ordinal = str_ordinal($speedrun->placement());

        $title = '['.$ordinal.'] '.$speedrun->category()->title." by ".$speedrun->user->name." in ".$speedrun->time."s";
        return view('speedrun.show',['speedrun'=>$speedrun, 'title'=>$title]);
    }
    public function find()
    {
        $speedrun = Speedrun::findOrFail(\request('run'));

        $ordinal = str_ordinal($speedrun->placement());

        $title = '['.$ordinal.'] '.$speedrun->category()->title." by ".$speedrun->user->name." in ".$speedrun->time."s";
        return view('speedrun.show',['speedrun'=>$speedrun, 'title'=>$title]);
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
                'platform'=>'integer|required|exists:platforms,id',
                'category'=>'integer|required|exists:categories,id'
            ]);
        $speedrun = new Speedrun(['url'=>$request['url'],'time'=>$request['time']]);
        $speedrun->user_id = auth()->user()->id;
        $speedrun->save();
        $speedrun->platforms()->sync([$request['platform']]);
        $speedrun->categories()->sync([$request['category']]);


        return redirect('/runner/'.auth()->user()->name)->with(['success'=>'Speedrun Submitted']);
    }

    public function verify(Speedrun $speedrun)
    {
        if(Gate::allows('manage_speedruns'))
        {
            $speedrun->verified = 1;
            $speedrun->save();
            return redirect()->back()->with(['success'=>'Speedrun Verified']);
        }
        else {
            abort(401);
        }
    }

    public function update(Speedrun $speedrun)
    {

    }

    public function delete(Speedrun $speedrun)
    {
        //Delete if user is the creator or if user has manager permissions
        if($speedrun->user_id == auth()->user()->id || Gate::allows('manage_speedruns'))
            $speedrun->delete();
        else
            abort(401);

        return redirect()->back()->with(['success'=>'Speedrun Deleted']);
    }
}
