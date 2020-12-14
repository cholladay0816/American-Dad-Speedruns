<?php

namespace App\Http\Livewire;

use App\Models\Speedrun;
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
        $comment = new \App\Models\Comment();
        $comment->message = $this->message;
        $comment->user_id = auth()->user()->id;
        $comment->speedrun_id = $this->speedrun->id;
        $comment->save();
        $this->reload();
    }
    public function delete($comment_id)
    {
        $comment = \App\Models\Comment::find($comment_id);
        if($comment == null)
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
