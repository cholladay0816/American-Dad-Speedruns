<x-app-layout>
    <x-slot name="title">
        Categories
    </x-slot>
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-5 py-10 text-black">

        @foreach($categories as $category)
            <div class="motion-safe:transition-none motion-reduce:transform-none transition duration-200 ease-in-out transform hover:-translate-y-1 hover:scale-110
             max-w-screen rounded overflow-hidden shadow-lg bg-white grid grid-cols-2 py-3">
                <a href="{{url('/categories/'.$category->title)}}" class="font-bold text-xl mb-2 text-center col-span-2">
                    {{$category->title}}
                </a>
                <a href="{{url('/categories/'.$category->title)}}">
                    <img class="h-48" src="{{$category->url}}" alt="Speedrun Icon">
                </a>
                <a href="{{url('/categories/'.$category->title)}}" class="px-2 py-4 mx-auto">
                    <p class="text-gray-700 text-base">
                        {{$category->description}}
                    </p>
                </a>
            </div>
        @endforeach

    </div>


</x-app-layout>
