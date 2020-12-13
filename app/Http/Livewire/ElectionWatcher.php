<?php

namespace App\Http\Livewire;

use App\Models\Election;
use App\Models\Vote;
use Livewire\Component;

class ElectionWatcher extends Component
{
    public Election $election;
    public $positive = -1;
    public $message;
    public function render()
    {
        return view('livewire.election-watcher', ['election' => $this->election]);
    }
    public function for()
    {
        $this->positive = 1;
    }
    public function against()
    {
        $this->positive = 0;
    }
    public function vote()
    {
        if(!$this->election->canVote())
            return false;
        if($this->positive !=0 && $this->positive != 1)
            return false;
        $vote = new Vote();
        $vote->election_id = $this->election->id;
        $vote->user_id = auth()->user()->id;
        $vote->positive = $this->positive;
        $vote->comment = $this->message;
        $vote->save();
        $this->election = Election::where('id', $this->election->id)->first();
        $this->positive = -1;
        $this->message = '';
        $this->render();
    }
    public function deleteVote()
    {
        if($this->election->expired())
            return false;
        $vote = $this->election->votes->where('user_id', auth()->user()->id)->first();
        $vote->delete();
        $this->election = Election::where('id', $this->election->id)->first();
        $this->render();
    }

}
