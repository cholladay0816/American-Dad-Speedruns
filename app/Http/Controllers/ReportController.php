<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::all();
        return view('reports.list', ['reports'=>$reports]);
    }
    public function show(Report $report)
    {
        return view('reports.show', ['report'=>$report]);
    }
    public function create()
    {
        return view('reports.new');
    }
    public function store(Request $request)
    {
        $res = $request->validate(['subject'=>'required|max:256', 'body'=>'required|max:1024']);
        $report = new Report();
        $report->user_id = auth()->user()->id;
        $report->subject = $res['subject'];
        $report->body = $res['body'];
        $report->save();
        return redirect(url('/reports/'.$report->id))->with(['success'=>'Report Created']);
    }
    public function edit(Report $report)
    {
        return view('reports.edit', ['report'=>$report]);
    }
    public function update(Request $request, Report $report)
    {
        $res = $request->validate(['subject'=>'required|max:256', 'body'=>'required|max:1024', 'solved'=>'required|numeric|min:0|max:1']);
        $report->subject = $res['subject'];
        $report->body = $res['body'];
        $report->solved = $res['solved'];
        $report->save();
        return redirect(url('/reports/'.$report->id))->with(['success'=>'Report Updated']);
    }
    public function delete(Report $report)
    {
        $report->delete();
        return redirect(url('/reports'))->with(['success'=>'Report Deleted']);
    }
}
