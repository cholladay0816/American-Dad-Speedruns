<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            [@th($speedrun->placement())] {{$speedrun->category()->title." by ".$speedrun->user->name." in ".$speedrun->time."s [".$speedrun->platform()->title."]"  }}
        </h2>
    </x-slot>
    <div class="max-w-7xl mx-auto min-h-screen">
        <div class="flex items-center justify-center flex-col my-24">
            <div class="w-2/3 h-96 relative overflow-hidden block">
                <iframe class="absolute top-0 bottom-0 left-0"
                        width="100%" height="100%" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen=""
                        src="{{$speedrun->embed_url()}}" ></iframe>

            </div>
        </div>
    <div class="mx-auto text-2xl font-semibold text-center">Comments</div>

        @foreach($speedrun->comments as $comment)
            @component('components.comment', ['comment' => $comment])
            @endcomponent
        @endforeach
    </div>

</x-app-layout>
