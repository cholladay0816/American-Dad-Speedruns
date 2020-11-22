<x-app-layout>
    <x-slot name="title">
        Platforms
    </x-slot>
    <div class="max-w-7xl mx-auto grid grid-cols-3 gap-5 py-10 text-black">
        @foreach($platforms as $platform)
            <div class="motion-safe:transition-none motion-reduce:transform-none transition duration-200 ease-in-out transform hover:-translate-y-1 hover:scale-110
             max-w-sm rounded overflow-hidden shadow-lg bg-white grid grid-cols-1 py-3">
                <a href="{{url('/platforms/'.$platform->title)}}" class="font-bold text-xl mb-2 text-center ">
                    {{$platform->title}}
                </a>
                <a href="{{url('/platforms/'.$platform->title)}}" class="px-8 py-4 mx-auto">
                    <p class="text-gray-700 text-base">
                        {{$platform->description}}
                    </p>
                </a>
            </div>
        @endforeach
    </div>

</x-app-layout>
