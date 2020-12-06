<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Banner extends Component
{
    protected $listeners = [
        'refreshParentComponent' => '$refresh',
    ];

    public $banner;

    public function render()
    {
        return view('livewire.banner');
    }
    public function detach()
    {
        if(auth()->guest())
            return;

        $this->banner->detach();
        $this->banner = null;
    }
}
