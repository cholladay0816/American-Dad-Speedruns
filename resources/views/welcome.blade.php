<x-app-layout >
    <h1 class="text-center font-bold mx-auto text-5xl mt-4">American Dad Speedruns</h1>
    <p class="text-center mx-auto text-lg mb-4 text-gray-200">The official leaderboards for all American Dad Speedruns.</p>
    @if(isset($banner))
        @livewire('banner', ['banner'=>$banner])
    @endif
    <div class="mx-auto">
        <div class="max-w-full lg:max-w-6xl mx-auto col-span-1 md:col-span-4">
            @if(isset($featured))
            <div class="py-12">
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-2 border rounded p-10 bg-gray-50 text-black">
                    <div class="h-56 md:h-96 xl:col-span-2 relative overflow-hidden block max-w-96 rounded">
                    <iframe class="absolute top-0 bottom-0 left-0"
                            width="100%" height="100%" frameborder="0"
                            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen=""
                            src="{{$featured->embed_url()}}" ></iframe>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-bold text-2xl" href="{{url('/speedruns/'.$featured->id)}}">Featured Run</h2>
                        <p class="font-semibold text-2xl">{{$featured->time}}s by <a href="{{url('/runner/'.$featured->user->name)}}">{{$featured->user->name}}</a></p>
                        <p class="text-gray-700 text-lg"><a href="{{url('/categories/'.$featured->category()->name)}}">{{$featured->category()->title}}</a> - <a href="{{url('/platforms/'.$featured->platform()->name)}}">{{$featured->platform()->title}}</a></p>
                    </div>
                </div>
            </div>
                <h2 class="rounded-t py-5 text-white bg-green-500 text-center font-bold text-2xl">
                    Please Consider Supporting Us
                </h2>
                <div class="rounded-b pb-8 pt-4 text-black bg-gray-100 text-left text-md px-8 flex flex-col">
                    <p class="text-lg">
                        AmericanDadSpeedruns may be the greatest speedrunning site of all time,
                        but the server costs are not cheap.
                        If you are interested in supporting our project, please consider donating to our PayPal.
                        Every dollar helps keep the servers online for the next generation of American Dad Speedrunners.
                    </p>
                    <div class="mt-4">
                        <a href="{{env('PAYPAL_DONATE')}}" class="rounded-md py-3 px-4 font-bold text-lg text-white bg-green-500 hover:bg-green-400">
                            Donate with PayPal
                        </a>
                    </div>
                </div>
            @endif

            @if($speedruns)

                <div class="max-w-full lg:max-w-6xl mx-auto py-12">
                    <div class="border rounded py-10 md:p-10 bg-gray-50 text-black">
                        <h2 class="font-bold text-2xl mb-10">Recently Submitted Speedruns</h2>
                        <div class="md:-mx-10">
                            @livewire('speedrun-table', ['speedruns'=>$speedruns])
                        </div>
                    </div>
                </div>
            @endif
            <div class="mx-auto border-white border-t border-dotted">
                <div class="max-w-7xl max-h-xl mx-auto grid md:grid-cols-2">
                    <div class="order-2 md:h-100">
                        <img class="max-w-24" src="{{asset('img/stan_says.png')}}"/>
                    </div>
                    <div class="my-auto flex flex-col">
                        <div class="font-bold text-4xl">Stan Says:</div>
                        <div class="font-semibold text-gray-300">You should join the American Dad Speedrunning Discord!</div>
                        <div class="border-l-2 border-yellow-300 text-white bg-opacity-10 hover:bg-opacity-25 bg-yellow-500 px-3">All the cool kids are already <a class="text-indigo-500 font-semibold">@here</a>.</div>
                        <a href="{{env('DISCORD_INVITE')}}" class="bg-indigo-500 border-white text-white text-xl rounded px-2 py-2 mr-auto mt-5 ">Click here to Join!</a>
                        <div class="text-gray-300 mt-8">Note: we are not affiliated with this server.</div>
                    </div>
                </div>
            </div>
            <div>

            </div>
        </div>
    </div>
</x-app-layout>
