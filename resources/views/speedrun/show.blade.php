<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('[').$speedrun->time.__('s] Run by ').$speedrun->user->name.__(' (').$speedrun->category->title.__(')') }}
        </h2>
    </x-slot>

</x-app-layout>
