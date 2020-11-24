<?php

namespace App\Http\Controllers;

use App\Models\Disqualification;
use App\Models\Speedrun;
use Illuminate\Http\Request;

class DisqualificationController extends Controller
{
    public function index()
    {
        $disqualifications = Disqualification::all()->sortBy(function ($disqualification, $key) {
            return $disqualification->speedrun->time;
        });;
        return view('admin.disqualifications', ['disqualifications'=>$disqualifications]);
    }
    public function create(Speedrun $speedrun)
    {
        if($speedrun->disqualified())
            return redirect(url('/admin/disqualifications/'.$speedrun->disqualification->id));
        return view('admin.disqualify-run', ['speedrun'=>$speedrun]);
    }
    public function store(Speedrun $speedrun)
    {
        $res = \request()->validate(['reason'=>'max:255|required', 'evidence'=>'max:255']);

        $speedrun->disqualify($res['reason'], $res['evidence']??null);
        return redirect(url('/admin/disqualifications/'.$speedrun->disqualification->id))->with(['success'=>'Run Disqualified']);
    }
    public function view(Disqualification $disqualification)
    {
        return view('admin.disqualification-edit',
            ['speedrun'=>$disqualification->speedrun, 'disqualification'=>$disqualification]);
    }
    public function update(Disqualification $disqualification)
    {
        $res = \request()->validate(['reason'=>'max:255|required', 'evidence'=>'max:255']);

        $disqualification->reason = $res['reason'];
        $disqualification->evidence = $res['evidence'];
        $disqualification->save();

        return redirect()->back()->with(['success'=>'Disqualification Updated']);
    }
    public function destroy(Disqualification $disqualification)
    {
        $id = $disqualification->speedrun_id;
        $disqualification->delete();
        return redirect(url('/speedruns/'.$id))->with(['success'=>'Disqualification Deleted']);
    }
}
