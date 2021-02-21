<?php

namespace App\Http\Livewire;

use App\Models\Message;
use Livewire\Component;

class CouncilChatroom extends Component
{
    public string $message = '';
    protected array $rules = [
        'message' => 'required|max:1024'
    ];

    public function render()
    {
        $messages = Message::orderByDesc('created_at')->paginate(20);


        return view('livewire.council-chatroom', ['messages' => $messages]);
    }

//    public function submit() {
//        $this->validate();
//
//        $message = Message::create(['user_id' => auth()->id(), 'body' => $this->message]);
//    }
}
