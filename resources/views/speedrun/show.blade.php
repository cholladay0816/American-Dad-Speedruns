<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            [@th($speedrun->placement())] {{$speedrun->category()->title." by ".$speedrun->user->name." in ".$speedrun->time."s [".$speedrun->platform()->title."]"  }}
        </h2>
    </x-slot>
    <div class="overflow-hidden text-black">
    <div class="flex items-center justify-center h-screen -mt-20 flex-col">
        <div class="bg-red-100 w-1/2 h-1/2"><iframe width="100%" height="100%" src="{{$speedrun->embed_url()}}"></iframe></div>
    </div>
    </div>

</x-app-layout>
