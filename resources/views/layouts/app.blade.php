
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0e9f6e">
        <meta name="robots" content="index, follow" />
        <link rel="icon" type="image/svg+xml" href="{{asset('trophy.svg')}}">
        <title>{{ (isset($title) ? $title.' - ':'').config('app.name', 'Laravel') }}</title>
        <meta name="description" content="{{($description??'The official leaderboards for American Dad Speedruns.')}}"/>
        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        @livewireStyles

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.7.3/dist/alpine.js" defer></script>
    </head>
    <body class="font-sans antialiased">
        <div class="fixed w-full p-5 text-center mx-auto mt-16 z-100 flex flex-col pointer-events-none">
            @if($err = Session::get('error'))
                @component('components.alert', ['color'=>'red-300', 'text'=>'red-500', 'id'=>'error'])
                    Error: {{$err}}
                @endcomponent
            @endif
            @if($success = Session::get('success'))
                @component('components.alert', ['color'=>'green-300', 'text'=>'green-500', 'id'=>'success'])
                    {{$success}}
                @endcomponent
            @endif
            @if($info = Session::get('info'))
                @component('components.alert', ['color'=>'indigo-300', 'text'=>'indigo-500', 'id'=>'info'])
                    {{$info}}
                @endcomponent
            @endif
        </div>
        <div class="min-h-screen text-white bg-dark" style="">
            @livewire('navigation-dropdown')
            <!-- Page Heading -->
            @if(isset($header))
            <header class="bg-green-500 shadow text-white">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endif
            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
