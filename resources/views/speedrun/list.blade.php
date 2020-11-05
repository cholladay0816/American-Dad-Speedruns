<x-app-layout>

    <!--
      Tailwind UI components require Tailwind CSS v1.8 and the @tailwindcss/ui plugin.
      Read the documentation to get started: https://tailwindui.com/documentation
    -->
    <div class="flex flex-col w-screen">
        @component('components.speedrun-table', ['speedruns'=>$speedruns])
        @endcomponent
    </div>

</x-app-layout>
