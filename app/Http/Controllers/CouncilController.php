<?php

namespace App\Http\Controllers;

use App\Mail\CouncilActivated;
use App\Models\Election;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CouncilController extends Controller
{
    public function index()
    {

        $judges = Role::where('name', 'council')->first()->users()->get();
        //show current elections live
        $elections = Election::where('expiration', '>', now()->toDateTimeString())->orderBy('created_at', 'DESC')->get();
        //get all council members
        //get all elections
        return view('council.index',
            [
                'judges'=>$judges,
                'elections'=>$elections
            ]);
    }

    public function join()
    {
        // auth()->user()->createOrGetStripeCustomer();
        if(Role::where('name', 'council')->first()->users()->where('user_id', auth()->user()->id)->exists())
        {
            return redirect(url('/council'))->with(['info'=>'You are already a member!']);
        }
        $seats = config('adsr.council.size') - Role::where('name', 'council')->first()->users()->count();
        if($seats <= 0)
        {
            return redirect()->back()->with(['error'=>'Council is full.']);
        }
        return view('council.join', ['seats'=>$seats]);
    }
    public function store(Request $request)
    {
        // validate seats
        $seats = config('adsr.council.size') - Role::where('name', 'council')->first()->users()->count();
        if($seats <= 0)
        {
            return redirect()->back()->with(['error'=>'Council is full.']);
        }
        Mail::to(auth()->user()->email)->queue(new CouncilActivated());
        Role::where('name', 'council')->first()->users()->attach([auth()->user()->id]);
        return redirect(url('/council'))->with(['success'=>'Council Upgrade applied!']);
    }
    public function destroy()
    {
        Role::where('name', 'council')->first()->users()->detach([auth()->user()->id]);

        return redirect(route('council'))->with(['success' => 'Council Membership Canceled']);
    }
}
