<div
    @if(!$election->canVote() && !$election->expired())
    wire:poll.30s
    @endif
    class="bg-white text-black px-8 rounded-2xl pt-4 pb-2 max-w-4xl mx-auto my-6 shadow-lg">
    <h1 class="text-center text-3xl font-bold

            border-b-2 border-gray-100
        py-2">
        Election: '{{$election->speedrun->title()}}'
    </h1>
    <div class="grid md:grid-cols-2 gap-5 pt-5
    @if($election->total() > 0)
        md:border-b-2
    @endif
    ">
        <div class=" flex flex-col rounded-lg">
            <div class="text-lg text-gray-500 font-thin rounded-md p-2">
                <p class="border-b-2 pb-2">
                To determine if Speedrun #{{$election->speedrun->id}} is to be
                    <span class="border-green-300 duration-150 hover:border-green-400 border-b-2">approved</span>
                    or
                    <span class="border-red-300 duration-150 hover:border-red-400 border-b-2">disqualified</span> from the leaderboards.
                </p>
                <p class="pt-2">
                This election is being held by the official <a class="font-semibold text-gray-600" href="{{url('/council/join')}}">American Dad Speedrunning Council</a>,
                made up of {{env('COUNCIL_SIZE')}} members.
                </p>
            </div>
            <div class="pl-1 italic text-md font-thin pt-2 pb-4">
                Election end{{$election->expired()?'ed':'s'}} <span>{{ $election->timeleft() }}</span>
            </div>
        </div>
        <div class="">
            @if(!$election->canVote())
            <div class="grid sm:grid-cols-2 gap-5">
                <div class="group flex flex-col rounded-lg bg-green-100 p-3 text-center transform translate hover:scale-110 duration-150">
                    <h4 class="font-semibold text-2xl border-b-2 border-green-300 duration-150 group-hover:border-green-400">For</h4>
                    <span class="text-5xl">{{$election->positive()}}</span>
                    <span class="text-green-800 text-sm">{{$election->percent('1')}}% of votes</span>
                </div>
                <div>
                    <div class="group flex flex-col rounded-lg bg-red-100 p-3 text-center transform translate hover:scale-110 duration-150">
                        <h4 class="font-semibold text-2xl border-b-2 border-red-300 duration-150 group-hover:border-red-400">Against</h4>
                        <span class="text-5xl">{{$election->negative()}}</span>
                        <span class="text-red-800 text-sm">{{$election->percent('0')}}% of votes</span>
                    </div>
                </div>
            </div>
            @else
                <div class="grid sm:grid-cols-2 gap-5 h-32">
                    <a wire:click="for" class="border-2 border-green-{{$positive == 1 ?'500':'200'}} cursor-pointer group flex flex-col rounded-lg bg-green-{{$positive == 1?'400':'100'}} p-3 text-center transform translate hover:scale-110 duration-150">
                        <h4 class="my-auto text-center font-semibold text-4xl duration-150">Approve</h4>
                    </a>
                    <a wire:click="against" class="border-2  border-red-{{$positive == 0 ?'500':'200'}} cursor-pointer group flex flex-col rounded-lg bg-red-{{$positive == 0?'400':'100'}} p-3 text-center transform translate hover:scale-110 duration-150">
                        <h4 class="my-auto font-semibold text-4xl duration-150">Disqualify</h4>
                    </a>
                </div>
                <textarea max="1024" placeholder="(Optional) Enter your reasoning here." wire:model.defer="message" class="rounded-lg w-full mt-4 p-3 bg-gray-100"></textarea>
                <div class="flex w-full justify-end my-3">
                    @if($positive != -1)
                    <button wire:loading.attr="disabled" wire:click="vote" class="text-xl text-white px-4 py-2 rounded-lg bg-blue-600">
                        Vote
                    </button>
                    @endif
                </div>

            @endif
        </div>
    </div>
    @if($election->total() > 0)
    <div class="p-2 mt-5 flex flex-1 flex-col">
        <h2 class="text-center font-bold text-xl mx-auto">
            Vote History
        </h2>
        <div class="max-h-64 overflow-y-auto">
            @foreach($election->votes as $vote)
                <div class=" rounded-lg bg-{{$vote->positive?'green':'red'}}-100 p-2 my-2 mx-1
                border-{{$vote->positive?'green':'red'}}-300
                hover:border-{{$vote->positive?'green':'red'}}-400
                duration-150
                border-t-4">
                    <h3 class="
                    @if(isset($vote->comment))

                    @endif
                        "><div class="flex justify-between">
                            <div class="pl-2 text-lg">
                                <span class="font-semibold">{{$vote->user->name}}</span>
                                voted
                                <span class="font-semibold text-{{$vote->positive ? 'green' : 'red'}}-900">{{$vote->positive ? 'for':'against'}}</span> this run.
                            </div>

                        <div class="italic font-thin text-gray-600 md:block hidden">
                            {{$vote->humanReadable()}}
                            @if($vote->user_id == auth()->user()->id && !$election->expired())
                            <svg wire:click="deleteVote" class="cursor-pointer inline w-4 h-4 ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            @endif
                        </div>
                        </div></h3>
                    @if(isset($vote->comment))
                        <p class="font-thin text-lg px-2 pt-2">{{$vote->comment}}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
