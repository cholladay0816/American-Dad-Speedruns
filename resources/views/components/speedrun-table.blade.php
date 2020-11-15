<div class="my-4 overflow-x-auto">
    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
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
                            {{$speedrun->time}}s
                        </td>
                        <td class="hidden md:table-cell sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                            <a class="text-center sm:text-left" href="{{$speedrun->category()->title}}">{{$speedrun->category()->title}}</a>
                        </td>
                        <td class="hidden md:table-cell sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                            <a href="{{$speedrun->platform()->title}}">{{$speedrun->platform()->title}}</a>
                        </td>
                        <td class="md:hidden table-cell sm:px-6 py-4 whitespace-no-wrap">
                            <a href="{{$speedrun->category()->title}}" class="text-sm leading-5 text-gray-900">{{$speedrun->category()->title}}</a>
                            <a href="{{$speedrun->platform()->title}}" class="text-sm leading-5 text-gray-500">{{$speedrun->platform()->title}}</a>
                        </td>
                        <td class="hidden md:table-cell sm:px-6 py-4 whitespace-no-wrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{$speedrun->disqualified()?'bg-red-200 text-red-800':'text-green-800 bg-green-100'}}">
                                      {{$speedrun->status()}}
                                    </span>
                        </td>
                        <td class="sm:px-2 py-4 whitespace-no-wrap text-left text-sm leading-5 font-medium">
                            <a href="{{url('/watch/'.$speedrun->id)}}" class="text-indigo-600 hover:text-indigo-900">Watch</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
