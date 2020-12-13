<?php

namespace App\Http\Livewire;

use Livewire\Component;

class UpdateBillingInformation extends Component
{
    public function render()
    {
        return view('profile.update-billing-information');
    }
    public function billing()
    {
        return redirect(auth()->user()->billingPortalUrl());
    }
}
