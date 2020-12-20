<x-app-layout>
    <div class="mx-auto max-w-6xl mx-4 bg-white rounded-2xl p-2 my-6 text-black">
        <h1 class="text-center text-5xl font-bold">Welcome to THE COUNCIL</h1>
        <p class="text-gray-600 text-xl md:px-8">This is an elite group of hardened American Dad Speedrunning judges, who have been bestowed divine power
        to deliver justice to any and all speedruns submitted to this site.</p>
        <p class="text-gray-600 text-xl md:px-8 mt-8">This group is so selective, in fact, that only {{config('adsr.councilsize')}} seats are given out.
        If you aren't a member after all available seats are taken, you'll need to wait for a seat to become available.
        After all, only a select group of gifted individuals are worthy to sit in THE COUNCIL.</p>
        <div class="my-4 mx-auto text-center">
            @if($judges->count() < config('adsr.councilsize'))
                <a href="{{url('council/join')}}" class="duration-300 text-2xl mx-auto px-3 py-2 bg-green-400 hover:bg-green-300 text-white font-semibold rounded-xl font-semibold">
                    Sign up ({{config('adsr.councilsize') - $judges->count()}} seats remain!)
                </a>
            @else
                <a class="cursor-not-allowed disabled text-2xl mx-auto px-3 py-2 bg-red-300 text-white font-semibold rounded-xl font-semibold">
                    Sorry, all seats are currently taken.
                </a>
            @endif
        </div>
        <div class="border-t-2 border-green-400 mt-4">
            <h2 class="text-center text-3xl font-semibold">Our honorable judges:</h2>
            <div class="flex flex-col">
                @foreach($judges as $judge)
                    @continue(!$judge->subscribed('default'))
                    @component('components.judge', ['judge'=>$judge])
                    @endcomponent
                @endforeach
            </div>
        </div>
    </div>
    @if($elections->count() > 0)
    <div class="container mx-auto px-4 md:px-0">
    <h2 class="text-center font-bold text-white text-4xl pt-5">Ongoing Elections <a class="text-sm font-light text-gray-400" href="{{url('elections')}}">(View All)</a></h2>
        @foreach($elections as $election)
        <div class="py-6">
            @livewire('election-watcher', ['election'=>$election])
        </div>
    @endforeach
    </div>
    @endif

</x-app-layout>
