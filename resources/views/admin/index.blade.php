<x-app-layout>
    <x-slot name="header">
        Admin Dashboard
    </x-slot>


    @component('components.speedrun-table', ['speedruns' =>$speedruns])
    @endcomponent

</x-app-layout>
