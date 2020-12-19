<x-app-layout>
    <x-slot name="header">
        Admin Dashboard
    </x-slot>
    <x-slot name="title">
        Admin Dashboard
    </x-slot>
    <div class="mx-3">
        <div class="mx-auto py-15 max-w-7xl grid sm:grid-cols-2 md:grid-cols-3 gap-8">
            @component('components.admin-card', ['link'=>'admin/verify'])
                Verify Speedruns ({{$runcount}})
            @endcomponent
            @component('components.admin-card', ['link'=>'admin/disqualifications'])
                Manage Disqualifications
            @endcomponent
            @component('components.admin-card', ['link'=>'speedruns'])
                Manage Speedruns
            @endcomponent
            @component('components.admin-card', ['link'=>'admin/manage_users'])
                Manage Users
            @endcomponent
            @component('components.admin-card', ['link'=>'admin/comments'])
                Manage Comments
            @endcomponent
            @component('components.admin-card', ['link'=>'banners'])
                Manage Banners
            @endcomponent
        </div>
    </div>

</x-app-layout>
