<?php

namespace App\Http\Livewire;

use App\Mail\CommentPosted;
use App\Models\Speedrun;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Comments extends Component
{
    public $message;
    public $speedrun;
    public function render()
    {
        return view('livewire.comments', ['speedrun'=>$this->speedrun, 'message'=>'']);
    }

    public function store()
    {
        if(auth()->guest())
            return;
        if(Gate::denies('use_site'))
            return;
        $comment = new \App\Models\Comment();
        $comment->message = $this->message;
        $comment->user_id = auth()->user()->id;
        $comment->speedrun_id = $this->speedrun->id;
        $comment->save();
        Mail::to($this->speedrun->user->email)->queue(new CommentPosted($comment));
        $this->reload();
    }
    public function delete($comment_id)
    {
        $comment = \App\Models\Comment::find($comment_id);
        if($comment == null)
            return;
        if(auth()->guest())
            return;
        if(!$comment->canDelete())
            return;

        $comment->delete();
        $this->reload();
    }
    public function reload()
    {
        $this->speedrun = Speedrun::find($this->speedrun->id);
        $this->message='';
        $this->render();
    }

}
