<x-app-layout>
    <x-slot name="header">
        Verify Speedruns
    </x-slot>

    @if($speedruns->count() > 0)
    @livewire('speedrun-table', ['speedruns' =>$speedruns])
    @else
    <div class="mx-auto text-center h-144 flex object-center justify-center">
        <div class="font-semibold text-3xl my-auto mx-auto text-gray-200 text-center">
            You're all caught up!
        </div>
    </div>
    @endif

</x-app-layout>
