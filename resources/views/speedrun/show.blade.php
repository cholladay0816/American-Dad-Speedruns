<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            [@th($speedrun->placement())] {{$speedrun->category()->title." by ".$speedrun->user->name." in ".$speedrun->time."s [".$speedrun->platform()->title."]"  }}
        </h2>
    </x-slot>

</x-app-layout>
