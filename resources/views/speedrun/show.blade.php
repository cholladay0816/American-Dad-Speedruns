<x-app-layout>
    <x-slot name="title">
        {{$title}}
    </x-slot>
    <x-slot name="description">
        {{$speedrun->user->name}} completed this speedrun in {{$speedrun->time}} seconds,
        placing @th($speedrun->placement()) in the {{$speedrun->category()->title}} category.
        This run was performed on the {{$speedrun->platform()->title}} platform.
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            [@th($speedrun->placement())] {{$speedrun->category()->title." by ".$speedrun->user->name." in ".$speedrun->time."s [".$speedrun->platform()->title."]"  }}
        </h2>
    </x-slot>
    <div class="md:max-w-7xl mx-auto min-h-screen">
        <div class="flex items-center justify-center flex-col my-24">
            <div class="w-full md:w-2/3 h-96 relative overflow-hidden block">
                <iframe class="absolute top-0 bottom-0 left-0"
                        width="100%" height="100%" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen=""
                        src="{{$speedrun->embed_url()}}" ></iframe>

            </div>
        </div>
        <div class="mx-auto text-2xl font-semibold text-center">Comments</div>
        <div class="py-5 text-gray-200 mx-auto text-lg font-semibold text-center">Coming soon!</div>

        @foreach($speedrun->comments as $comment)
            @component('components.comment', ['comment' => $comment])
            @endcomponent
        @endforeach
    </div>

</x-app-layout>
