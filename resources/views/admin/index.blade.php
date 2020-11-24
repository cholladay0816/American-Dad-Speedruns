<x-app-layout>
    <x-slot name="header">
        Admin Dashboard
    </x-slot>
    <x-slot name="title">
        Admin Dashboard
    </x-slot>

    <div class="py-15 max-w-7xl mx-auto grid grid-cols-3 gap-8">
        @component('components.admin-card', ['link'=>'verify'])
            Verify Speedruns ({{$runcount}})
        @endcomponent
        @component('components.admin-card', ['link'=>'disqualifications'])
            Manage Disqualifications
        @endcomponent
        @component('components.admin-card', ['link'=>'manage_users'])
            Manage Users
        @endcomponent
        @component('components.admin-card', ['link'=>'speedruns'])
            Manage Speedruns
        @endcomponent
        @component('components.admin-card', ['link'=>'bulletins'])
            Manage Bulletins
        @endcomponent
        @component('components.admin-card', ['link'=>'emails'])
            Manage Emails
        @endcomponent
    </div>

</x-app-layout>
