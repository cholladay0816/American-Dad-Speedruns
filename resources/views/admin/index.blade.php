<x-admin-layout>
    <x-slot name="header">
        Admin Dashboard
    </x-slot>


    @component('components.speedrun-table', ['speedruns' =>$speedruns])
    @endcomponent

</x-admin-layout>
