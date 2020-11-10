<nav x-data="{ open: false }" class="bg-green-500 border-b border-green-500 text-white relative sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex">
                <!-- Logo -->
                <div class="text-white flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}">
                        <div class="block h-9 w-auto" >
                            <img class="w-8 h-8 my-auto" src="{{asset('trophy.svg')}}" alt="Trophy">
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="text-white hidden space-x-4 sm:-my-px sm:ml-4 sm:flex">
                    <a href="{{url('/')}}" class="font-bold text-xl text-white text-center my-auto pr-4">American Dad Speedruns</a>
                    <a href="{{url('/platforms')}}" class="px-1 text-md text-gray-100 text-center my-auto">Platforms</a>
                    <a href="{{url('/categories')}}" class="px-1 text-md text-gray-100 text-center my-auto">Categories</a>
                    @auth()
                    <a href="{{url('/speedruns/new')}}" class="px-1 text-md text-gray-100 text-center my-auto">Submit Run</a>
                    @endauth
                </div>
            </div>
            @if(auth()->user())
            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-jet-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-white hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Account Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Account') }}
                        </div>

                        <x-jet-dropdown-link href="{{ route('profile.show') }}">
                            {{ __('Profile') }}
                        </x-jet-dropdown-link>



                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-jet-dropdown-link href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                                {{ __('Logout') }}
                            </x-jet-dropdown-link>
                        </form>
                    </x-slot>
                </x-jet-dropdown>
            </div>
            @else
                <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <x-jet-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center text-sm font-medium text-white hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                    <div>Guest</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <!-- Account Management -->

                                <x-jet-dropdown-link href="{{ route('login') }}">
                                    {{ __('Login') }}
                                </x-jet-dropdown-link>
                                <x-jet-dropdown-link href="{{ route('register') }}">
                                    {{ __('Register') }}
                                </x-jet-dropdown-link>


                                <div class="border-t border-gray-100"></div>

                            </x-slot>
                        </x-jet-dropdown>
                    </div>
            @endif
            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-jet-responsive-nav-link href="{{ route('speedruns') }}" :active="request()->routeIs('speedruns')">
                {{ __('Speedruns') }}
            </x-jet-responsive-nav-link>
            <x-jet-responsive-nav-link href="{{ route('categories') }}" :active="request()->routeIs('categories')">
                {{ __('Categories') }}
            </x-jet-responsive-nav-link>
            <x-jet-responsive-nav-link href="{{ route('platforms') }}" :active="request()->routeIs('platforms')">
                {{ __('Platforms') }}
            </x-jet-responsive-nav-link>
        </div>
        @if(auth()->user())
            <div class="pt-2 pb-3 space-y-1">
                <x-jet-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-jet-responsive-nav-link>
            </div>
        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">

                <div class="">
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-jet-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-jet-responsive-nav-link>


                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-jet-responsive-nav-link href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                        {{ __('Logout') }}
                    </x-jet-responsive-nav-link>
                </form>
            </div>
        </div>
        @else
            <div class="pt-2 pb-3 space-y-1">
            <a class="block pl-3 pr-4 py-2 border-l-4 border-green-500 text-base font-medium text-indigo-700 bg-gray-50
            focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700
            transition duration-150 ease-in-out"
               href="{{ route('login') }}" :active="request()->routeIs('login')">
                {{ __('Login') }}
            </a>
            <a class="block pl-3 pr-4 py-2 border-l-4 border-green-500 text-base font-medium text-indigo-700 bg-gray-50
            focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700
            transition duration-150 ease-in-out"
               href="{{ route('register') }}" :active="request()->routeIs('register')">
                {{ __('Register') }}
            </a>
            </div>
            @endif
    </div>

</nav>
