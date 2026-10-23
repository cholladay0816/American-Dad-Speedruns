<?php
namespace App\Http\Livewire;
use Livewire\Component;
// Retired surface: reject even stale component actions; retain historical tables.
class UpdateBillingInformation extends Component
{
    public function render() { abort(410); }
    public function billing() { abort(410); }
}
