<x-app-layout>

    <!--
      Tailwind UI components require Tailwind CSS v1.8 and the @tailwindcss/ui plugin.
      Read the documentation to get started: https://tailwindui.com/documentation
    -->
    <div class="flex flex-col w-screen">
        <div class="my-4 overflow-x-auto">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Place
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Time
                            </th>
                            <th class="md:hidden table-cell px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Category/Platform
                            </th>
                            <th class="hidden md:table-cell px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="hidden md:table-cell px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Platform
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                1st
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center">
                                    <div class="">
                                        <div class="text-sm leading-5 font-medium text-gray-900">
                                            Jane Cooper
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                1s
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                Any%
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                XBox
                            </td>
                            <td class="md:hidden table-cell px-6 py-4 whitespace-no-wrap">
                                <div class="text-sm leading-5 text-gray-900">Any%</div>
                                <div class="text-sm leading-5 text-gray-500">XBox</div>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                  Active
                                </span>
                            </td>
                        </tr>

                        <!-- More rows... -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
