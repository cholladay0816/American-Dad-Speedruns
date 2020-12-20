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

        $judges = Role::where('name', 'council')->first()->users;
        //show current elections live
        $now = now()->toDateTimeString();
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
        auth()->user()->createOrGetStripeCustomer();
        if(auth()->user()->subscribed())
            return redirect(url('/council'))->with(['info'=>'You are already subscribed!']);
        $seats = env('COUNCIL_SIZE') - Role::where('name', 'council')->first()->users()->count();
        return view('council.join', ['intent' => auth()->user()->createSetupIntent(), 'seats'=>$seats]);
    }
    public function store(Request $request)
    {
        $res = $request->validate(['paymentMethod'=>'required']);
        auth()->user()->createOrGetStripeCustomer();
        auth()->user()->newSubscription('default', env('SUBSCRIPTION_COUNCIL'))->create($res['paymentMethod']);
        Mail::to(auth()->user()->email)->queue(new CouncilActivated());
        Role::where('name', 'council')->first()->users()->attach([auth()->user()->id]);
        return redirect(url('/council'))->with(['success'=>'Council Upgrade applied!']);
    }
}
