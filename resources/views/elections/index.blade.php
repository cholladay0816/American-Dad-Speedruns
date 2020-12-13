<x-app-layout>
    <div class="container mx-auto px-4 md:px-0">
    @foreach($elections as $election)
        <div class="py-6">
            @livewire('election-watcher', ['election'=>$election])
        </div>
    @endforeach
    </div>

</x-app-layout>
