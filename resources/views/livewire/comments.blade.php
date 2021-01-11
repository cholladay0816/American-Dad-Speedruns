<div class="max-w-4xl mx-auto pt-4 pb-24">
    @auth()
        <form wire:submit.prevent="store" class="w-full flex flex-col pb-12">
            <textarea name="message" required maxlength="4096" placeholder="Write your comment here..." rows="4" class="p-2 rounded-md bg-darker w-full"
                      wire:model.lazy="message"></textarea>
            @error('message')
                <span class="text-red-500">Error: {{$message}}</span>
            @enderror
            <div class="flex flex-row justify-end">
                <button type="submit" class="transition-colors duration-100
                @if($message=='')
                    cursor-default opacity-0
                @else
                    cursor-pointer opacity-100 opacity-100
                @endif
                    bg-green-400 hover:bg-green-500 text-white text-xl rounded-lg px-3 py-2 my-2"
                >Submit</button>
            </div>
        </form>
    @endauth
    @foreach($speedrun->comments as $comment)
            @if(isset($comment))
                <div class="group duration-200 transform translate w-full bg-darker bg-opacity-30 rounded-r-lg py-2 px-3 border-l-2 hover:border-green-400 border-green-600 my-2">
                    <div class="flex flex-col">
                        <div class="flex flex-row justify-between">
                            <h3 class="font-semibold text-gray-200 text-md">{{$comment->user->name}}</h3>
                            <span class="text-sm font-hairline text-gray-600">Sent {{$comment->uploaded()}}
                                @if($comment->canDelete())
                                    <svg wire:click="delete('{{$comment->id}}')" class="cursor-pointer inline w-4 h-4 text-red-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                @endif
                            </span>
                        </div>
                        <p class="text-white text-xl">{{$comment->message}}</p>
                    </div>
                </div>
            @else
                <div>

                </div>
            @endif
        @endforeach
</div>
