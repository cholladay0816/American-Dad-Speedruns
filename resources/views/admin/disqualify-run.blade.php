<x-app-layout>

    <x-slot name="title">
        Disqualify Run
    </x-slot>
    <x-slot name="description">
        Disqualify {{$speedrun->user->name}}'s {{$speedrun->category()->title}} run.
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Disqualify: [@th($speedrun->placement())] {{$speedrun->category()->title." by ".$speedrun->user->name." in ".$speedrun->time."s [".$speedrun->platform()->title."]"  }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto min-h-screen">
        <h1 class="text-center text-4xl font-bold mt-5">Disqualify Run</h1>
    <div class="flex items-center justify-center flex-col mt-12">
        <div class="w-full md:w-5/6 h-144 relative overflow-hidden block">
            <iframe class="absolute top-0 bottom-0 left-0"
                    width="100%" height="100%" frameborder="0"
                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen=""
                    src="{{$speedrun->embed_url()}}" ></iframe>
        </div>
    </div>
        <form method="POST">
            @csrf
            @error('reason')
            <p class="mx-4 pt-5 text-red-500 font-semibold text-sm italic">{{$message}}</p>
            @enderror
        <div class="pb-5 mt-2 pb-24 flex flex-col">
            <textarea required name="reason" type="number" min="0.0001" step="0.0001" class="appearance-none flex flex-1 bg-gray-200 text-gray-700
                @error('reason')
                    border border-red-500
                @enderror
                rounded py-3 h-24 m-4 px-4 leading-tight focus:outline-none focus:bg-gray-300 focus:border-gray-500"
                id="grid-platform-id" placeholder="Reason for disqualification"></textarea>

            <textarea name="evidence" type="number" min="0.0001" step="0.0001" class="appearance-none flex flex-1 bg-gray-200 text-gray-700
                @error('evidence')
                border border-red-500
                @enderror
                rounded py-3 h-24 m-4 px-4 leading-tight focus:outline-none focus:bg-gray-300 focus:border-gray-500"
                      id="grid-platform-id" placeholder="Evidence URL (Optional)"></textarea>

            <button type="submit" class="m-4 bg-red-500 text-white px-5 py-1 rounded h-12 self-end">Submit</button>
        </div>


        </form>
    </div>

</x-app-layout>
