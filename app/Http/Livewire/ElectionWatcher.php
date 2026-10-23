<?php
namespace App\Http\Livewire;
use Livewire\Component;
// Retired surface: reject even stale component actions; retain historical tables.
class ElectionWatcher extends Component
{
    public function render() { abort(410); }
    public function for() { abort(410); }
    public function against() { abort(410); }
    public function vote() { abort(410); }
    public function deleteVote() { abort(410); }
}
