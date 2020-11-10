<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = $request->validate(['speedrun_id'=>'exists:speedruns,id', 'message'=>'max:1024']);

        $comment = new Comment([
                'user_id'=>auth()->user()->id,
                'speedrun_id'=>$result['speedrun_id'],
                'message'=>$request['message']
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {

        if($comment->user_id == auth()->user()->id || Gate::allows('manage_comments'))
        {
            return view('comment.edit', ['comment'=>$comment]);
        }
        abort(401);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        $result = \request()->validate(['message'=>'max:1024']);

        if($comment->user_id == auth()->user()->id || Gate::allows('manage_comments'))
        {
            $comment->message = $result['message'];
            $comment->save();
            return redirect()->back(200);
        }
        abort(401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        if($comment->user_id == auth()->user()->id || Gate::allows('manage_comments'))
        {
            $comment->delete();
            return redirect()->back(200);
        }
        abort(401);
    }
}
