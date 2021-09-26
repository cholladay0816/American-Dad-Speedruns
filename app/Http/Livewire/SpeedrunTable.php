<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SpeedrunTable extends Component
{
    public $speedruns;
    public $runs = [];
    public $readyToLoad = false;
    public $count = 1;
    public function loadRuns()
    {
        $this->readyToLoad = true;
    }
    public function render()
    {
        return view('livewire.speedrun-table', ['speedruns'=> $this->speedruns, 'readyToLoad'=>$this->readyToLoad]);
    }
}
