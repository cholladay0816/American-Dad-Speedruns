<x-app-layout>
    <x-slot name="title">
        Platforms
    </x-slot>
    <div class="max-w-7xl mx-auto grid grid-cols-3 gap-5 py-10 text-black">

        @foreach($platforms as $platform)

            <div class="max-w-sm rounded overflow-hidden shadow-lg bg-white grid grid-cols-2 py-3">
                <a href="{{url('/platforms/'.$platform->title)}}" class="font-bold text-xl mb-2 text-center col-span-2">
                    {{$platform->title}}
                </a>
                <a href="{{url('/platforms/'.$platform->title)}}">
                    <img class="h-48" src="{{$platform->url}}" alt="Speedrun Icon">
                </a>
                <div class="px-2 py-4 mx-auto">
                    <p class="text-gray-700 text-base">
                        {{$platform->description}}
                    </p>
                </div>
            </div>
        @endforeach

    </div>


</x-app-layout>
