<?php

namespace App\Http\Controllers;

use App\Mail\ElectionStarted;
use App\Mail\RunApproved;
use App\Mail\RunCreated;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Election;
use App\Models\Platform;
use App\Models\Role;
use App\Models\Speedrun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class SpeedrunController extends Controller
{
    public function welcome()
    {
        if(auth()->guest())
            $banner = Banner::orderBy('created_at', 'ASC')->first();
        else
            $banner = auth()->user()->banners()->orderBy('created_at', 'ASC')->first();
        $featured = Speedrun::where('verified',1)->inRandomOrder()->get()->filter(function($run) {return !$run->disqualified();})->first();
        $speedruns = Speedrun::where('verified',1)->latest()->limit(12)->get()->filter(function($run) {return !$run->disqualified();});
        return view('welcome', ['featured'=>$featured, 'speedruns'=>$speedruns, 'banner'=>$banner]);
    }
    public function index()
    {
        $speedruns = Speedrun::where('verified',1)->orderBy('time', 'asc')->get()->filter(function($run) {return !$run->disqualified();});
        return view('speedrun.list',['speedruns'=>$speedruns]);
    }
    public function show(Speedrun $speedrun)
    {
        return view('speedrun.show',['speedrun'=>$speedrun, 'title'=>$speedrun->title()]);
    }
    public function find()
    {
        $speedrun = Speedrun::findOrFail(\request('run'));

        return view('speedrun.show',['speedrun'=>$speedrun, 'title'=>$speedrun->title()]);
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
        Mail::to('support@americandadspeedruns.com')
            ->queue(new RunCreated($speedrun));

        return redirect('/runner/'.auth()->user()->name)->with(['success'=>'Speedrun Submitted']);
    }

    public function verify(Speedrun $speedrun)
    {
        if(Gate::allows('manage_speedruns'))
        {
            $speedrun->verified = 1;
            Mail::to($speedrun->user->email)
                ->queue(new RunApproved($speedrun));
            $speedrun->save();
            $election = new Election();
            $election->speedrun_id = $speedrun->id;
            $election->save();
            $members = Role::where('name', 'council')->firstOrFail()->users;
            foreach ($members as $user)
            {
                Mail::to($user->email)->queue(new ElectionStarted($election, $user));
            }

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
