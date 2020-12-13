<x-app-layout>
    <div class="container mx-auto px-4 md:px-0">
        <div class="py-6">
            @livewire('election-watcher', ['election'=>$election])
        </div>
    </div>
</x-app-layout>
