<x-app-layout>
    <x-slot name="header">
        Disqualifications
    </x-slot>
    <x-slot name="title">Disqualifications</x-slot>


    <div class="max-w-7xl mx-auto">

        <div class="my-4 overflow-x-auto">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th class="border-r-2 sm:border-0 border-dashed border-gray-200 sm:px-4 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="border-r-2 sm:border-0 border-dashed border-gray-200 sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Time
                            </th>
                            <th class="sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Reason
                            </th>
                            <th class="hidden md:table-cell border-r-2 sm:border-0 border-dashed sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Evidence
                            </th>
                            <th class="hidden md:table-cell sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="hidden md:table-cell sm:px-6 py-3 bg-gray-50 text-center sm:text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Platform
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($disqualifications as $disqualification)
                            <tr>
                                <td class="sm:px-4 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                    {{$disqualification->speedrun->user->name}}
                                </td>
                                <td class="sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                    {{$disqualification->speedrun->time}}s
                                </td>
                                <td class="sm:px-6 py-4 whitespace-no-wrap">
                                    <div class="flex items-center">
                                        <div class="">
                                            <div class="text-sm leading-5 font-medium text-gray-900">
                                                <a>{{$disqualification->reason}}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-center sm:text-left sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                    <a class="text-blue-500 hover:text-blue-700" href="{{$disqualification->evidence}}">{{$disqualification->evidence}}</a>
                                </td>
                                <td class="hidden md:table-cell sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                    <a class="text-center sm:text-left">{{$disqualification->speedrun->category()->title}}</a>
                                </td>
                                <td class="hidden md:table-cell sm:px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                    <a>{{$disqualification->speedrun->platform()->title}}</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
</x-app-layout>
