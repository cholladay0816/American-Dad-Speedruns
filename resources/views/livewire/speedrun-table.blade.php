<div wire:init="loadRuns">
{{--    @if($readyToLoad)--}}
    <div class="my-4">
        <div class="py-2 align-middle inline-block lg:min-w-full w-full sm:px-6 lg:px-8">
            <div class="shadow border-b border-gray-200 sm:rounded-lg">
                <table class="lg:min-w-full w-full divide-y divide-gray-200">
                    <thead>
                    <tr>
                        <th class="border-r-2 sm:border-0 border-dashed border-gray-200 sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Place
                        </th>
                        <th class="border-r-2 sm:border-0 border-dashed sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="border-r-2 sm:border-0 border-dashed sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Time
                        </th>
                        <th class="md:hidden table-cell sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Info
                        </th>
                        <th class="hidden md:table-cell sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="hidden md:table-cell sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Platform
                        </th>
                        <th class="hidden md:table-cell sm:px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="py-3 bg-gray-50"></th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($speedruns as $speedrun)
                        <tr>
                            @cache($speedrun)
                            <td class="sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                @th($speedrun->placement())
                            </td>
                            <td class="sm:px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="">
                                        <div class="text-sm leading-5 font-medium text-gray-900">
                                            <a href="{{url('/runner/'.$speedrun->user->name)}}">{{$speedrun->user->name}}</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center sm:text-left sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                {{number_format($speedrun->time, config('adsr.speedrun.decimals'))}}s
                            </td>
                            <td class="hidden md:table-cell sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                <a class="text-center sm:text-left" href="{{url('categories/'.$speedrun->category()->name)}}">{{$speedrun->category()->title}}</a>
                            </td>
                            <td class="hidden md:table-cell sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                <a href="{{url('platforms/'.$speedrun->platform()->name)}}">{{$speedrun->platform()->title}}</a>
                            </td>
                            <td class="md:hidden table-cell sm:px-6 py-4 whitespace-no-wrap">
                                <a href="{{url('categories/'.$speedrun->category()->name)}}" class="text-sm leading-5 text-gray-900">{{$speedrun->category()->title}}</a>
                                <a href="{{url('platforms/'.$speedrun->platform()->name)}}" class="text-sm leading-5 text-gray-500">{{$speedrun->platform()->title}}</a>
                            </td>
                            <td class="hidden md:table-cell sm:px-6 py-4 whitespace-no-wrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{$speedrun->disqualified()?'bg-red-200 text-red-800':'text-green-800 bg-green-100'}}">
                                      {{$speedrun->status()}}
                                    </span>
                            </td>
                            @endcache
                            <td class="sm:px-2 py-4 whitespace-no-wrap text-left text-sm leading-5 font-medium">
                                <a href="{{url('/watch/'.$speedrun->id)}}" class="text-indigo-600 hover:text-indigo-900">Watch</a>
                                @if($speedrun->canDelete())
                                    @can('manage_speedruns')
                                        @if($speedrun->verified==0)
                                            <form method="POST" action="{{url('/speedruns/'.$speedrun->id)}}">
                                                @csrf
                                                @method('PATCH')
                                                <input class="text-green-500 hover:text-green-800 bg-white" type="submit" value="Verify">
                                            </form>

                                        @elseif(!$speedrun->disqualified())
                                            <div>
                                                <a href="{{url('/admin/disqualify/'.$speedrun->id)}}"
                                                   class="text-black hover:text-gray-600 bg-white">Disqualify</a>
                                            </div>
                                        @elseif($speedrun->disqualified())
                                            <div>
                                                <a href="{{url('/admin/disqualifications/'.$speedrun->disqualification->id)}}"
                                                   class="text-black hover:text-gray-600 bg-white">Manage DQ</a>
                                            </div>
                                        @endif

                                    @endcan
                                    <form method="POST" action="{{url('/speedruns/'.$speedrun->id)}}">
                                        @csrf
                                        @method('DELETE')
                                        <input class="text-red-500 hover:text-red-800 bg-white" type="submit" value="Delete">
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
{{--    @else--}}
{{--        <div class="h-24 flex object-center my-auto">--}}
{{--            <div class="inline-flex text-3xl text-center font-thin text-green-500 mx-auto my-auto">--}}
{{--                <svg class="animate-spin my-auto h-5 w-5 mr-3 ..." viewBox="0 0 24 24">--}}
{{--                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">--}}
{{--                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>--}}
{{--                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>--}}
{{--                    </svg>--}}
{{--                </svg>--}}
{{--                Loading Speedruns..</div>--}}
{{--        </div>--}}
{{--    @endif--}}
</div>
