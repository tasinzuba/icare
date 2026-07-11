<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-[#C8102E] to-[#A00E27] text-white font-bold shadow-md transition-transform group-hover:scale-105">
                            <span class="text-lg">i</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900 tracking-tight">I-Care</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(auth()->user() && !auth()->user()->is_admin)
                        <x-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.listening.index')" :active="request()->routeIs('student.listening.*')">
                            {{ __('Listening') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.reading.index')" :active="request()->routeIs('student.reading.*')">
                            {{ __('Reading') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.writing.index')" :active="request()->routeIs('student.writing.*')">
                            {{ __('Writing') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.speaking.index')" :active="request()->routeIs('student.speaking.*')">
                            {{ __('Speaking') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.results')" :active="request()->routeIs('student.results*')">
                            {{ __('My Results') }}
                        </x-nav-link>
                    @endif
                    
                    @if(auth()->user() && auth()->user()->is_admin)
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.sections.index')" :active="request()->routeIs('admin.sections.*')">
                            {{ __('Test Sections') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.test-sets.index')" :active="request()->routeIs('admin.test-sets.*')">
                            {{ __('Test Sets') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.questions.index')" :active="request()->routeIs('admin.questions.*')">
                            {{ __('Questions') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.attempts.index')" :active="request()->routeIs('admin.attempts.*')">
                            {{ __('Student Attempts') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-gray-200 text-sm leading-4 font-semibold rounded-md text-gray-800 bg-white hover:text-[#C8102E] hover:border-[#C8102E]/40 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::check() ? Auth::user()->name : 'Guest' }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>