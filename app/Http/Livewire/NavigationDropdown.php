<?php
namespace App\Http\Livewire;
use Livewire\Component;
class NavigationDropdown extends Component
{
    protected $listeners = ['refresh-navigation-dropdown' => '$refresh', 'refresh-navigation-menu' => '$refresh'];
    public function render() { return view('navigation-dropdown'); }
}
