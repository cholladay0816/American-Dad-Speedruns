<x-app-layout>

    <div class="mx-auto max-w-7xl py-10">

        <!-- This example requires Tailwind CSS v2.0+ -->
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Creator
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Title
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Expiration
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($banners as $banner)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{$banner->uploader->name}}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{$banner->uploader->email}}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{\Illuminate\Support\Str::limit($banner->title, 48, $end='...')}}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class=" inline-flex text-sm text-gray-900">
                                      {{$banner->expiration}}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{url('/banners/'.$banner->id)}}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{url('/banners/'.$banner->id)}}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="submit" class="bg-transparent text-red-500" value="Delete">
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-48 max-w-sm mx-auto">
            <a href="{{url('banners/new')}}" class="mx-auto p-6 text-center bg-blue-500 rounded-xl text-white text-2xl font-bold">
                Create New Banner
            </a>
        </div>

    </div>
</x-app-layout>
